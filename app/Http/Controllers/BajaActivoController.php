<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoTecnico;
use App\Models\BajaActivo;
use App\Models\Colaborador;
use App\Models\EstadoActivo;
use App\Models\Mantenimiento;
use App\Models\TramiteReferencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Bajas de activos (F6), sobre el enum TO BE:
 *
 *   SOLICITADA ─► EN_EVALUACION ─► RECOMENDADA ─► APROBADA ─► EJECUTADA
 *        │              │               │
 *        └──────────────┴───────────────┴─► RECHAZADA
 *
 * Reglas centrales:
 *  - Nunca se pasa un activo a DADO_DE_BAJA por edición directa: solo la
 *    ejecución de una baja APROBADA lo hace (y le quita el responsable).
 *  - Al solicitar, el activo queda PENDIENTE_BAJA; si la baja se rechaza,
 *    se restaura su situación operativa.
 *  - Aprobar exige expediente registrado (tramites_referencias, entidad BAJA).
 *  - Al ejecutar, estado_siga queda PENDIENTE_ACTUALIZACION (Patrimonio debe
 *    reflejar la baja en SIGA y luego marcarla REGISTRADO/OBSERVADO).
 */
class BajaActivoController extends Controller
{
    public function index()
    {
        $bajas = BajaActivo::with([
                'activo.modelo.marca', 'activo.categoria', 'activo.situacion', 'activo.responsable',
                'mantenimientoOrigen', 'solicitadoPor', 'evaluadoPor', 'aprobadoPor.colaborador',
                'tramites', 'documentos.subidoPor.colaborador',
            ])
            ->orderByDesc('id_baja')
            ->get()
            ->map(fn($b) => $this->formatBaja($b))
            ->values();

        // Activos elegibles: no dados de baja y sin propuesta de baja abierta.
        $conBajaAbierta = BajaActivo::whereIn('estado', BajaActivo::ESTADOS_ABIERTOS)
            ->pluck('id_activo');

        $activos = Activo::with('modelo.marca', 'situacion:id_estado_activo,codigo,nombre')
            ->whereNotIn('id_activo', $conBajaAbierta)
            ->whereDoesntHave('situacion', fn($q) => $q->where('codigo', 'DADO_DE_BAJA'))
            ->orderBy('codigo_interno')
            ->get()
            ->map(fn($a) => [
                'id_activo'      => $a->id_activo,
                'codigo_interno' => $a->codigo_interno,
                'modelo'         => trim(($a->modelo?->marca?->nombre ?? '') . ' ' . ($a->modelo?->nombre ?? '')),
                'situacion'      => $a->situacion?->codigo,
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
            'solicitado_por'          => 'nullable|integer|exists:colaboradores,id_colaborador',
            'fecha_solicitud'         => 'required|date|before_or_equal:today',
            'observaciones'           => 'nullable|string|max:1000',
        ], [
            'id_activo.required'   => 'Debes seleccionar un activo.',
            'causal_baja.required' => 'Debes seleccionar la causal de baja.',
            'motivo.required'      => 'El motivo/sustento de la baja es obligatorio.',
        ]);

        $activo = Activo::with('situacion:id_estado_activo,codigo')->findOrFail($request->id_activo);

        if ($activo->situacion?->codigo === 'DADO_DE_BAJA') {
            throw ValidationException::withMessages([
                'id_activo' => 'El activo ya está dado de baja.',
            ]);
        }

        $abierta = BajaActivo::where('id_activo', $activo->id_activo)
            ->whereIn('estado', BajaActivo::ESTADOS_ABIERTOS)
            ->first();
        if ($abierta) {
            throw ValidationException::withMessages([
                'id_activo' => "El activo ya tiene la propuesta {$abierta->codigo} en curso.",
            ]);
        }

        // El mantenimiento vinculado debe pertenecer al mismo activo.
        if ($request->id_mantenimiento_origen) {
            $perteneceAlActivo = Mantenimiento::where('id_mantenimiento', $request->id_mantenimiento_origen)
                ->where('id_activo', $activo->id_activo)
                ->exists();
            if (! $perteneceAlActivo) {
                throw ValidationException::withMessages([
                    'id_mantenimiento_origen' => 'El mantenimiento vinculado no corresponde al activo seleccionado.',
                ]);
            }
        }

        $baja = null;
        DB::transaction(function () use ($request, $activo, &$baja) {
            $baja = BajaActivo::create([
                'id_activo'               => $activo->id_activo,
                'id_mantenimiento_origen' => $request->id_mantenimiento_origen ?: null,
                'solicitado_por'          => $request->solicitado_por ?: null,
                'causal_baja'             => $request->causal_baja,
                'motivo'                  => trim($request->motivo),
                'diagnostico_tecnico'     => $request->diagnostico_tecnico ? trim($request->diagnostico_tecnico) : null,
                'estado'                  => 'SOLICITADA',
                'estado_siga'             => 'NO_APLICA',
                'fecha_solicitud'         => $request->fecha_solicitud,
                'observaciones'           => $request->observaciones ? trim($request->observaciones) : null,
            ]);

            $this->situarActivo($activo, 'PENDIENTE_BAJA');
        });

        return $this->respuesta($baja, "Propuesta de baja {$baja->codigo} registrada.");
    }

    /** Evaluación técnica: EN_EVALUACION, o directamente RECOMENDADA / RECHAZADA. */
    public function evaluar(Request $request, int $id)
    {
        $baja = BajaActivo::with('activo.situacion')->findOrFail($id);

        if (! in_array($baja->estado, ['SOLICITADA', 'EN_EVALUACION'], true)) {
            throw ValidationException::withMessages([
                'estado' => "La baja {$baja->codigo} ya fue evaluada ({$this->legible($baja->estado)}).",
            ]);
        }

        $request->validate([
            'resultado'           => ['required', Rule::in(['EN_EVALUACION', 'RECOMENDADA', 'RECHAZADA'])],
            'diagnostico_tecnico' => 'nullable|string|max:2000',
            'evaluado_por'        => 'nullable|integer|exists:colaboradores,id_colaborador',
            'observaciones'       => 'nullable|string|max:1000',
        ], [
            'resultado.required' => 'Indica el resultado de la evaluación.',
        ]);

        // Recomendar exige informe técnico (regla: baja con sustento técnico).
        $diagnostico = $request->filled('diagnostico_tecnico')
            ? trim($request->diagnostico_tecnico)
            : $baja->diagnostico_tecnico;

        if ($request->resultado === 'RECOMENDADA' && ! $diagnostico) {
            throw ValidationException::withMessages([
                'diagnostico_tecnico' => 'No se puede recomendar la baja sin informe/diagnóstico técnico.',
            ]);
        }

        DB::transaction(function () use ($request, $baja, $diagnostico) {
            $baja->update([
                'estado'              => $request->resultado,
                'diagnostico_tecnico' => $diagnostico,
                'evaluado_por'        => $request->evaluado_por ?: $baja->evaluado_por,
                'fecha_evaluacion'    => now()->toDateString(),
                'observaciones'       => $this->anotar($baja->observaciones, $request->observaciones),
            ]);

            if ($request->resultado === 'RECHAZADA') {
                $this->restaurarActivo($baja);
            }
        });

        return $this->respuesta($baja, "Baja {$baja->codigo}: evaluación registrada ({$this->legible($request->resultado)}).");
    }

    /** Registra el expediente formal (tramites_referencias, entidad BAJA). */
    public function expediente(Request $request, int $id)
    {
        $baja = BajaActivo::findOrFail($id);

        if (! in_array($baja->estado, BajaActivo::ESTADOS_ABIERTOS, true)) {
            throw ValidationException::withMessages([
                'estado' => "La baja {$baja->codigo} ya no está en curso.",
            ]);
        }

        $request->validate([
            'numero_expediente' => 'required|string|max:100',
            'tipo_documento'    => 'nullable|string|max:100',
            'asunto'            => 'nullable|string|max:255',
        ], [
            'numero_expediente.required' => 'Indica el número de expediente.',
        ]);

        TramiteReferencia::create([
            'entidad_tipo'      => 'BAJA',
            'entidad_id'        => $baja->id_baja,
            'numero_expediente' => trim($request->numero_expediente),
            'tipo_documento'    => $request->tipo_documento ? trim($request->tipo_documento) : 'Expediente de baja',
            'asunto'            => $request->asunto ? trim($request->asunto) : "Baja de activo {$baja->codigo}",
            'sistema_origen'    => 'TRAMITE_DOCUMENTARIO',
            'estado_tramite'    => 'EN_PROCESO',
            'fecha_inicio'      => now()->toDateString(),
            'registrado_por'    => Auth::id(),
        ]);

        return $this->respuesta($baja, "Expediente {$request->numero_expediente} vinculado a la baja {$baja->codigo}.");
    }

    /** Aprobación formal: exige evaluación RECOMENDADA y expediente registrado. */
    public function aprobar(Request $request, int $id)
    {
        $baja = BajaActivo::with('tramites')->findOrFail($id);

        if ($baja->estado !== 'RECOMENDADA') {
            throw ValidationException::withMessages([
                'estado' => 'Solo se puede aprobar una baja con evaluación técnica RECOMENDADA.',
            ]);
        }

        if ($baja->tramites->isEmpty()) {
            throw ValidationException::withMessages([
                'estado' => 'Registra el expediente de trámite documentario antes de aprobar la baja.',
            ]);
        }

        $request->validate(['observaciones' => 'nullable|string|max:1000']);

        $baja->update([
            'estado'           => 'APROBADA',
            'aprobado_por'     => Auth::id(),
            'fecha_aprobacion' => now()->toDateString(),
            'observaciones'    => $this->anotar($baja->observaciones, $request->observaciones),
        ]);

        return $this->respuesta($baja, "Baja {$baja->codigo} aprobada. Ya puede ejecutarse.");
    }

    /** Ejecución: el activo pasa a DADO_DE_BAJA y queda pendiente en SIGA. */
    public function ejecutar(int $id)
    {
        $baja = BajaActivo::with('activo.situacion')->findOrFail($id);

        if ($baja->estado !== 'APROBADA') {
            throw ValidationException::withMessages([
                'estado' => 'Solo se puede ejecutar una baja APROBADA.',
            ]);
        }

        DB::transaction(function () use ($baja) {
            $baja->update([
                'estado'      => 'EJECUTADA',
                'estado_siga' => 'PENDIENTE_ACTUALIZACION',
                'fecha_baja'  => now()->toDateString(),
            ]);

            $activo = $baja->activo;
            $this->situarActivo($activo, 'DADO_DE_BAJA');
            $activo->update([
                'id_responsable_actual' => null,
                'estado_siga'           => 'PENDIENTE_ACTUALIZACION',
            ]);
        });

        return $this->respuesta($baja, "Baja {$baja->codigo} ejecutada: el activo quedó DADO DE BAJA (pendiente de actualizar en SIGA).");
    }

    /** Rechazo en cualquier etapa previa a la ejecución; restaura el activo. */
    public function rechazar(Request $request, int $id)
    {
        $baja = BajaActivo::with('activo.situacion')->findOrFail($id);

        if (! in_array($baja->estado, ['SOLICITADA', 'EN_EVALUACION', 'RECOMENDADA', 'APROBADA'], true)) {
            throw ValidationException::withMessages([
                'estado' => "La baja {$baja->codigo} ya no está en curso.",
            ]);
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

    /** Marca el reflejo de la baja en SIGA (lo registra Patrimonio). */
    public function marcarSiga(Request $request, int $id)
    {
        $baja = BajaActivo::with('activo')->findOrFail($id);

        if ($baja->estado !== 'EJECUTADA') {
            throw ValidationException::withMessages([
                'estado' => 'Solo se marca en SIGA una baja ya ejecutada.',
            ]);
        }

        $request->validate([
            'estado_siga'   => ['required', Rule::in(['REGISTRADO', 'OBSERVADO'])],
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $baja) {
            $baja->update([
                'estado_siga'   => $request->estado_siga,
                'observaciones' => $this->anotar($baja->observaciones, $request->observaciones ? 'SIGA: ' . trim($request->observaciones) : null),
            ]);
            $baja->activo->update(['estado_siga' => $request->estado_siga]);
        });

        return $this->respuesta($baja, "Baja {$baja->codigo} marcada en SIGA como {$this->legible($request->estado_siga)}.");
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    private function situarActivo(Activo $activo, string $codigoSituacion): void
    {
        $id = EstadoActivo::where('tipo', 'SITUACION')
            ->where('codigo', $codigoSituacion)
            ->value('id_estado_activo');

        if ($id) {
            $activo->update(['id_situacion_actual' => $id]);
        }

        $estadoOperativo = match ($codigoSituacion) {
            'PENDIENTE_BAJA' => 'PENDIENTE_BAJA',
            'DADO_DE_BAJA'   => 'DADO_DE_BAJA',
            default          => 'OPERATIVO',
        };

        ActivoTecnico::where('id_activo', $activo->id_activo)
            ->update(['estado_operativo' => $estadoOperativo]);
    }

    /** Devuelve el activo a su situación operativa si quedó PENDIENTE_BAJA. */
    private function restaurarActivo(BajaActivo $baja): void
    {
        $activo = $baja->activo;
        if ($activo->situacion?->codigo === 'PENDIENTE_BAJA') {
            $this->situarActivo($activo, $activo->id_responsable_actual ? 'EN_USO' : 'EN_ALMACEN');
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
            'activo.modelo.marca', 'activo.categoria', 'activo.situacion', 'activo.responsable',
            'mantenimientoOrigen', 'solicitadoPor', 'evaluadoPor', 'aprobadoPor.colaborador',
            'tramites', 'documentos.subidoPor.colaborador',
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
        $expediente = $b->tramites->sortByDesc('id_tramite_referencia')->first();

        return [
            'id_baja'             => $b->id_baja,
            'codigo'              => $b->codigo,
            'id_activo'           => $b->id_activo,
            'activo_codigo'       => $a?->codigo_interno,
            'activo_patrimonial'  => $a?->codigo_patrimonial,
            'activo_modelo'       => trim(($a?->modelo?->marca?->nombre ?? '') . ' ' . ($a?->modelo?->nombre ?? '')),
            'activo_categoria'    => $a?->categoria?->nombre,
            'activo_situacion'    => $a?->situacion?->codigo,
            'activo_url'          => $a ? route('activos.ver', $a->id_activo) : null,
            'valor_compra'        => $a?->valor_compra,
            'causal'              => $b->causal_baja,
            'estado'              => $b->estado,
            'estado_siga'         => $b->estado_siga,
            'motivo'              => $b->motivo,
            'diagnostico'         => $b->diagnostico_tecnico,
            'observaciones'       => $b->observaciones,
            'origen'              => $b->mantenimientoOrigen
                ? 'Mantenimiento ' . $b->mantenimientoOrigen->codigo
                : ($b->causal_baja === 'SANEAMIENTO_FALTANTE' ? 'Saneamiento' : 'Registro directo'),
            'expediente'          => $expediente?->numero_expediente,
            'solicitado_por'      => $b->solicitadoPor?->nombre_completo,
            'evaluado_por'        => $b->evaluadoPor?->nombre_completo,
            'aprobado_por'        => $nombreUsuario($b->aprobadoPor),
            'fecha_solicitud'     => $b->fecha_solicitud?->format('Y-m-d'),
            'fecha_evaluacion'    => $b->fecha_evaluacion?->format('Y-m-d'),
            'fecha_aprobacion'    => $b->fecha_aprobacion?->format('Y-m-d'),
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
