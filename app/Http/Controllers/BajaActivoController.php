<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoTecnico;
use App\Models\BajaActivo;
use App\Models\Colaborador;
use App\Models\Mantenimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Bajas técnicas OTI (F-C, brief §16). NO es una baja patrimonial: es una
 * recomendación técnica que OTI registra, evalúa y valida internamente.
 *
 *   REGISTRADA ─► EN_EVALUACION ─► RECOMENDADA ─► VALIDADA ─► EJECUTADA
 *        │              │               │
 *        └──────────────┴───────────────┴─► RECHAZADA
 *
 * Reglas:
 *  - OTI registra y recomienda (no "solicita"/"aprueba" como área externa):
 *    registrado_por = usuario; evaluado_por / validado_por = colaborador OTI.
 *  - Al recomendar: el activo queda OBSERVADO y condición MALO.
 *  - Solo al EJECUTAR el activo pasa a DADO_DE_BAJA (y pierde responsable).
 *  - Rechazar en cualquier etapa previa restaura la situación operativa.
 */
class BajaActivoController extends Controller
{
    public function index()
    {
        $bajas = BajaActivo::with([
                'activo.modelo.marca', 'activo.categoria', 'activo.responsable',
                'mantenimientoOrigen', 'registradoPor.colaborador', 'evaluadoPor', 'validadoPor',
                'documentos.subidoPor.colaborador',
            ])
            ->orderByDesc('id_baja')
            ->get()
            ->map(fn($b) => $this->formatBaja($b))
            ->values();

        // Activos elegibles: no dados de baja y sin baja abierta en curso.
        $conBajaAbierta = BajaActivo::whereIn('estado', BajaActivo::ESTADOS_ABIERTOS)
            ->pluck('id_activo');

        $activos = Activo::with('modelo.marca')
            ->whereNotIn('id_activo', $conBajaAbierta)
            ->where('situacion_actual', '!=', 'DADO_DE_BAJA')
            ->orderBy('codigo_interno')
            ->get()
            ->map(fn($a) => [
                'id_activo'      => $a->id_activo,
                'codigo_interno' => $a->codigo_interno,
                'modelo'         => trim(($a->modelo?->marca?->nombre ?? '') . ' ' . ($a->modelo?->nombre ?? '')),
                'situacion'      => $a->situacion_actual,
                'valor_compra'   => $a->valor_compra,
            ])
            ->values();

        // Mantenimientos que recomendaron baja, por activo (para vincular origen).
        $mantsBaja = Mantenimiento::where(function ($q) {
                $q->where('recomienda_baja', true)->orWhere('estado', 'RECOMENDADO_BAJA');
            })
            ->orderByDesc('id_mantenimiento')
            ->get(['id_mantenimiento', 'id_activo', 'descripcion', 'estado'])
            ->groupBy('id_activo')
            ->map(fn($grupo) => $grupo->map(fn($m) => [
                'id_mantenimiento' => $m->id_mantenimiento,
                'codigo'           => $m->codigo,
                'descripcion'      => \Illuminate\Support\Str::limit($m->descripcion, 70),
            ])->values());

        $colaboradores = Colaborador::where('estado', 'ACTIVO')
            ->orderBy('per_apepat')
            ->get(['id_colaborador', 'per_nombre', 'per_apepat', 'per_apemat', 'cargo']);

        return view('content.bajas.index', compact('bajas', 'activos', 'mantsBaja', 'colaboradores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_activo'               => 'required|integer|exists:activo,id_activo',
            'causal_baja'             => ['required', Rule::in(BajaActivo::CAUSALES)],
            'motivo'                  => 'required|string|max:2000',
            'diagnostico_tecnico'     => 'nullable|string|max:2000',
            'id_mantenimiento_origen' => 'nullable|integer|exists:mantenimientos,id_mantenimiento',
            'fecha_registro'          => 'required|date|before_or_equal:today',
            'observaciones'           => 'nullable|string|max:1000',
        ], [
            'id_activo.required'   => 'Debes seleccionar un activo.',
            'causal_baja.required' => 'Debes seleccionar la causal de baja.',
            'motivo.required'      => 'El motivo/sustento de la baja es obligatorio.',
        ]);

        $activo = Activo::findOrFail($request->id_activo);

        if ($activo->situacion_actual === 'DADO_DE_BAJA') {
            throw ValidationException::withMessages(['id_activo' => 'El activo ya está dado de baja.']);
        }

        $abierta = BajaActivo::where('id_activo', $activo->id_activo)
            ->whereIn('estado', BajaActivo::ESTADOS_ABIERTOS)
            ->first();
        if ($abierta) {
            throw ValidationException::withMessages(['id_activo' => "El activo ya tiene la baja {$abierta->codigo} en curso."]);
        }

        if ($request->id_mantenimiento_origen) {
            $pertenece = Mantenimiento::where('id_mantenimiento', $request->id_mantenimiento_origen)
                ->where('id_activo', $activo->id_activo)->exists();
            if (! $pertenece) {
                throw ValidationException::withMessages([
                    'id_mantenimiento_origen' => 'El mantenimiento vinculado no corresponde al activo seleccionado.',
                ]);
            }
        }

        $baja = BajaActivo::create([
            'id_activo'               => $activo->id_activo,
            'id_mantenimiento_origen' => $request->id_mantenimiento_origen ?: null,
            'registrado_por'          => Auth::id(),
            'causal_baja'             => $request->causal_baja,
            'motivo'                  => trim($request->motivo),
            'diagnostico_tecnico'     => $request->diagnostico_tecnico ? trim($request->diagnostico_tecnico) : null,
            'estado'                  => 'REGISTRADA',
            'fecha_registro'          => $request->fecha_registro,
            'observaciones'           => $request->observaciones ? trim($request->observaciones) : null,
        ]);

        return $this->respuesta($baja, "Baja {$baja->codigo} registrada.");
    }

    /**
     * Evaluación técnica. resultado ∈ {EN_EVALUACION, RECOMENDADA, RECHAZADA}.
     * Recomendar exige informe/diagnóstico + clasificación y deja el activo
     * OBSERVADO con condición MALO.
     */
    public function evaluar(Request $request, int $id)
    {
        $baja = BajaActivo::with('activo')->findOrFail($id);

        if (! in_array($baja->estado, ['REGISTRADA', 'EN_EVALUACION'], true)) {
            throw ValidationException::withMessages([
                'estado' => "La baja {$baja->codigo} ya fue evaluada ({$this->legible($baja->estado)}).",
            ]);
        }

        $request->validate([
            'resultado'           => ['required', Rule::in(['EN_EVALUACION', 'RECOMENDADA', 'RECHAZADA'])],
            'evaluado_por'        => 'nullable|integer|exists:colaboradores,id_colaborador',
            'diagnostico_tecnico' => 'nullable|string|max:2000',
            'valor_referencial'   => 'nullable|numeric|min:0',
            'clasificacion_final' => ['nullable', Rule::in(BajaActivo::CLASIFICACIONES)],
            'numero_informe_tecnico' => 'nullable|string|max:100',
            'motivo'              => 'nullable|string|max:1000',
            'observaciones'       => 'nullable|string|max:1000',
        ], [
            'resultado.required' => 'Indica el resultado de la evaluación.',
        ]);

        $diagnostico = $request->filled('diagnostico_tecnico')
            ? trim($request->diagnostico_tecnico)
            : $baja->diagnostico_tecnico;

        if ($request->resultado === 'RECOMENDADA') {
            if (! $diagnostico) {
                throw ValidationException::withMessages([
                    'diagnostico_tecnico' => 'No se puede recomendar la baja sin informe/diagnóstico técnico.',
                ]);
            }
            if (! $request->clasificacion_final) {
                throw ValidationException::withMessages([
                    'clasificacion_final' => 'Indica la clasificación final del activo (RAEE, chatarra, obsoleto…).',
                ]);
            }
        }

        DB::transaction(function () use ($request, $baja, $diagnostico) {
            $baja->update([
                'estado'                 => $request->resultado,
                'evaluado_por'           => $request->evaluado_por ?: $baja->evaluado_por,
                'diagnostico_tecnico'    => $diagnostico,
                'valor_referencial'      => $request->filled('valor_referencial') ? $request->valor_referencial : $baja->valor_referencial,
                'clasificacion_final'    => $request->clasificacion_final ?: $baja->clasificacion_final,
                'numero_informe_tecnico' => $request->numero_informe_tecnico ?: $baja->numero_informe_tecnico,
                'fecha_evaluacion'       => now()->toDateString(),
                'observaciones'          => $this->anotar($baja->observaciones, $request->observaciones ?: ($request->motivo ?: null)),
            ]);

            if ($request->resultado === 'RECOMENDADA') {
                // Brief §16: al recomendar, el activo queda OBSERVADO y condición MALO.
                $this->situarActivo($baja->activo, 'OBSERVADO');
                $baja->activo->update(['condicion_actual' => 'MALO']);
            } elseif ($request->resultado === 'RECHAZADA') {
                $this->restaurarActivo($baja);
            }
        });

        return $this->respuesta($baja, "Baja {$baja->codigo}: evaluación registrada ({$this->legible($request->resultado)}).");
    }

    /** Validación interna OTI: RECOMENDADA → VALIDADA. */
    public function validar(Request $request, int $id)
    {
        $baja = BajaActivo::findOrFail($id);

        if ($baja->estado !== 'RECOMENDADA') {
            throw ValidationException::withMessages([
                'estado' => 'Solo se valida una baja con evaluación técnica RECOMENDADA.',
            ]);
        }

        $request->validate([
            'validado_por'                => 'nullable|integer|exists:colaboradores,id_colaborador',
            'numero_documento_validacion' => 'nullable|string|max:100',
            'observaciones'               => 'nullable|string|max:1000',
        ]);

        $baja->update([
            'estado'                      => 'VALIDADA',
            'validado_por'                => $request->validado_por ?: null,
            'numero_documento_validacion' => $request->numero_documento_validacion ?: null,
            'fecha_validacion'            => now()->toDateString(),
            'observaciones'               => $this->anotar($baja->observaciones, $request->observaciones),
        ]);

        return $this->respuesta($baja, "Baja {$baja->codigo} validada. Ya puede ejecutarse.");
    }

    /** Ejecución: el activo pasa a DADO_DE_BAJA. */
    public function ejecutar(int $id)
    {
        $baja = BajaActivo::with('activo')->findOrFail($id);

        if ($baja->estado !== 'VALIDADA') {
            throw ValidationException::withMessages(['estado' => 'Solo se puede ejecutar una baja VALIDADA.']);
        }

        DB::transaction(function () use ($baja) {
            $baja->update([
                'estado'     => 'EJECUTADA',
                'fecha_baja' => now()->toDateString(),
            ]);

            $activo = $baja->activo;
            $this->situarActivo($activo, 'DADO_DE_BAJA');
            $activo->update(['id_responsable_actual' => null]);
        });

        return $this->respuesta($baja, "Baja {$baja->codigo} ejecutada: el activo quedó DADO DE BAJA.");
    }

    /** Rechazo en cualquier etapa previa a la ejecución; restaura el activo. */
    public function rechazar(Request $request, int $id)
    {
        $baja = BajaActivo::with('activo')->findOrFail($id);

        if (! in_array($baja->estado, BajaActivo::ESTADOS_ABIERTOS, true)) {
            throw ValidationException::withMessages(['estado' => "La baja {$baja->codigo} ya no está en curso."]);
        }

        $request->validate(
            ['motivo' => 'required|string|max:1000'],
            ['motivo.required' => 'Indica el motivo del rechazo.']
        );

        DB::transaction(function () use ($request, $baja) {
            $baja->update([
                'estado'        => 'RECHAZADA',
                'observaciones' => $this->anotar($baja->observaciones, 'RECHAZADA: ' . trim($request->motivo)),
            ]);

            $this->restaurarActivo($baja);
        });

        return $this->respuesta($baja, "Baja {$baja->codigo} rechazada; el activo vuelve a su situación operativa.");
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    private function situarActivo(Activo $activo, string $situacion): void
    {
        $activo->update(['situacion_actual' => $situacion]);

        $estadoOperativo = match ($situacion) {
            'OBSERVADO'    => 'PENDIENTE_BAJA',
            'DADO_DE_BAJA' => 'DADO_DE_BAJA',
            default        => 'OPERATIVO',
        };

        ActivoTecnico::where('id_activo', $activo->id_activo)
            ->update(['estado_operativo' => $estadoOperativo]);
    }

    /** Devuelve el activo a su situación operativa si quedó OBSERVADO por la baja. */
    private function restaurarActivo(BajaActivo $baja): void
    {
        $activo = $baja->activo;
        if ($activo->situacion_actual === 'OBSERVADO') {
            $this->situarActivo($activo, $activo->id_responsable_actual ? 'EN_USO' : 'DISPONIBLE');
        }
    }

    /** Acumula notas en observaciones sin perder las anteriores. */
    private function anotar(?string $actual, ?string $nota): ?string
    {
        $nota = $nota ? trim($nota) : null;
        if (! $nota) {
            return $actual;
        }

        return $actual ? $actual . ' | ' . $nota : $nota;
    }

    private function legible(string $valor): string
    {
        return strtolower(str_replace('_', ' ', $valor));
    }

    private function respuesta(BajaActivo $baja, string $mensaje)
    {
        $baja->refresh()->load([
            'activo.modelo.marca', 'activo.categoria', 'activo.responsable',
            'mantenimientoOrigen', 'registradoPor.colaborador', 'evaluadoPor', 'validadoPor',
            'documentos.subidoPor.colaborador',
        ]);

        return response()->json([
            'success' => true,
            'message' => $mensaje,
            'data'    => $this->formatBaja($baja),
        ]);
    }

    private function formatBaja(BajaActivo $b): array
    {
        $a = $b->activo;
        $nombreUsuario = fn($u) => $u?->colaborador?->nombre_completo ?: ($u?->nombre_usuario ?? null);

        return [
            'id_baja'             => $b->id_baja,
            'codigo'              => $b->codigo,
            'id_activo'           => $b->id_activo,
            'activo_codigo'       => $a?->codigo_interno,
            'activo_patrimonial'  => $a?->codigo_patrimonial,
            'activo_modelo'       => trim(($a?->modelo?->marca?->nombre ?? '') . ' ' . ($a?->modelo?->nombre ?? '')),
            'activo_categoria'    => $a?->categoria?->nombre,
            'activo_situacion'    => $a ? $a->situacionLabel() : null,
            'activo_url'          => $a ? route('activos.ver', $a->id_activo) : null,
            'valor_compra'        => $a?->valor_compra,
            'causal'              => $b->causal_baja,
            'clasificacion'       => $b->clasificacion_final,
            'estado'              => $b->estado,
            'motivo'              => $b->motivo,
            'diagnostico'         => $b->diagnostico_tecnico,
            'informe_tecnico'     => $b->numero_informe_tecnico,
            'documento_validacion' => $b->numero_documento_validacion,
            'valor_referencial'   => $b->valor_referencial,
            'observaciones'       => $b->observaciones,
            'origen'              => $b->mantenimientoOrigen
                ? 'Mantenimiento ' . $b->mantenimientoOrigen->codigo
                : 'Registro directo',
            'registrado_por'      => $nombreUsuario($b->registradoPor),
            'evaluado_por'        => $b->evaluadoPor?->nombre_completo,
            'validado_por'        => $b->validadoPor?->nombre_completo,
            'fecha_registro'      => $b->fecha_registro?->format('Y-m-d'),
            'fecha_evaluacion'    => $b->fecha_evaluacion?->format('Y-m-d'),
            'fecha_validacion'    => $b->fecha_validacion?->format('Y-m-d'),
            'fecha_baja'          => $b->fecha_baja?->format('Y-m-d'),
            'documentos'          => $b->documentos->map(fn($d) => [
                'id_documento'    => $d->id_documento,
                'tipo_documento'  => $d->tipo_documento,
                'nombre_original' => $d->nombre_original,
                'extension'       => $d->extension,
                'tamano_kb'       => $d->tamano_kb,
                'subido_por'      => $nombreUsuario($d->subidoPor),
                'fecha'           => $d->creado_en?->format('d/m/Y'),
                'url_descarga'    => route('documentos.download', $d->id_documento),
            ])->values(),
        ];
    }
}
