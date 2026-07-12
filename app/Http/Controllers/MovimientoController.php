<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\DetalleMovimientoActivo;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MovimientoController extends Controller
{
    /**
     * Movimientos internos OTI (brief §13). Solo 3 tipos. La devolución NO es un
     * tipo: se registra sobre el propio PRESTAMO (ver devolver()). Cada tipo define:
     *   - situacion:   situación resultante del activo (null = se decide en el flujo).
     *   - origen:      situaciones ACTUALES admitidas como origen.
     *   - colaborador/ubicacion/devolucion: qué campos exige la operación.
     *
     * Situaciones: DISPONIBLE, EN_USO, EN_PRESTAMO, EN_MANTENIMIENTO,
     * EN_PROVEEDOR, OBSERVADO, DADO_DE_BAJA (terminal, no se mueve).
     */
    private const OPERACIONES = [
        'PRESTAMO' => [
            'situacion' => 'EN_PRESTAMO',
            'origen'    => ['DISPONIBLE', 'EN_USO'],
            'colaborador' => true, 'ubicacion' => false, 'devolucion' => true,
        ],
        'TRANSFERENCIA' => [
            'situacion' => 'EN_USO',
            'origen'    => ['DISPONIBLE', 'EN_USO'],
            'colaborador' => true, 'ubicacion' => false, 'devolucion' => false,
        ],
        'REGULARIZACION' => [
            'situacion' => null, // se toma del input o se conserva
            'origen'    => ['DISPONIBLE', 'EN_USO', 'EN_PRESTAMO', 'EN_MANTENIMIENTO', 'EN_PROVEEDOR', 'OBSERVADO'],
            'colaborador' => false, 'ubicacion' => false, 'devolucion' => false,
        ],
    ];

    public function index()
    {
        $movimientos = Movimiento::with([
                'detalles.activo:id_activo,codigo_interno',
                'detalles.responsableOrigen', 'detalles.responsableDestino',
                'detalles.ubicacionOrigen', 'detalles.ubicacionDestino',
                'registradoPor',
            ])
            ->orderByDesc('fecha_movimiento')
            ->get()
            ->map(fn($m) => $this->formatMovimiento($m))
            ->values();

        return view('content.movimientos.index', compact('movimientos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo'                      => ['required', Rule::in(array_keys(self::OPERACIONES))],
            'activo_ids'                => 'required|array|min:1',
            'activo_ids.*'              => 'integer|exists:activo,id_activo',
            'id_colaborador_destino'    => 'nullable|integer|exists:colaboradores,id_colaborador',
            'id_ubicacion_destino'      => 'nullable|integer|exists:ubicaciones,id_ubicacion',
            'fecha_devolucion_estimada' => 'nullable|date|after_or_equal:today',
            'condicion_actual'          => ['nullable', 'in:' . implode(',', Activo::CONDICIONES)],
            'situacion_actual'          => ['nullable', 'in:' . implode(',', Activo::SITUACIONES)],
            'motivo'                    => 'nullable|string|max:500',
            'observaciones'             => 'nullable|string|max:500',
        ], [
            'tipo.required'       => 'Debes seleccionar un tipo de movimiento.',
            'activo_ids.required' => 'Debes seleccionar al menos un activo.',
        ]);

        $tipo = $request->tipo;
        $op   = self::OPERACIONES[$tipo];

        // ── Validaciones condicionales según el tipo ──────────────────
        if ($op['colaborador'] && ! $request->id_colaborador_destino) {
            throw ValidationException::withMessages(['id_colaborador_destino' => 'Debes seleccionar el colaborador destino.']);
        }
        if ($op['devolucion'] && ! $request->fecha_devolucion_estimada) {
            throw ValidationException::withMessages(['fecha_devolucion_estimada' => 'Indica la fecha estimada de devolución.']);
        }
        if ($tipo === 'REGULARIZACION') {
            if (! $request->motivo) {
                throw ValidationException::withMessages(['motivo' => 'La regularización exige un motivo.']);
            }
            $cambios = array_filter([
                $request->id_colaborador_destino, $request->id_ubicacion_destino,
                $request->condicion_actual, $request->situacion_actual,
            ]);
            if (empty($cambios)) {
                throw ValidationException::withMessages(['motivo' => 'Indica al menos un dato a regularizar (responsable, ubicación, condición o situación).']);
            }
        }

        $colaboradorDestino = $request->id_colaborador_destino ?: null;
        $ubicacionDestino   = $request->id_ubicacion_destino ?: null;

        $activos = Activo::whereIn('id_activo', $request->activo_ids)->get();

        // El movimiento solo procede si la situación ACTUAL de cada activo lo
        // admite. Se valida todo el lote (o todos avanzan, o ninguno).
        $invalidos = $activos->filter(fn($a) => ! in_array($a->situacion_actual, $op['origen'], true));

        if ($invalidos->isNotEmpty()) {
            $detalle = $invalidos
                ->map(fn($a) => $a->codigo_interno . ' (' . (Activo::SITUACION_LABELS[$a->situacion_actual] ?? $a->situacion_actual) . ')')
                ->implode(', ');

            throw ValidationException::withMessages([
                'activo_ids' => "No se puede registrar «{$tipo}» por la situación actual de: {$detalle}. " . $this->motivoRegla($tipo),
            ]);
        }

        DB::transaction(function () use ($request, $tipo, $op, $colaboradorDestino, $ubicacionDestino, $activos) {
            $mov = Movimiento::create([
                'codigo_movimiento'         => 'TMP',
                'tipo'                      => $tipo,
                'estado'                    => 'EJECUTADO', // se ejecuta el efecto al registrar
                'fecha_registro'            => now(),
                'fecha_movimiento'          => now(),
                'fecha_devolucion_estimada' => $op['devolucion'] ? $request->fecha_devolucion_estimada : null,
                'estado_devolucion'         => $op['devolucion'] ? 'PENDIENTE_DEVOLUCION' : 'NO_APLICA',
                'motivo'                    => $request->motivo ?: null,
                'observaciones'             => $request->observaciones ?: null,
                'registrado_por'            => Auth::id(),
                'ejecutado_por'             => Auth::id(),
                'fecha_ejecucion'           => now(),
                'requiere_tramite'          => false,
            ]);

            $mov->update(['codigo_movimiento' => 'MOV-' . str_pad((string) $mov->id_movimiento, 6, '0', STR_PAD_LEFT)]);

            foreach ($activos as $activo) {
                $situacionAnterior = $activo->situacion_actual;

                // Responsable / ubicación / situación / condición resultantes.
                $respDestino = match ($tipo) {
                    'PRESTAMO', 'TRANSFERENCIA' => $colaboradorDestino,
                    'REGULARIZACION'            => $colaboradorDestino ?: $activo->id_responsable_actual,
                };
                $ubicDestino = $ubicacionDestino ?: $activo->id_ubicacion_actual;

                $situacionResultante = $op['situacion']
                    ?? ($request->situacion_actual ?: $situacionAnterior); // REGULARIZACION
                $condicionResultante = $tipo === 'REGULARIZACION' && $request->condicion_actual
                    ? $request->condicion_actual
                    : $activo->condicion_actual;

                DetalleMovimientoActivo::create([
                    'id_movimiento'          => $mov->id_movimiento,
                    'id_activo'              => $activo->id_activo,
                    'id_responsable_origen'  => $activo->id_responsable_actual,
                    'id_responsable_destino' => $respDestino,
                    'id_ubicacion_origen'    => $activo->id_ubicacion_actual,
                    'id_ubicacion_destino'   => $ubicDestino,
                    'condicion_salida'       => $activo->condicion_actual,
                    'situacion_anterior'     => $situacionAnterior,
                    'situacion_resultante'   => $situacionResultante,
                    // El préstamo queda pendiente hasta su devolución.
                    'resultado'              => $op['devolucion'] ? 'PENDIENTE' : 'APLICADO',
                    'observacion_salida'     => $request->observaciones ?: null,
                ]);

                $activo->update([
                    'id_responsable_actual' => $respDestino,
                    'id_ubicacion_actual'   => $ubicDestino,
                    'situacion_actual'      => $situacionResultante,
                    'condicion_actual'      => $condicionResultante,
                    'actualizado_por'       => Auth::id(),
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Movimiento {$tipo} registrado para " . count($request->activo_ids) . ' activo' . (count($request->activo_ids) !== 1 ? 's' : '') . '.',
            'data'    => $this->activosRefrescados($request->activo_ids),
        ]);
    }

    /**
     * Registra la devolución de un PRESTAMO. La devolución no es un movimiento
     * nuevo: cierra el préstamo existente y decide la situación de retorno.
     */
    public function devolver(Request $request, int $id)
    {
        $request->validate([
            'condicion_retorno'      => ['required', 'in:' . implode(',', Activo::CONDICIONES)],
            'estado_devolucion'      => ['required', 'in:DEVUELTO,DEVUELTO_OBSERVADO'],
            'observacion_devolucion' => 'nullable|string|max:500',
        ], [
            'condicion_retorno.required' => 'Indica en qué condición retorna el activo.',
            'estado_devolucion.required' => 'Indica si la devolución es conforme u observada.',
        ]);

        $mov = Movimiento::with('detalles')->findOrFail($id);

        if ($mov->tipo !== 'PRESTAMO' || $mov->estado_devolucion !== 'PENDIENTE_DEVOLUCION') {
            throw ValidationException::withMessages(['id' => 'Este movimiento no es un préstamo pendiente de devolución.']);
        }

        $observado = $request->estado_devolucion === 'DEVUELTO_OBSERVADO';

        DB::transaction(function () use ($request, $mov, $observado) {
            $mov->update([
                'fecha_devolucion_real'  => now()->toDateString(),
                'estado_devolucion'      => $request->estado_devolucion,
                'observacion_devolucion' => $request->observacion_devolucion ?: null,
            ]);

            foreach ($mov->detalles as $det) {
                // Vuelve bien → DISPONIBLE; vuelve mal → OBSERVADO.
                $situacionRetorno = $observado ? 'OBSERVADO' : 'DISPONIBLE';

                $det->update([
                    'condicion_retorno'    => $request->condicion_retorno,
                    'resultado'            => $observado ? 'DEVUELTO_OBSERVADO' : 'DEVUELTO',
                    'situacion_resultante' => $situacionRetorno,
                    'observacion_retorno'  => $request->observacion_devolucion ?: null,
                ]);

                if ($activo = Activo::find($det->id_activo)) {
                    $activo->update([
                        // El préstamo era temporal: el activo vuelve a manos de OTI.
                        'id_responsable_actual' => $det->id_responsable_origen,
                        'situacion_actual'      => $situacionRetorno,
                        'condicion_actual'      => $request->condicion_retorno,
                        'actualizado_por'       => Auth::id(),
                    ]);
                }
            }
        });

        $ids = $mov->detalles->pluck('id_activo')->all();

        return response()->json([
            'success' => true,
            'message' => 'Devolución del préstamo ' . $mov->codigo_movimiento . ' registrada.',
            'data'    => $this->activosRefrescados($ids),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    /** Reformatea los activos afectados para refrescar la tabla en cliente. */
    private function activosRefrescados(array $ids)
    {
        $ubicacionesPorId = Ubicacion::get(['id_ubicacion', 'id_ubicacion_padre', 'nombre'])
            ->keyBy('id_ubicacion');

        return Activo::with('modelo.marca', 'modelo.categoriaActivo', 'ubicacion.sede', 'responsable', 'categoria', 'activoTecnico')
            ->whereIn('id_activo', $ids)
            ->get()
            ->map(fn($a) => ActivoController::formatActivo($a, $ubicacionesPorId))
            ->values();
    }

    /** Mensaje legible que explica por qué una operación fue rechazada. */
    private function motivoRegla(string $tipo): string
    {
        return match ($tipo) {
            'PRESTAMO'       => 'Solo se presta un activo DISPONIBLE o EN USO (no en mantenimiento, proveedor, prestado ni dado de baja).',
            'TRANSFERENCIA'  => 'Solo se transfiere un activo DISPONIBLE o EN USO.',
            'REGULARIZACION' => 'No se puede regularizar un activo dado de baja.',
            default          => '',
        };
    }

    private function formatMovimiento(Movimiento $m): array
    {
        $nombre = fn($c) => $c
            ? trim("{$c->per_apepat} " . ($c->per_apemat ? "{$c->per_apemat}, " : ', ') . "{$c->per_nombre}")
            : null;

        // Origen/destino viven por activo en el detalle. Se agrega a un único valor
        // si es homogéneo en todo el lote; si difiere, se muestra "Varios".
        $agg = function ($valores) {
            $u = collect($valores)->filter()->unique()->values();
            return $u->count() === 1 ? $u->first() : ($u->count() > 1 ? 'Varios' : null);
        };

        return [
            'id_movimiento'             => $m->id_movimiento,
            'codigo'                    => $m->codigo_movimiento,
            'tipo'                      => $m->tipo,
            'estado'                    => $m->estado,
            'estado_devolucion'         => $m->estado_devolucion,
            'fecha'                     => $m->fecha_movimiento?->format('Y-m-d H:i'),
            'fecha_devolucion_estimada' => $m->fecha_devolucion_estimada?->format('Y-m-d'),
            'fecha_devolucion_real'     => $m->fecha_devolucion_real?->format('Y-m-d'),
            'es_prestamo_pendiente'     => $m->tipo === 'PRESTAMO' && $m->estado_devolucion === 'PENDIENTE_DEVOLUCION',
            'activos'                   => $m->detalles->map(fn($d) => $d->activo?->codigo_interno)->filter()->values(),
            'colaborador_origen'        => $agg($m->detalles->map(fn($d) => $nombre($d->responsableOrigen))),
            'colaborador_destino'       => $agg($m->detalles->map(fn($d) => $nombre($d->responsableDestino))),
            'ubicacion_origen'          => $agg($m->detalles->map(fn($d) => $d->ubicacionOrigen?->nombre)),
            'ubicacion_destino'         => $agg($m->detalles->map(fn($d) => $d->ubicacionDestino?->nombre)),
            'motivo'                    => $m->motivo,
            'registrado_por'            => $m->registradoPor?->nombre_usuario,
        ];
    }
}
