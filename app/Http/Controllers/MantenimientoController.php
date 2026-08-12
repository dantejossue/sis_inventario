<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoTecnico;
use App\Models\AuditoriaCambio;
use App\Models\Colaborador;
use App\Models\DocumentoAdjunto;
use App\Models\Mantenimiento;
use App\Models\MantenimientoAvance;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Ciclo de mantenimiento (F5) simplificado:
 *
 *   REGISTRADO ─► EN_ATENCION ─► FINALIZADO
 *        │             │
 *        └─────────────┴──────► CANCELADO
 *
 * - La modalidad de atención se decide en el servidor según la garantía del
 *   activo (INTERNA_OTI = técnico OTI, GARANTIA_PROVEEDOR = proveedor).
 * - El registro inicial NO cambia la situación del activo. Esta cambia con el
 *   primer avance (EN_MANTENIMIENTO si es interno, EN_PROVEEDOR si es garantía).
 * - El resultado técnico se guarda en `resultado_atencion`
 *   (OPERATIVO | RECOMENDADO_BAJA), independiente del estado de proceso.
 * - Cada avance crea un asiento en `mantenimiento_avances` (historial completo).
 * - Al finalizar con RECOMENDADO_BAJA el activo queda OBSERVADO / PENDIENTE_BAJA;
 *   la baja formal la ejecuta el módulo de bajas.
 */
class MantenimientoController extends Controller
{
    /** Extensiones permitidas para evidencias (mismas que documentos adjuntos). */
    private const EXTENSIONES = 'pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx';

    /** Relaciones que se cargan para formatear un mantenimiento. */
    private const RELACIONES = [
        'activo.modelo.marca',
        'activo.categoria',
        'activo.responsable',
        'solicitadoPor',
        'tecnicoResponsable',
        'registradoPor.colaborador',
        'documentos.subidoPor.colaborador',
        'avances.registradoPor.colaborador',
        'avances.documentos.subidoPor.colaborador',
    ];

    public function index()
    {
        $mantenimientos = Mantenimiento::with(self::RELACIONES)
            ->orderByDesc('id_mantenimiento')
            ->get()
            ->map(fn($m) => $this->formatMantenimiento($m))
            ->values();

        // Activos elegibles para nuevo mantenimiento: no dados de baja y sin
        // mantenimiento abierto en curso.
        $conMantAbierto = Mantenimiento::whereIn('estado', Mantenimiento::ESTADOS_ABIERTOS)
            ->pluck('id_activo');

        $activos = Activo::with('modelo.marca', 'categoria')
            ->whereNotIn('id_activo', $conMantAbierto)
            ->where('situacion_actual', '!=', 'DADO_DE_BAJA')
            ->orderBy('codigo_interno')
            ->get()
            ->map(fn($activo) => [
                'id_activo'          => $activo->id_activo,
                'codigo_interno'     => $activo->codigo_interno,
                'codigo_patrimonial' => $activo->codigo_patrimonial,
                'modelo'             => trim(
                    ($activo->modelo?->marca?->nombre ?? '') . ' ' . ($activo->modelo?->nombre ?? '')
                ),
                'categoria' => [
                    'nombre' => $activo->categoria?->nombre,
                    'icono'  => $activo->categoria?->icono,
                ],
                'situacion'          => $activo->situacionLabel(),
                'garantia_vigente'   => $this->garantiaVigente($activo),
                'garantia_inicio'    => $activo->garantia_inicio
                    ? Carbon::parse($activo->garantia_inicio)->format('Y-m-d')
                    : null,
                'garantia_fin'       => $activo->garantia_fin
                    ? Carbon::parse($activo->garantia_fin)->format('Y-m-d')
                    : null,
                'proveedor'          => $activo->proveedor,
            ])
            ->values();

        $colaboradores = Colaborador::where('estado', 'ACTIVO')
            ->orderBy('per_apepat')
            ->get(['id_colaborador', 'per_nombre', 'per_apepat', 'per_apemat', 'cargo']);

        $tecnicosOti = Colaborador::from('colaboradores as c')
            ->join('sede_dependencia as sd', 'c.id_sede_dependencia', '=', 'sd.id_sede_dependencia')
            ->join('dependencias as d', 'sd.id_dependencia', '=', 'd.id_dependencia')
            ->where('c.estado', 'ACTIVO')
            ->where(function ($query) {
                $query
                    ->whereRaw("UPPER(TRIM(COALESCE(d.descripcion, ''))) = 'OTI'")
                    ->orWhereRaw("UPPER(d.nombre_dependencia) LIKE '%TECNOLOG%'");
            })
            ->orderBy('c.per_apepat')
            ->get(['c.id_colaborador', 'c.per_nombre', 'c.per_apepat', 'c.per_apemat', 'c.cargo']);

        return view('content.mantenimientos.index', compact('mantenimientos', 'activos', 'colaboradores', 'tecnicosOti'));
    }

    /**
     * Registra un mantenimiento en estado REGISTRADO. La modalidad la decide el
     * servidor según la garantía; la situación del activo NO cambia todavía.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_activo'           => 'required|integer|exists:activo,id_activo',
            'tipo_mantenimiento'  => ['required', Rule::in(['PREVENTIVO', 'CORRECTIVO', 'REVISION_TECNICA'])],
            'solicitado_por'      => 'nullable|integer|exists:colaboradores,id_colaborador',
            'tecnico_responsable' => 'nullable|integer|exists:colaboradores,id_colaborador',
            'proveedor'           => 'nullable|string|max:150',
            'fecha_reporte'       => 'required|date|before_or_equal:today',
            'descripcion'         => 'required|string|max:2000',
        ], [
            'id_activo.required'          => 'Debes seleccionar un activo.',
            'tipo_mantenimiento.required' => 'Debes seleccionar el tipo de mantenimiento.',
            'descripcion.required'        => 'Describe el problema o motivo del mantenimiento.',
            'fecha_reporte.required'      => 'Indica la fecha de reporte.',
        ]);

        $activo = Activo::findOrFail($request->id_activo);

        if ($activo->situacion_actual === 'DADO_DE_BAJA') {
            throw ValidationException::withMessages([
                'id_activo' => 'No se puede registrar mantenimiento sobre un activo dado de baja.',
            ]);
        }

        $abierto = Mantenimiento::where('id_activo', $activo->id_activo)
            ->whereIn('estado', Mantenimiento::ESTADOS_ABIERTOS)
            ->first();
        if ($abierto) {
            throw ValidationException::withMessages([
                'id_activo' => "El activo ya tiene el mantenimiento {$abierto->codigo} en curso. Finalízalo o cancélalo antes de abrir otro.",
            ]);
        }

        // La modalidad la decide el servidor según la garantía (no se confía en el JS).
        $modalidadAtencion = $this->garantiaVigente($activo) ? 'GARANTIA_PROVEEDOR' : 'INTERNA_OTI';

        $tecnicoResponsable = null;
        $proveedor          = null;

        if ($modalidadAtencion === 'INTERNA_OTI') {
            // Sin garantía vigente: se exige técnico OTI.
            if (! $request->tecnico_responsable) {
                throw ValidationException::withMessages([
                    'tecnico_responsable' => 'Debes seleccionar al técnico responsable de OTI.',
                ]);
            }
            $tecnicoResponsable = $request->tecnico_responsable;
        } else {
            // Garantía vigente: proveedor del activo o, como respaldo, el del formulario.
            $proveedor = trim((string) ($activo->proveedor ?: $request->proveedor));
            if ($proveedor === '') {
                throw ValidationException::withMessages([
                    'proveedor' => 'El activo tiene garantía vigente pero no tiene proveedor registrado. Indica el proveedor.',
                ]);
            }
        }

        $mant = Mantenimiento::create([
            'id_activo'           => $activo->id_activo,
            'solicitado_por'      => $request->solicitado_por ?: null,
            'registrado_por'      => Auth::id(),
            'tecnico_responsable' => $tecnicoResponsable,
            'tipo_mantenimiento'  => $request->tipo_mantenimiento,
            'modalidad_atencion'  => $modalidadAtencion,
            // 'origen_reporte' / 'prioridad': columnas eliminadas (migración 2026_07_13_104456).
            'descripcion'         => trim($request->descripcion),
            'diagnostico'         => null,
            'resultado'           => null,
            'resultado_atencion'  => null,
            'proveedor'           => $proveedor,
            'costo'               => null,
            'fecha_reporte'       => $request->fecha_reporte,
            'fecha_inicio'        => null,
            'fecha_fin'           => null,
            'recomienda_baja'     => false,
            'estado'              => 'REGISTRADO',
        ]);

        // NOTA: la situación del activo NO cambia al registrar; cambia con el primer avance.

        AuditoriaCambio::registrar('MANTENIMIENTO', $mant->id_mantenimiento, 'CREAR', null, [
            'codigo'    => $mant->codigo,
            'id_activo' => $mant->id_activo,
            'tipo'      => $mant->tipo_mantenimiento,
            'modalidad' => $mant->modalidad_atencion,
        ]);

        return $this->respuesta($mant, "Mantenimiento {$mant->codigo} registrado correctamente.");
    }

    /**
     * Registra un avance técnico. Crea un asiento en el historial, cambia el
     * estado a EN_ATENCION (si venía de REGISTRADO) y sitúa el activo según la
     * modalidad. La evidencia es opcional. No pide estado, modalidad, técnico ni
     * proveedor: ya se definieron al registrar.
     */
    public function avanzar(Request $request, int $id)
    {
        $mant = Mantenimiento::with('activo')->findOrFail($id);

        if (! in_array($mant->estado, Mantenimiento::ESTADOS_ABIERTOS, true)) {
            throw ValidationException::withMessages([
                'estado' => "El mantenimiento {$mant->codigo} ya no admite avances.",
            ]);
        }

        $request->validate([
            'diagnostico'         => 'nullable|string|max:2000',
            'actividad_realizada' => 'required|string|max:2000',
            'observacion'         => 'nullable|string|max:2000',
            'costo'               => 'nullable|numeric|min:0',
            'evidencia'           => 'nullable|file|mimes:' . self::EXTENSIONES . '|max:5120',
        ], [
            'actividad_realizada.required' => 'Describe la actividad realizada en este avance.',
            'evidencia.mimes'              => 'Formato de evidencia no permitido.',
            'evidencia.max'                => 'La evidencia no puede superar los 5 MB.',
        ]);

        DB::transaction(function () use ($request, $mant) {
            $avance = MantenimientoAvance::create([
                'id_mantenimiento'    => $mant->id_mantenimiento,
                'diagnostico'         => $request->filled('diagnostico') ? trim($request->diagnostico) : null,
                'actividad_realizada' => trim($request->actividad_realizada),
                'observacion'         => $request->filled('observacion') ? trim($request->observacion) : null,
                'costo'               => $request->filled('costo') ? $request->costo : null,
                'registrado_por'      => Auth::id(),
            ]);

            $mant->update([
                'estado'       => 'EN_ATENCION',
                'fecha_inicio' => $mant->fecha_inicio ?: now()->toDateString(),
                // El diagnóstico principal refleja el más reciente (resumen); el historial queda intacto.
                'diagnostico'  => $request->filled('diagnostico') ? trim($request->diagnostico) : $mant->diagnostico,
                'costo'        => $request->filled('costo') ? $request->costo : $mant->costo,
            ]);

            // Situación del activo según la modalidad definida al registrar.
            $this->situarActivo(
                $mant->activo,
                $mant->modalidad_atencion === 'GARANTIA_PROVEEDOR' ? 'EN_PROVEEDOR' : 'EN_MANTENIMIENTO'
            );

            if ($request->hasFile('evidencia')) {
                $this->guardarEvidencia($request->file('evidencia'), $mant->id_mantenimiento, 'EVIDENCIA_AVANCE', $avance->id_avance);
            }
        });

        AuditoriaCambio::registrar('MANTENIMIENTO', $mant->id_mantenimiento, 'ACTUALIZAR', null, [
            'codigo' => $mant->codigo,
            'estado' => 'EN_ATENCION',
        ]);

        return $this->respuesta($mant, "Avance registrado en {$mant->codigo}.");
    }

    /**
     * Finaliza la atención técnica. Solo desde EN_ATENCION. Exige resultado
     * (OPERATIVO | RECOMENDADO_BAJA), diagnóstico final, resultado descriptivo y
     * evidencia final obligatoria. Devuelve el activo a su situación operativa o
     * lo deja pendiente de baja.
     */
    public function finalizar(Request $request, int $id)
    {
        $mant = Mantenimiento::with('activo')->findOrFail($id);

        if ($mant->estado !== 'EN_ATENCION') {
            throw ValidationException::withMessages([
                'estado' => "Solo se puede finalizar un mantenimiento en atención. Registra primero un avance.",
            ]);
        }

        $request->validate([
            'resultado_atencion' => ['required', Rule::in(Mantenimiento::RESULTADOS_ATENCION)],
            'diagnostico'        => 'required|string|max:2000',
            'resultado'          => 'required|string|max:2000',
            'costo'              => 'nullable|numeric|min:0',
            'evidencia'          => 'required|file|mimes:' . self::EXTENSIONES . '|max:5120',
            // Condición física que certifica el técnico al cerrar (solo si el equipo
            // queda operativo; si recomienda baja, se fuerza MALO). Opcional en el
            // servidor para no bloquear el cierre: si no llega, la condición no cambia.
            'condicion_resultante' => ['nullable', Rule::in(Activo::CONDICIONES)],
        ], [
            'resultado_atencion.required' => 'Indica el resultado de la atención.',
            'diagnostico.required'        => 'Registra el diagnóstico final.',
            'resultado.required'          => 'Describe la actividad/resultado final.',
            'evidencia.required'          => 'La evidencia final es obligatoria.',
            'evidencia.mimes'             => 'Formato de evidencia no permitido.',
            'evidencia.max'               => 'La evidencia no puede superar los 5 MB.',
        ]);

        $recomiendaBaja = $request->resultado_atencion === 'RECOMENDADO_BAJA';

        DB::transaction(function () use ($request, $mant, $recomiendaBaja) {
            $mant->update([
                'estado'             => 'FINALIZADO',
                'resultado_atencion' => $request->resultado_atencion,
                'diagnostico'        => trim($request->diagnostico),
                'resultado'          => trim($request->resultado),
                'costo'              => $request->filled('costo') ? $request->costo : $mant->costo,
                'recomienda_baja'    => $recomiendaBaja,
                'fecha_inicio'       => $mant->fecha_inicio ?: now()->toDateString(),
                'fecha_fin'          => now()->toDateString(),
            ]);

            $this->guardarEvidencia($request->file('evidencia'), $mant->id_mantenimiento, 'EVIDENCIA_FINAL');

            $activo = $mant->activo;
            if ($recomiendaBaja) {
                // El módulo de mantenimiento solo recomienda la baja: activo OBSERVADO, condición MALO.
                $this->situarActivo($activo, 'OBSERVADO');
                // Traza de condición (marcar tras situarActivo, que ya guardó la situación).
                $activo->marcarOrigenCondicion('MANTENIMIENTO', 'MANTENIMIENTO', $mant->id_mantenimiento, 'Recomendado para baja tras mantenimiento');
                $activo->update(['condicion_actual' => 'MALO']);
            } else {
                $this->situarActivo($activo, $activo->id_responsable_actual ? 'EN_USO' : 'DISPONIBLE');
                // El técnico certifica la condición física resultante (reparado o revisión).
                // Si no se indicó, se conserva la condición actual (el Observer no registra sin cambio).
                $condicionResultante = $request->condicion_resultante ?: $activo->condicion_actual;
                $activo->marcarOrigenCondicion('MANTENIMIENTO', 'MANTENIMIENTO', $mant->id_mantenimiento, trim($request->resultado));
                $activo->update(['condicion_actual' => $condicionResultante]);
            }
        });

        AuditoriaCambio::registrar('MANTENIMIENTO', $mant->id_mantenimiento, 'CERRAR', null, [
            'codigo'             => $mant->codigo,
            'resultado_atencion' => $request->resultado_atencion,
            'recomienda_baja'    => $recomiendaBaja,
        ]);

        // Integración con bajas: si se recomienda baja, se envían datos para que el
        // front abra el modal de propuesta de baja (la baja NO se crea automáticamente).
        $extra = [];
        if ($recomiendaBaja) {
            $extra = [
                'abrir_modal_baja' => true,
                'baja_prefill'     => [
                    'id_activo'            => $mant->id_activo,
                    'id_mantenimiento'     => $mant->id_mantenimiento,
                    'codigo_activo'        => $mant->activo?->codigo_interno,
                    'codigo_mantenimiento' => $mant->codigo,
                    'diagnostico'          => $mant->diagnostico,
                    'resultado'            => $mant->resultado,
                    'causal_sugerida'      => 'DANO_IRREPARABLE',
                ],
            ];
        }

        return $this->respuesta($mant, "Mantenimiento {$mant->codigo} finalizado ({$this->legible($request->resultado_atencion)}).", $extra);
    }

    /*
     * cerrar(): FUERA DE USO. El cierre administrativo adicional ya no existe:
     * FINALIZADO es el estado terminal del proceso. El mismo personal de OTI
     * registra, atiende y finaliza. Ruta comentada en routes/web.php.
     *
     * public function cerrar(int $id)
     * {
     *     $mant = Mantenimiento::findOrFail($id);
     *     $mant->update(['estado' => 'CERRADO']);
     *     return $this->respuesta($mant, "Mantenimiento {$mant->codigo} cerrado.");
     * }
     */

    /** Cancela un mantenimiento abierto (REGISTRADO/EN_ATENCION) y restaura el activo. */
    public function cancelar(Request $request, int $id)
    {
        $mant = Mantenimiento::with('activo')->findOrFail($id);

        if (! in_array($mant->estado, Mantenimiento::ESTADOS_ABIERTOS, true)) {
            throw ValidationException::withMessages([
                'estado' => "El mantenimiento {$mant->codigo} ya no está abierto: no se puede cancelar.",
            ]);
        }

        $request->validate(
            ['motivo' => 'required|string|max:500'],
            ['motivo.required' => 'Indica el motivo de la cancelación.']
        );

        DB::transaction(function () use ($request, $mant) {
            $mant->update([
                'estado'    => 'CANCELADO',
                'resultado' => 'CANCELADO: ' . trim($request->motivo),
                'fecha_fin' => now()->toDateString(),
                // No se establece resultado_atencion en una cancelación.
            ]);

            // Si el activo quedó EN_MANTENIMIENTO/EN_PROVEEDOR por este proceso, se restaura.
            $activo = $mant->activo;
            if (in_array($activo->situacion_actual, ['EN_MANTENIMIENTO', 'EN_PROVEEDOR'], true)) {
                $this->situarActivo($activo, $activo->id_responsable_actual ? 'EN_USO' : 'DISPONIBLE');
            }
        });

        AuditoriaCambio::registrar('MANTENIMIENTO', $mant->id_mantenimiento, 'CANCELAR', null, ['codigo' => $mant->codigo], $request->motivo);

        return $this->respuesta($mant, "Mantenimiento {$mant->codigo} cancelado.");
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    /** Determina si la garantía del activo está vigente hoy. */
    private function garantiaVigente(Activo $activo): bool
    {
        $inicio = $activo->garantia_inicio ? Carbon::parse($activo->garantia_inicio)->startOfDay() : null;
        $fin    = $activo->garantia_fin ? Carbon::parse($activo->garantia_fin)->endOfDay() : null;
        $hoy    = now();

        return $fin !== null
            && $fin->greaterThanOrEqualTo($hoy)
            && ($inicio === null || $inicio->lessThanOrEqualTo($hoy));
    }

    /** Guarda una evidencia en documentos_adjuntos (disco privado local). */
    private function guardarEvidencia(UploadedFile $file, int $idMantenimiento, string $tipoDocumento, ?int $idAvance = null): void
    {
        $ruta = $file->store('documentos/mantenimiento', 'local');

        DocumentoAdjunto::create([
            'entidad_tipo'    => 'MANTENIMIENTO',
            'entidad_id'      => $idMantenimiento,
            'id_avance'       => $idAvance,
            'tipo_documento'  => $tipoDocumento,
            'archivo'         => $ruta,
            'nombre_original' => $file->getClientOriginalName(),
            'extension'       => strtolower($file->getClientOriginalExtension()),
            'tamano_kb'       => (int) round($file->getSize() / 1024),
            'subido_por'      => Auth::id(),
        ]);
    }

    /**
     * Actualiza la situación del activo y sincroniza el estado operativo de su
     * ficha técnica (si tiene).
     */
    private function situarActivo(Activo $activo, string $situacion): void
    {
        $activo->update(['situacion_actual' => $situacion]);

        $estadoOperativo = match (true) {
            in_array($situacion, ['EN_MANTENIMIENTO', 'EN_PROVEEDOR'], true) => 'EN_MANTENIMIENTO',
            $situacion === 'OBSERVADO' => 'PENDIENTE_BAJA',
            default                    => 'OPERATIVO',
        };

        ActivoTecnico::where('id_activo', $activo->id_activo)
            ->update(['estado_operativo' => $estadoOperativo]);
    }

    private function legible(string $valor): string
    {
        return strtolower(str_replace('_', ' ', $valor));
    }

    /** Respuesta JSON uniforme con el registro re-formateado para la tabla. */
    private function respuesta(Mantenimiento $mant, string $mensaje, array $extra = [])
    {
        $mant->refresh()->load(self::RELACIONES);

        return response()->json(array_merge([
            'success' => true,
            'message' => $mensaje,
            'data'    => $this->formatMantenimiento($mant),
        ], $extra));
    }

    private function formatMantenimiento(Mantenimiento $m): array
    {
        $a = $m->activo;
        $nombreUsuario = fn($u) => $u?->colaborador?->nombre_completo ?: ($u?->nombre_usuario ?? null);

        $mapDoc = fn($d) => [
            'id_documento'    => $d->id_documento,
            'tipo_documento'  => $d->tipo_documento,
            'nombre_original' => $d->nombre_original,
            'extension'       => $d->extension,
            'tamano_kb'       => $d->tamano_kb,
            'subido_por'      => $nombreUsuario($d->subidoPor),
            'fecha'           => $d->creado_en?->format('d/m/Y'),
            'url_descarga'    => route('documentos.download', $d->id_documento),
        ];

        return [
            'id_mantenimiento'    => $m->id_mantenimiento,
            'codigo'              => $m->codigo,
            'id_activo'           => $m->id_activo,

            'activo_codigo'       => $a?->codigo_interno,
            'activo_patrimonial'  => $a?->codigo_patrimonial,
            'activo_modelo'       => trim(($a?->modelo?->marca?->nombre ?? '') . ' ' . ($a?->modelo?->nombre ?? '')),
            'activo_categoria'    => $a?->categoria?->nombre,
            'activo_categoria_icono' => $a?->categoria?->icono,
            'activo_responsable'  => $a?->responsable?->nombre_completo,
            'activo_situacion'    => $a ? $a->situacionLabel() : null,
            'activo_url'          => $a ? route('activos.ver', $a->id_activo) : null,
            'tipo'                => $m->tipo_mantenimiento,
            'modalidad'           => $m->modalidad_atencion,
            // 'origen' / 'prioridad': columnas eliminadas (migración 2026_07_13_104456); sin uso en la UI nueva.
            'estado'              => $m->estado,
            'resultado_atencion'  => $m->resultado_atencion,
            'descripcion'         => $m->descripcion,
            'diagnostico'         => $m->diagnostico,
            'resultado'           => $m->resultado,
            'proveedor'           => $m->proveedor,
            'costo'               => $m->costo,
            'recomienda_baja'     => $m->recomienda_baja,
            'fecha_reporte'       => $m->fecha_reporte?->format('Y-m-d'),
            'fecha_inicio'        => $m->fecha_inicio?->format('Y-m-d'),
            'fecha_fin'           => $m->fecha_fin?->format('Y-m-d'),
            'creado_en'           => $m->creado_en?->format('Y-m-d H:i'),
            'solicitado_por'      => $m->solicitadoPor?->nombre_completo,
            'tecnico'             => $m->tecnicoResponsable?->nombre_completo,
            'id_tecnico'          => $m->tecnico_responsable,
            'registrado_por'      => $nombreUsuario($m->registradoPor),
            'documentos'          => $m->documentos->map($mapDoc)->values(),
            'avances'             => $m->avances->map(fn($av) => [
                'id_avance'           => $av->id_avance,
                'diagnostico'         => $av->diagnostico,
                'actividad_realizada' => $av->actividad_realizada,
                'observacion'         => $av->observacion,
                'costo'               => $av->costo,
                'registrado_por'      => $nombreUsuario($av->registradoPor),
                'fecha'               => $av->creado_en?->format('d/m/Y H:i'),
                'documentos'          => $av->documentos->map($mapDoc)->values(),
            ])->values(),
        ];
    }
}
