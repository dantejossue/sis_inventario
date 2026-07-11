<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\DetalleMovimientoActivo;
use App\Models\EstadoActivo;
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
     * Máquina de estados (obs #1). La clave es la OPERACIÓN que expone la UI
     * (vocabulario estable para el front). Cada operación define:
     *   - mov:         el tipo persistido en movimientos.tipo (enum TO BE).
     *   - situacion:   código de situación resultante del activo (null = no cambia).
     *   - origen:      situaciones ACTUALES del activo admitidas como origen.
     *   - colaborador/ubicacion/devolucion: qué campos exige la operación.
     *
     * Situaciones del modelo: EN_USO, EN_ALMACEN, EN_MANTENIMIENTO,
     * EN_DESPLAZAMIENTO, PENDIENTE_BAJA, DADO_DE_BAJA (terminal).
     */
    private const OPERACIONES = [
        'ASIGNAR' => [
            'mov' => 'ASIGNACION', 'situacion' => 'EN_USO',
            'colaborador' => true, 'ubicacion' => false, 'devolucion' => false,
            'origen' => ['EN_ALMACEN'],
        ],
        'TRANSFERENCIA' => [
            'mov' => 'TRANSFERENCIA', 'situacion' => 'EN_USO',
            'colaborador' => true, 'ubicacion' => false, 'devolucion' => false,
            'origen' => ['EN_USO'],
        ],
        'PRESTAMO' => [
            'mov' => 'PRESTAMO_TEMPORAL', 'situacion' => 'EN_DESPLAZAMIENTO',
            'colaborador' => true, 'ubicacion' => false, 'devolucion' => true,
            'origen' => ['EN_ALMACEN'],
        ],
        'DEVOLUCION' => [
            'mov' => 'DEVOLUCION_INTERNA', 'situacion' => 'EN_ALMACEN',
            'colaborador' => false, 'ubicacion' => false, 'devolucion' => false,
            'origen' => ['EN_DESPLAZAMIENTO'],
        ],
        'REUBICACION' => [
            'mov' => 'DESPLAZAMIENTO_INTERNO', 'situacion' => null,
            'colaborador' => false, 'ubicacion' => true, 'devolucion' => false,
            'origen' => ['EN_ALMACEN', 'EN_USO', 'EN_DESPLAZAMIENTO', 'EN_MANTENIMIENTO'],
        ],
        // La BAJA ya no es un movimiento rápido: se gestiona en el módulo de
        // Bajas (BajaActivoController) con evaluación, expediente y aprobación.
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
            'tipo'                        => ['required', Rule::in(array_keys(self::OPERACIONES))],
            'activo_ids'                  => 'required|array|min:1',
            'activo_ids.*'                => 'integer|exists:activo,id_activo',
            'id_colaborador_destino'      => 'nullable|integer|exists:colaboradores,id_colaborador',
            'id_ubicacion_destino'        => 'nullable|integer|exists:ubicaciones,id_ubicacion',
            'fecha_devolucion_programada' => 'nullable|date|after_or_equal:today',
            'motivo'                      => 'nullable|string|max:500',
            'observaciones'               => 'nullable|string|max:500',
        ], [
            'tipo.required'        => 'Debes seleccionar un tipo de movimiento.',
            'activo_ids.required'  => 'Debes seleccionar al menos un activo.',
        ]);

        $tipo = $request->tipo;
        $op   = self::OPERACIONES[$tipo];

        // Validaciones condicionales según la operación
        if ($op['colaborador'] && !$request->id_colaborador_destino) {
            throw ValidationException::withMessages(['id_colaborador_destino' => 'Debes seleccionar el colaborador destino.']);
        }
        if ($op['ubicacion'] && !$request->id_ubicacion_destino) {
            throw ValidationException::withMessages(['id_ubicacion_destino' => 'Debes seleccionar la ubicación destino.']);
        }
        if ($op['devolucion'] && !$request->fecha_devolucion_programada) {
            throw ValidationException::withMessages(['fecha_devolucion_programada' => 'Indica la fecha de devolución programada.']);
        }

        $colaboradorDestino = $request->id_colaborador_destino ?: null;
        $ubicacionDestino   = $request->id_ubicacion_destino ?: null;

        $activos = Activo::with('situacion:id_estado_activo,codigo')
            ->whereIn('id_activo', $request->activo_ids)
            ->get();

        // Regla de negocio: el movimiento solo procede si la situación ACTUAL de
        // cada activo lo admite (máquina de estados). Se valida todo el lote para
        // que ninguno avance si alguno es inválido.
        $invalidos = $activos->filter(fn($a) => !in_array($a->situacion?->codigo, $op['origen'], true));

        if ($invalidos->isNotEmpty()) {
            $detalle = $invalidos
                ->map(fn($a) => $a->codigo_interno . ' (' . str_replace('_', ' ', $a->situacion?->codigo ?? 'SIN SITUACIÓN') . ')')
                ->implode(', ');

            throw ValidationException::withMessages([
                'activo_ids' => "No se puede registrar «{$tipo}» por la situación actual de: {$detalle}. " . $this->motivoRegla($tipo),
            ]);
        }

        DB::transaction(function () use ($request, $tipo, $op, $colaboradorDestino, $ubicacionDestino, $activos) {
            // La fecha de devolución programada ya no es columna de cabecera (TO BE);
            // si aplica, se anota en las observaciones del movimiento.
            $observaciones = $request->observaciones ?: null;
            if ($op['devolucion'] && $request->fecha_devolucion_programada) {
                $nota = 'Devolución programada: ' . $request->fecha_devolucion_programada;
                $observaciones = $observaciones ? $observaciones . ' | ' . $nota : $nota;
            }

            $mov = Movimiento::create([
                'codigo_movimiento' => 'TMP',
                'tipo'              => $op['mov'],
                'estado'            => 'EJECUTADO', // la app ejecuta el efecto al registrar
                'motivo'            => $request->motivo ?: null,
                'observaciones'     => $observaciones,
                'registrado_por'    => Auth::id(),
                'fecha_registro'    => now(),
                'fecha_movimiento'  => now(),
                'requiere_tramite'  => false,
            ]);

            $mov->update(['codigo_movimiento' => 'MOV-' . str_pad((string) $mov->id_movimiento, 6, '0', STR_PAD_LEFT)]);

            $situacionId = $op['situacion'] ? $this->situacionId($op['situacion']) : null;

            foreach ($activos as $activo) {
                // Responsable y ubicación RESULTANTES del activo tras la operación.
                $respDestino = match ($tipo) {
                    'ASIGNAR', 'TRANSFERENCIA', 'PRESTAMO' => $colaboradorDestino,
                    'DEVOLUCION', 'BAJA'                   => null,
                    'REUBICACION'                          => $activo->id_responsable_actual,
                };
                $ubicDestino = ($ubicacionDestino && $tipo !== 'BAJA')
                    ? $ubicacionDestino
                    : $activo->id_ubicacion_actual;

                DetalleMovimientoActivo::create([
                    'id_movimiento'          => $mov->id_movimiento,
                    'id_activo'              => $activo->id_activo,
                    'id_responsable_origen'  => $activo->id_responsable_actual,
                    'id_responsable_destino' => $respDestino,
                    'id_ubicacion_origen'    => $activo->id_ubicacion_actual,
                    'id_ubicacion_destino'   => $ubicDestino,
                    'condicion_salida_id'    => $activo->id_condicion_actual,
                    'condicion_entrada_id'   => $activo->id_condicion_actual,
                    'estado_revision'        => 'CONFORME',
                ]);

                // Efectos sobre el activo
                $updates = [
                    'id_responsable_actual' => $respDestino,
                    'id_ubicacion_actual'   => $ubicDestino,
                ];
                if ($situacionId) {
                    $updates['id_situacion_actual'] = $situacionId;
                }

                $activo->update($updates);
            }
        });

        // Re-formatear los activos afectados para refrescar la tabla en cliente.
        $ubicacionesPorId = Ubicacion::get(['id_ubicacion', 'id_ubicacion_padre', 'nombre'])
            ->keyBy('id_ubicacion');

        $data = Activo::with('modelo.marca', 'modelo.categoriaActivo', 'condicion', 'situacion', 'ubicacion.sede', 'responsable')
            ->whereIn('id_activo', $request->activo_ids)
            ->get()
            ->map(fn($a) => ActivoController::formatActivo($a, $ubicacionesPorId))
            ->values();

        $n = count($request->activo_ids);

        return response()->json([
            'success' => true,
            'message' => "Movimiento {$tipo} registrado para {$n} activo" . ($n !== 1 ? 's' : '') . '.',
            'data'    => $data,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    private function situacionId(string $codigo): int
    {
        return (int) EstadoActivo::where('tipo', 'SITUACION')
            ->where('codigo', $codigo)
            ->value('id_estado_activo');
    }

    /** Mensaje legible que explica por qué una operación fue rechazada. */
    private function motivoRegla(string $tipo): string
    {
        return match ($tipo) {
            'ASIGNAR', 'PRESTAMO' => 'Solo se permite con activos en almacén.',
            'TRANSFERENCIA'       => 'Solo se permite con activos que estén EN USO.',
            'DEVOLUCION'          => 'Solo se permite con activos que estén EN DESPLAZAMIENTO (prestados).',
            'REUBICACION'         => 'No se permite con activos dados de baja.',
            default               => '',
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
            'id_movimiento'       => $m->id_movimiento,
            'codigo'              => $m->codigo_movimiento,
            'tipo'                => $m->tipo,
            'estado'              => $m->estado,
            'fecha'               => $m->fecha_movimiento?->format('Y-m-d H:i'),
            'activos'             => $m->detalles->map(fn($d) => $d->activo?->codigo_interno)->filter()->values(),
            'colaborador_origen'  => $agg($m->detalles->map(fn($d) => $nombre($d->responsableOrigen))),
            'colaborador_destino' => $agg($m->detalles->map(fn($d) => $nombre($d->responsableDestino))),
            'ubicacion_origen'    => $agg($m->detalles->map(fn($d) => $d->ubicacionOrigen?->nombre)),
            'ubicacion_destino'   => $agg($m->detalles->map(fn($d) => $d->ubicacionDestino?->nombre)),
            'motivo'              => $m->motivo,
            'registrado_por'      => $m->registradoPor?->nombre_usuario,
        ];
    }
}
