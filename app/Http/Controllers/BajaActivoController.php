<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoTecnico;
use App\Models\AuditoriaCambio;
use App\Models\BajaActivo;
use App\Models\DocumentoAdjunto;
use App\Models\Mantenimiento;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Bajas de activos (flujo simplificado). El módulo NO evalúa técnicamente el
 * activo: esa evaluación ya la hace mantenimientos, que finaliza con
 * resultado_atencion = RECOMENDADO_BAJA. Aquí solo se registra, sustenta,
 * ejecuta o rechaza formalmente la propuesta.
 *
 *   REGISTRADA ─► EJECUTADA
 *        └──────► RECHAZADA
 *
 * - REGISTRADA: activo OBSERVADO, condición MALO, ficha PENDIENTE_BAJA.
 * - EJECUTADA: baja confirmada por documento formal → activo DADO_DE_BAJA.
 * - RECHAZADA: propuesta administrativa no aceptada; el activo permanece
 *   OBSERVADO (su recuperación se hace luego por mantenimiento/regularización).
 */
class BajaActivoController extends Controller
{
    /** Extensiones permitidas para documentos de sustento. */
    private const EXTENSIONES = 'pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx';

    /** Tipos de documento formal admitidos al ejecutar la baja. */
    private const TIPOS_DOC_FORMAL = ['ACTA_BAJA', 'RESOLUCION', 'DOCUMENTO_PATRIMONIAL', 'INFORME_FINAL', 'OTRO'];

    /** Relaciones que se cargan para formatear una baja. */
    private const RELACIONES = [
        'activo.modelo.marca',
        'activo.categoria',
        'activo.responsable',
        'mantenimientoOrigen',
        'registradoPor.colaborador',
        'ejecutadoPor.colaborador',
        'rechazadoPor.colaborador',
        'documentos.subidoPor.colaborador',
    ];

    public function index()
    {
        $bajas = BajaActivo::with(self::RELACIONES)
            ->orderByDesc('id_baja')
            ->get()
            ->map(fn($b) => $this->formatBaja($b))
            ->values();

        // Activos elegibles: no dados de baja y sin propuesta REGISTRADA en curso.
        // (Una baja EJECUTADA deja el activo DADO_DE_BAJA, ya excluido por situación.)
        $conBajaAbierta = BajaActivo::whereIn('estado', BajaActivo::ESTADOS_ABIERTOS)
            ->pluck('id_activo');

        $activos = Activo::with('modelo.marca', 'categoria')
            ->whereNotIn('id_activo', $conBajaAbierta)
            ->where('situacion_actual', '!=', 'DADO_DE_BAJA')
            ->orderBy('codigo_interno')
            ->get()
            ->map(fn($a) => [
                'id_activo'      => $a->id_activo,
                'codigo_interno' => $a->codigo_interno,
                'modelo'         => trim(($a->modelo?->marca?->nombre ?? '') . ' ' . ($a->modelo?->nombre ?? '')),
                'categoria' => [
                    'nombre' => $a->categoria?->nombre,
                    'icono'  => $a->categoria?->icono,
                ],
                'situacion'      => $a->situacion_actual,
            ])
            ->values();

        // Mantenimientos que recomendaron baja, por activo (para vincular origen).
        $mantsBaja = Mantenimiento::where('recomienda_baja', true)
            ->orderByDesc('id_mantenimiento')
            ->get(['id_mantenimiento', 'id_activo', 'descripcion', 'diagnostico', 'resultado'])
            ->groupBy('id_activo')
            ->map(fn($grupo) => $grupo->map(fn($m) => [
                'id_mantenimiento' => $m->id_mantenimiento,
                'codigo'           => $m->codigo,
                'descripcion'      => Str::limit($m->descripcion, 70),
                'diagnostico'      => $m->diagnostico,
                'resultado'        => $m->resultado,
            ])->values());

        return view('content.bajas.index', compact('bajas', 'activos', 'mantsBaja'));
    }

    /**
     * Registra una propuesta de baja (REGISTRADA). Deja el activo OBSERVADO.
     * Acepta un documento inicial opcional (FormData).
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_activo'               => 'required|integer|exists:activo,id_activo',
            'causal_baja'             => ['required', Rule::in(BajaActivo::CAUSALES)],
            'motivo'                  => 'required|string|max:2000',
            'id_mantenimiento_origen' => 'nullable|integer|exists:mantenimientos,id_mantenimiento',
            'fecha_registro'          => 'required|date|before_or_equal:today',
            'observaciones'           => 'nullable|string|max:1000',
            'documento'               => 'nullable|file|mimes:' . self::EXTENSIONES . '|max:5120',
        ], [
            'id_activo.required'   => 'Debes seleccionar un activo.',
            'causal_baja.required' => 'Debes seleccionar la causal de baja.',
            'motivo.required'      => 'El motivo/sustento de la baja es obligatorio.',
            'documento.mimes'      => 'Formato de documento no permitido.',
            'documento.max'        => 'El documento no puede superar los 5 MB.',
        ]);

        $activo = Activo::findOrFail($request->id_activo);

        if ($activo->situacion_actual === 'DADO_DE_BAJA') {
            throw ValidationException::withMessages(['id_activo' => 'El activo ya está dado de baja.']);
        }

        // Sin duplicados: no se permite otra propuesta si ya hay una REGISTRADA o EJECUTADA.
        $existente = BajaActivo::where('id_activo', $activo->id_activo)
            ->whereIn('estado', ['REGISTRADA', 'EJECUTADA'])
            ->first();
        if ($existente) {
            throw ValidationException::withMessages([
                'id_activo' => "El activo ya tiene la baja {$existente->codigo} ({$this->legible($existente->estado)}).",
            ]);
        }

        $mantOrigen = null;
        if ($request->id_mantenimiento_origen) {
            $mantOrigen = Mantenimiento::where('id_mantenimiento', $request->id_mantenimiento_origen)
                ->where('id_activo', $activo->id_activo)
                ->first();
            if (! $mantOrigen) {
                throw ValidationException::withMessages([
                    'id_mantenimiento_origen' => 'El mantenimiento vinculado no corresponde al activo seleccionado.',
                ]);
            }
        }

        $baja = DB::transaction(function () use ($request, $activo, $mantOrigen) {
            $baja = BajaActivo::create([
                'id_activo'               => $activo->id_activo,
                'id_mantenimiento_origen' => $mantOrigen?->id_mantenimiento,
                'registrado_por'          => Auth::id(),
                'causal_baja'             => $request->causal_baja,
                'motivo'                  => trim($request->motivo),
                // Snapshot del diagnóstico del mantenimiento de origen (si existe).
                'diagnostico_tecnico'     => $mantOrigen?->diagnostico,
                'estado'                  => 'REGISTRADA',
                'fecha_registro'          => $request->fecha_registro,
                'observaciones'           => $request->observaciones ? trim($request->observaciones) : null,
            ]);

            // El activo queda OBSERVADO / condición MALO / ficha PENDIENTE_BAJA.
            $this->situarActivo($activo, 'OBSERVADO');
            // Traza de condición: la propuesta de baja fuerza la condición a MALO.
            $activo->marcarOrigenCondicion('BAJA', 'BAJA', $baja->id_baja, $request->motivo ? trim($request->motivo) : null);
            $activo->update(['condicion_actual' => 'MALO']);

            if ($request->hasFile('documento')) {
                $this->guardarDocumento($request->file('documento'), $baja->id_baja, 'SUSTENTO_INICIAL');
            }

            return $baja;
        });

        AuditoriaCambio::registrar('BAJA', $baja->id_baja, 'CREAR', null, [
            'codigo' => $baja->codigo,
            'id_activo' => $baja->id_activo,
            'causal' => $baja->causal_baja,
        ], $request->motivo);

        return $this->respuesta($baja, "Baja {$baja->codigo} registrada.");
    }

    /**
     * Ejecuta formalmente la baja (solo desde REGISTRADA). Exige documento
     * formal. El activo pasa a DADO_DE_BAJA.
     */
    public function ejecutar(Request $request, int $id)
    {
        $baja = BajaActivo::with('activo')->findOrFail($id);

        if ($baja->estado !== 'REGISTRADA') {
            throw ValidationException::withMessages([
                'estado' => "Solo se puede ejecutar una baja REGISTRADA (esta está {$this->legible($baja->estado)}).",
            ]);
        }

        $request->validate([
            'fecha_ejecucion'  => 'required|date|before_or_equal:today',
            'tipo_documento'   => ['required', Rule::in(self::TIPOS_DOC_FORMAL)],
            'numero_documento' => 'nullable|string|max:100',
            'documento'        => 'required|file|mimes:' . self::EXTENSIONES . '|max:5120',
            'observaciones'    => 'nullable|string|max:1000',
        ], [
            'fecha_ejecucion.required' => 'Indica la fecha de ejecución.',
            'tipo_documento.required'  => 'Indica el tipo de documento formal.',
            'documento.required'       => 'El documento formal de baja es obligatorio.',
            'documento.mimes'          => 'Formato de documento no permitido.',
            'documento.max'            => 'El documento no puede superar los 5 MB.',
        ]);

        DB::transaction(function () use ($request, $baja) {
            $baja->update([
                'estado'        => 'EJECUTADA',
                'fecha_baja'    => $request->fecha_ejecucion,
                'ejecutado_por' => Auth::id(),
                'observaciones' => $this->anotar($baja->observaciones, $request->observaciones),
            ]);

            $this->guardarDocumento($request->file('documento'), $baja->id_baja, $request->tipo_documento, $request->numero_documento);

            $activo = $baja->activo;
            $this->situarActivo($activo, 'DADO_DE_BAJA');
            $activo->update(['id_responsable_actual' => null]);
        });

        AuditoriaCambio::registrar('BAJA', $baja->id_baja, 'EJECUTAR', null, [
            'codigo' => $baja->codigo,
            'id_activo' => $baja->id_activo,
            'fecha_baja' => $baja->fecha_baja?->toDateString(),
        ]);

        return $this->respuesta($baja, "Baja {$baja->codigo} ejecutada: el activo quedó DADO DE BAJA.");
    }

    /** Rechaza la propuesta (solo desde REGISTRADA). El activo permanece OBSERVADO. */
    public function rechazar(Request $request, int $id)
    {
        $baja = BajaActivo::with('activo')->findOrFail($id);

        if ($baja->estado !== 'REGISTRADA') {
            throw ValidationException::withMessages([
                'estado' => "Solo se puede rechazar una baja REGISTRADA (esta está {$this->legible($baja->estado)}).",
            ]);
        }

        $request->validate(
            ['motivo' => 'required|string|max:1000'],
            ['motivo.required' => 'Indica el motivo del rechazo.']
        );

        DB::transaction(function () use ($request, $baja) {
            $baja->update([
                'estado'        => 'RECHAZADA',
                'motivo_rechazo' => trim($request->motivo),
                'fecha_rechazo' => now()->toDateString(),
                'rechazado_por' => Auth::id(),
            ]);
            // El activo se mantiene OBSERVADO: el rechazo administrativo no lo vuelve operativo.
        });

        AuditoriaCambio::registrar('BAJA', $baja->id_baja, 'CANCELAR', null, ['codigo' => $baja->codigo], $request->motivo);

        return $this->respuesta($baja, "Baja {$baja->codigo} rechazada. El activo permanece OBSERVADO.");
    }

    /*
     * ── Métodos del flujo anterior: FUERA DE USO ──
     * La evaluación técnica ya no vive en bajas (la hace mantenimientos).
     * Rutas comentadas en routes/web.php.
     *
     * public function evaluar(Request $request, int $id) { ... }  // EN_EVALUACION / RECOMENDADA / RECHAZADA
     * public function validar(Request $request, int $id) { ... }  // RECOMENDADA → VALIDADA
     */

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

    /** Guarda un documento de sustento en el disco privado local. */
    private function guardarDocumento(UploadedFile $file, int $idBaja, string $tipoDocumento, ?string $numeroDocumento = null): void
    {
        $ruta = $file->store('documentos/baja', 'local');

        DocumentoAdjunto::create([
            'entidad_tipo'     => 'BAJA',
            'entidad_id'       => $idBaja,
            'tipo_documento'   => $tipoDocumento,
            'numero_documento' => $numeroDocumento ?: null,
            'archivo'          => $ruta,
            'nombre_original'  => $file->getClientOriginalName(),
            'extension'        => strtolower($file->getClientOriginalExtension()),
            'tamano_kb'        => (int) round($file->getSize() / 1024),
            'subido_por'       => Auth::id(),
        ]);
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
        $baja->refresh()->load(self::RELACIONES);

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
            'activo_categoria_icono' => $a?->categoria?->icono,
            'activo_situacion'    => $a ? $a->situacionLabel() : null,
            'activo_url'          => $a ? route('activos.ver', $a->id_activo) : null,
            'causal'              => $b->causal_baja,
            'estado'              => $b->estado,
            'motivo'              => $b->motivo,
            'observaciones'       => $b->observaciones,
            'diagnostico'         => $b->diagnostico_tecnico,
            // Origen de mantenimiento
            'origen'              => $b->mantenimientoOrigen ? 'Mantenimiento ' . $b->mantenimientoOrigen->codigo : 'Registro directo',
            'id_mantenimiento_origen'   => $b->id_mantenimiento_origen,
            'mantenimiento_origen'      => $b->mantenimientoOrigen?->codigo,
            'diagnostico_mantenimiento' => $b->mantenimientoOrigen?->diagnostico,
            'resultado_mantenimiento'   => $b->mantenimientoOrigen?->resultado,
            // Rechazo
            'motivo_rechazo'      => $b->motivo_rechazo,
            // Usuarios
            'registrado_por'      => $nombreUsuario($b->registradoPor),
            'ejecutado_por'       => $nombreUsuario($b->ejecutadoPor),
            'rechazado_por'       => $nombreUsuario($b->rechazadoPor),
            // Fechas
            'fecha_registro'      => $b->fecha_registro?->format('Y-m-d'),
            'fecha_ejecucion'     => $b->fecha_baja?->format('Y-m-d'),
            'fecha_rechazo'       => $b->fecha_rechazo?->format('Y-m-d'),
            // Documentos
            'documentos_count'    => $b->documentos->count(),
            'documentos'          => $b->documentos->map(fn($d) => [
                'id_documento'    => $d->id_documento,
                'tipo_documento'  => $d->tipo_documento,
                'numero_documento' => $d->numero_documento,
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
