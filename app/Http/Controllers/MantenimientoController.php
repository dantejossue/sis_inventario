<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoTecnico;
use App\Models\Colaborador;
use App\Models\EstadoActivo;
use App\Models\Mantenimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Ciclo de mantenimiento (F5), sobre la máquina de estados del enum TO BE:
 *
 *   SOLICITADO ─► EN_REVISION ─► EN_MANTENIMIENTO ─► ATENDIDO ──────┐
 *        │             │        ◄─► DERIVADO_PROVEEDOR SIN_REPARACION ├─► CERRADO
 *        │             └────────────────────────────► RECOMENDADO_BAJA┘
 *        └───────────────────────────────────────────► CANCELADO
 *
 * Efectos sobre el activo:
 *  - Al iniciar la atención (EN_REVISION / EN_MANTENIMIENTO / DERIVADO_PROVEEDOR)
 *    la situación del activo pasa a EN_MANTENIMIENTO.
 *  - Al finalizar, vuelve a EN_USO (con responsable) o EN_ALMACEN; si el
 *    resultado es RECOMENDADO_BAJA (o se marca recomienda_baja) → PENDIENTE_BAJA.
 *  - Cancelar restaura la situación si estaba EN_MANTENIMIENTO.
 */
class MantenimientoController extends Controller
{
    /** Transiciones de avance permitidas entre estados abiertos. */
    private const AVANCES = [
        'SOLICITADO'         => ['EN_REVISION', 'EN_MANTENIMIENTO', 'DERIVADO_PROVEEDOR'],
        'EN_REVISION'        => ['EN_MANTENIMIENTO', 'DERIVADO_PROVEEDOR'],
        'EN_MANTENIMIENTO'   => ['DERIVADO_PROVEEDOR'],
        'DERIVADO_PROVEEDOR' => ['EN_MANTENIMIENTO'],
    ];

    public function index()
    {
        $mantenimientos = Mantenimiento::with([
                'activo.modelo.marca', 'activo.categoria', 'activo.responsable', 'activo.situacion',
                'solicitadoPor', 'tecnicoResponsable', 'registradoPor.colaborador',
                'documentos.subidoPor.colaborador',
            ])
            ->orderByDesc('id_mantenimiento')
            ->get()
            ->map(fn($m) => $this->formatMantenimiento($m))
            ->values();

        // Activos elegibles para nuevo mantenimiento: no dados de baja y sin
        // mantenimiento abierto en curso.
        $conMantAbierto = Mantenimiento::whereIn('estado', Mantenimiento::ESTADOS_ABIERTOS)
            ->pluck('id_activo');

        $activos = Activo::with('modelo.marca', 'situacion:id_estado_activo,codigo,nombre')
            ->whereNotIn('id_activo', $conMantAbierto)
            ->whereDoesntHave('situacion', fn($q) => $q->where('codigo', 'DADO_DE_BAJA'))
            ->orderBy('codigo_interno')
            ->get()
            ->map(fn($a) => [
                'id_activo'      => $a->id_activo,
                'codigo_interno' => $a->codigo_interno,
                'modelo'         => trim(($a->modelo?->marca?->nombre ?? '') . ' ' . ($a->modelo?->nombre ?? '')),
                'situacion'      => $a->situacion?->nombre,
            ])
            ->values();

        $colaboradores = Colaborador::where('estado', 'ACTIVO')
            ->orderBy('per_apepat')
            ->get(['id_colaborador', 'per_nombre', 'per_apepat', 'per_apemat', 'cargo']);

        return view('content.mantenimientos.index', compact('mantenimientos', 'activos', 'colaboradores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_activo'           => 'required|integer|exists:activo,id_activo',
            'tipo_mantenimiento'  => ['required', Rule::in(['PREVENTIVO', 'CORRECTIVO', 'GARANTIA', 'REVISION_TECNICA'])],
            'prioridad'           => ['required', Rule::in(['BAJA', 'MEDIA', 'ALTA', 'CRITICA'])],
            'origen_reporte'      => ['required', Rule::in(['USUARIO', 'OTI', 'USG', 'INVENTARIO', 'OTRO'])],
            'solicitado_por'      => 'nullable|integer|exists:colaboradores,id_colaborador',
            'tecnico_responsable' => 'nullable|integer|exists:colaboradores,id_colaborador',
            'proveedor'           => 'nullable|string|max:150',
            'fecha_reporte'       => 'required|date|before_or_equal:today',
            'descripcion'         => 'required|string|max:2000',
            'diagnostico'         => 'nullable|string|max:2000',
            'costo'               => 'nullable|numeric|min:0',
        ], [
            'id_activo.required'          => 'Debes seleccionar un activo.',
            'tipo_mantenimiento.required' => 'Debes seleccionar el tipo de mantenimiento.',
            'descripcion.required'        => 'Describe el problema o motivo del mantenimiento.',
            'fecha_reporte.required'      => 'Indica la fecha de reporte.',
        ]);

        $activo = Activo::with('situacion:id_estado_activo,codigo')->findOrFail($request->id_activo);

        if ($activo->situacion?->codigo === 'DADO_DE_BAJA') {
            throw ValidationException::withMessages([
                'id_activo' => 'No se puede registrar mantenimiento sobre un activo dado de baja.',
            ]);
        }

        $abierto = Mantenimiento::where('id_activo', $activo->id_activo)
            ->whereIn('estado', Mantenimiento::ESTADOS_ABIERTOS)
            ->first();
        if ($abierto) {
            throw ValidationException::withMessages([
                'id_activo' => "El activo ya tiene el mantenimiento {$abierto->codigo} en curso. Ciérralo o cancélalo antes de abrir otro.",
            ]);
        }

        $mant = Mantenimiento::create([
            'id_activo'           => $activo->id_activo,
            'solicitado_por'      => $request->solicitado_por ?: null,
            'registrado_por'      => Auth::id(),
            'tecnico_responsable' => $request->tecnico_responsable ?: null,
            'tipo_mantenimiento'  => $request->tipo_mantenimiento,
            'origen_reporte'      => $request->origen_reporte,
            'prioridad'           => $request->prioridad,
            'descripcion'         => trim($request->descripcion),
            'diagnostico'         => $request->diagnostico ? trim($request->diagnostico) : null,
            'proveedor'           => $request->proveedor ? trim($request->proveedor) : null,
            'costo'               => $request->costo ?: null,
            'fecha_reporte'       => $request->fecha_reporte,
            'estado'              => 'SOLICITADO',
        ]);

        return $this->respuesta($mant, "Mantenimiento {$mant->codigo} registrado correctamente.");
    }

    /**
     * Avance técnico: transición entre estados abiertos + actualización de
     * diagnóstico, técnico, proveedor y costo. Al entrar en atención, el
     * activo pasa a situación EN_MANTENIMIENTO.
     */
    public function avanzar(Request $request, int $id)
    {
        $mant = Mantenimiento::with('activo.situacion')->findOrFail($id);

        $permitidos = self::AVANCES[$mant->estado] ?? [];
        if (empty($permitidos)) {
            throw ValidationException::withMessages([
                'estado' => "El mantenimiento {$mant->codigo} está {$this->legible($mant->estado)}: ya no admite avances.",
            ]);
        }

        $request->validate([
            'estado'              => ['required', Rule::in($permitidos)],
            'diagnostico'         => 'nullable|string|max:2000',
            'tecnico_responsable' => 'nullable|integer|exists:colaboradores,id_colaborador',
            'proveedor'           => 'nullable|string|max:150',
            'costo'               => 'nullable|numeric|min:0',
        ], [
            'estado.required' => 'Debes indicar el nuevo estado.',
            'estado.in'       => 'Esa transición de estado no está permitida desde el estado actual.',
        ]);

        if ($request->estado === 'DERIVADO_PROVEEDOR' && ! trim((string) ($request->proveedor ?: $mant->proveedor))) {
            throw ValidationException::withMessages([
                'proveedor' => 'Para derivar a proveedor debes indicar el proveedor.',
            ]);
        }

        DB::transaction(function () use ($request, $mant) {
            $mant->update([
                'estado'              => $request->estado,
                'diagnostico'         => $request->filled('diagnostico') ? trim($request->diagnostico) : $mant->diagnostico,
                'tecnico_responsable' => $request->tecnico_responsable ?: $mant->tecnico_responsable,
                'proveedor'           => $request->filled('proveedor') ? trim($request->proveedor) : $mant->proveedor,
                'costo'               => $request->filled('costo') ? $request->costo : $mant->costo,
                'fecha_inicio'        => $mant->fecha_inicio ?: now()->toDateString(),
            ]);

            $this->situarActivo($mant->activo, 'EN_MANTENIMIENTO');
        });

        return $this->respuesta($mant, "Mantenimiento {$mant->codigo} actualizado a {$this->legible($request->estado)}.");
    }

    /**
     * Finaliza la atención técnica. Exige diagnóstico y resultado (regla de
     * negocio: no cerrar sin ambos) y devuelve el activo a su situación
     * operativa o lo deja pendiente de baja.
     */
    public function finalizar(Request $request, int $id)
    {
        $mant = Mantenimiento::with('activo.situacion')->findOrFail($id);

        if (! in_array($mant->estado, Mantenimiento::ESTADOS_ABIERTOS, true)) {
            throw ValidationException::withMessages([
                'estado' => "El mantenimiento {$mant->codigo} ya no está abierto.",
            ]);
        }

        $request->validate([
            'estado'      => ['required', Rule::in(Mantenimiento::ESTADOS_RESULTADO)],
            'resultado'   => 'required|string|max:2000',
            'diagnostico' => 'nullable|string|max:2000',
            'costo'       => 'nullable|numeric|min:0',
        ], [
            'estado.required'    => 'Debes indicar el resultado de la atención.',
            'resultado.required' => 'Describe el resultado de la atención (obligatorio para finalizar).',
        ]);

        $diagnostico = $request->filled('diagnostico') ? trim($request->diagnostico) : $mant->diagnostico;
        if (! $diagnostico) {
            throw ValidationException::withMessages([
                'diagnostico' => 'No se puede finalizar sin diagnóstico técnico registrado.',
            ]);
        }

        $recomiendaBaja = $request->estado === 'RECOMENDADO_BAJA';

        DB::transaction(function () use ($request, $mant, $diagnostico, $recomiendaBaja) {
            $mant->update([
                'estado'          => $request->estado,
                'diagnostico'     => $diagnostico,
                'resultado'       => trim($request->resultado),
                'costo'           => $request->filled('costo') ? $request->costo : $mant->costo,
                'recomienda_baja' => $recomiendaBaja,
                'fecha_inicio'    => $mant->fecha_inicio ?: now()->toDateString(),
                'fecha_fin'       => now()->toDateString(),
            ]);

            $activo = $mant->activo;
            if ($recomiendaBaja) {
                $this->situarActivo($activo, 'PENDIENTE_BAJA');
            } else {
                $this->situarActivo($activo, $activo->id_responsable_actual ? 'EN_USO' : 'EN_ALMACEN', $request->estado);
            }
        });

        return $this->respuesta($mant, "Mantenimiento {$mant->codigo} finalizado: {$this->legible($request->estado)}.");
    }

    /** Cierre formal (validación final) de un mantenimiento ya atendido. */
    public function cerrar(int $id)
    {
        $mant = Mantenimiento::findOrFail($id);

        if (! in_array($mant->estado, Mantenimiento::ESTADOS_RESULTADO, true)) {
            throw ValidationException::withMessages([
                'estado' => "Solo se puede cerrar un mantenimiento con resultado registrado (atendido, sin reparación o recomendado para baja).",
            ]);
        }

        $mant->update(['estado' => 'CERRADO']);

        return $this->respuesta($mant, "Mantenimiento {$mant->codigo} cerrado.");
    }

    /** Cancela un mantenimiento abierto y restaura la situación del activo. */
    public function cancelar(Request $request, int $id)
    {
        $mant = Mantenimiento::with('activo.situacion')->findOrFail($id);

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
            ]);

            // Si el activo quedó EN_MANTENIMIENTO por este proceso, se restaura.
            $activo = $mant->activo;
            if ($activo->situacion?->codigo === 'EN_MANTENIMIENTO') {
                $this->situarActivo($activo, $activo->id_responsable_actual ? 'EN_USO' : 'EN_ALMACEN');
            }
        });

        return $this->respuesta($mant, "Mantenimiento {$mant->codigo} cancelado.");
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    /**
     * Actualiza la situación del activo y sincroniza el estado operativo de
     * su ficha técnica (si tiene). $resultado ajusta el estado_operativo al
     * finalizar (SIN_REPARACION → INOPERATIVO).
     */
    private function situarActivo(Activo $activo, string $codigoSituacion, ?string $resultado = null): void
    {
        $id = EstadoActivo::where('tipo', 'SITUACION')
            ->where('codigo', $codigoSituacion)
            ->value('id_estado_activo');

        if ($id) {
            $activo->update(['id_situacion_actual' => $id]);
        }

        $estadoOperativo = match (true) {
            $codigoSituacion === 'EN_MANTENIMIENTO' => 'EN_MANTENIMIENTO',
            $codigoSituacion === 'PENDIENTE_BAJA'   => 'PENDIENTE_BAJA',
            $resultado === 'SIN_REPARACION'         => 'INOPERATIVO',
            default                                 => 'OPERATIVO',
        };

        ActivoTecnico::where('id_activo', $activo->id_activo)
            ->update(['estado_operativo' => $estadoOperativo]);
    }

    private function legible(string $valor): string
    {
        return strtolower(str_replace('_', ' ', $valor));
    }

    /** Respuesta JSON uniforme con el registro re-formateado para la tabla. */
    private function respuesta(Mantenimiento $mant, string $mensaje)
    {
        $mant->refresh()->load([
            'activo.modelo.marca', 'activo.categoria', 'activo.responsable', 'activo.situacion',
            'solicitadoPor', 'tecnicoResponsable', 'registradoPor.colaborador',
            'documentos.subidoPor.colaborador',
        ]);

        return response()->json([
            'success' => true,
            'message' => $mensaje,
            'data'    => $this->formatMantenimiento($mant),
        ]);
    }

    private function formatMantenimiento(Mantenimiento $m): array
    {
        $a = $m->activo;
        $nombreUsuario = fn($u) => $u?->colaborador?->nombre_completo ?: ($u?->nombre_usuario ?? null);

        return [
            'id_mantenimiento'    => $m->id_mantenimiento,
            'codigo'              => $m->codigo,
            'id_activo'           => $m->id_activo,
            'activo_codigo'       => $a?->codigo_interno,
            'activo_patrimonial'  => $a?->codigo_patrimonial,
            'activo_modelo'       => trim(($a?->modelo?->marca?->nombre ?? '') . ' ' . ($a?->modelo?->nombre ?? '')),
            'activo_categoria'    => $a?->categoria?->nombre,
            'activo_responsable'  => $a?->responsable?->nombre_completo,
            'activo_situacion'    => $a?->situacion?->nombre,
            'activo_url'          => $a ? route('activos.ver', $a->id_activo) : null,
            'tipo'                => $m->tipo_mantenimiento,
            'origen'              => $m->origen_reporte,
            'prioridad'           => $m->prioridad,
            'estado'              => $m->estado,
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
            'documentos'          => $m->documentos->map(fn($d) => [
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
