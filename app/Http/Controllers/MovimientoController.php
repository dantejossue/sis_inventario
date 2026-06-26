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
     * Configuración por tipo de movimiento: qué exige y qué efecto produce
     * sobre el activo (situación resultante y estado del propio movimiento).
     * Equivale a los flags "requiere"/"afecta" del diseño original.
     */
    private const TIPOS = [
        'ASIGNAR'       => ['colaborador' => true,  'ubicacion' => false, 'devolucion' => false, 'situacion' => 'ASIGNADO',     'estado_mov' => 'CERRADO'],
        'TRANSFERENCIA' => ['colaborador' => true,  'ubicacion' => false, 'devolucion' => false, 'situacion' => 'ASIGNADO',     'estado_mov' => 'CERRADO'],
        'PRESTAMO'      => ['colaborador' => true,  'ubicacion' => false, 'devolucion' => true,  'situacion' => 'EN_PRESTAMO',  'estado_mov' => 'ABIERTO'],
        'DEVOLUCION'    => ['colaborador' => false, 'ubicacion' => false, 'devolucion' => false, 'situacion' => 'DISPONIBLE',   'estado_mov' => 'CERRADO'],
        'REUBICACION'   => ['colaborador' => false, 'ubicacion' => true,  'devolucion' => false, 'situacion' => null,           'estado_mov' => 'CERRADO'],
        'BAJA'          => ['colaborador' => false, 'ubicacion' => false, 'devolucion' => false, 'situacion' => 'DADO_DE_BAJA', 'estado_mov' => 'CERRADO'],
    ];

    /**
     * Situaciones "entregables": el activo está libre y listo para asignar o
     * prestar. Para el negocio, DISPONIBLE = operativo y guardado en almacén,
     * por lo que esos tres estados se tratan como equivalentes.
     */
    private const ENTREGABLE = ['DISPONIBLE', 'EN_ALMACEN', 'OPERATIVO'];

    /**
     * Máquina de estados: situación ACTUAL del activo permitida como ORIGEN de
     * cada tipo de movimiento. Si la situación del activo no está en la lista,
     * el movimiento se rechaza. Un activo DADO_DE_BAJA es terminal: no aparece
     * en ninguna lista, así que no admite ningún movimiento.
     */
    private const TRANSICIONES = [
        'ASIGNAR'       => self::ENTREGABLE,
        'TRANSFERENCIA' => ['ASIGNADO'],
        'PRESTAMO'      => self::ENTREGABLE,
        'DEVOLUCION'    => ['EN_PRESTAMO'],
        'REUBICACION'   => ['DISPONIBLE', 'EN_ALMACEN', 'OPERATIVO', 'ASIGNADO', 'EN_PRESTAMO', 'EN_MANTENIMIENTO'],
        'BAJA'          => ['DISPONIBLE', 'EN_ALMACEN', 'OPERATIVO', 'ASIGNADO', 'EN_MANTENIMIENTO'],
    ];

    public function index()
    {
        $movimientos = Movimiento::with([
                'detalles.activo:id_activo,codigo_interno',
                'colaboradorOrigen', 'colaboradorDestino',
                'ubicacionOrigen', 'ubicacionDestino',
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
            'tipo'                        => ['required', Rule::in(array_keys(self::TIPOS))],
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
        $cfg  = self::TIPOS[$tipo];

        // Validaciones condicionales según el tipo
        if ($cfg['colaborador'] && !$request->id_colaborador_destino) {
            throw ValidationException::withMessages(['id_colaborador_destino' => 'Debes seleccionar el colaborador destino.']);
        }
        if ($cfg['ubicacion'] && !$request->id_ubicacion_destino) {
            throw ValidationException::withMessages(['id_ubicacion_destino' => 'Debes seleccionar la ubicación destino.']);
        }
        if ($cfg['devolucion'] && !$request->fecha_devolucion_programada) {
            throw ValidationException::withMessages(['fecha_devolucion_programada' => 'Indica la fecha de devolución programada.']);
        }

        $colaboradorDestino = $request->id_colaborador_destino ?: null;
        $ubicacionDestino   = $request->id_ubicacion_destino ?: null;

        $activos = Activo::with('situacion:id_estado_activo,nombre')
            ->whereIn('id_activo', $request->activo_ids)
            ->get();

        // Regla de negocio: el movimiento solo procede si la situación ACTUAL de
        // cada activo lo admite (máquina de estados). Se valida todo el lote para
        // que ninguno avance si alguno es inválido.
        $permitidas = self::TRANSICIONES[$tipo];
        $invalidos  = $activos->filter(fn($a) => !in_array($a->situacion?->nombre, $permitidas, true));

        if ($invalidos->isNotEmpty()) {
            $detalle = $invalidos
                ->map(fn($a) => $a->codigo_interno . ' (' . str_replace('_', ' ', $a->situacion?->nombre ?? 'SIN SITUACIÓN') . ')')
                ->implode(', ');

            throw ValidationException::withMessages([
                'activo_ids' => "No se puede registrar «{$tipo}» por la situación actual de: {$detalle}. " . $this->motivoRegla($tipo),
            ]);
        }

        DB::transaction(function () use ($request, $tipo, $cfg, $colaboradorDestino, $ubicacionDestino, $activos) {
            // Origen para la cabecera: solo si es homogéneo entre los activos del lote.
            $origenColab = $activos->pluck('id_responsable_actual')->unique();
            $origenUbic  = $activos->pluck('id_ubicacion_actual')->unique();

            $mov = Movimiento::create([
                'codigo_movimiento'           => 'TMP',
                'tipo'                        => $tipo,
                'estado'                      => $cfg['estado_mov'],
                'id_colaborador_origen'       => $origenColab->count() === 1 ? $origenColab->first() : null,
                'id_colaborador_destino'      => $colaboradorDestino,
                'id_ubicacion_origen'         => $origenUbic->count() === 1 ? $origenUbic->first() : null,
                'id_ubicacion_destino'        => $ubicacionDestino,
                'fecha_devolucion_programada' => $request->fecha_devolucion_programada ?: null,
                'motivo'                      => $request->motivo ?: null,
                'observaciones'               => $request->observaciones ?: null,
                'registrado_por'              => Auth::id(),
            ]);

            $mov->update(['codigo_movimiento' => 'MOV-' . str_pad((string) $mov->id_movimiento, 6, '0', STR_PAD_LEFT)]);

            $situacionId = $cfg['situacion'] ? $this->situacionId($cfg['situacion']) : null;

            foreach ($activos as $activo) {
                DetalleMovimientoActivo::create([
                    'id_movimiento'        => $mov->id_movimiento,
                    'id_activo'            => $activo->id_activo,
                    'condicion_salida_id'  => $activo->id_condicion_actual,
                    'condicion_entrada_id' => $activo->id_condicion_actual,
                ]);

                // Una DEVOLUCION cierra el/los PRESTAMO abiertos de ese activo.
                if ($tipo === 'DEVOLUCION') {
                    Movimiento::where('tipo', 'PRESTAMO')
                        ->where('estado', 'ABIERTO')
                        ->whereHas('detalles', fn($q) => $q->where('id_activo', $activo->id_activo))
                        ->update(['estado' => 'CERRADO', 'fecha_cierre' => now()]);
                }

                // Efectos sobre el activo
                $updates = [];
                if ($situacionId) {
                    $updates['id_situacion_actual'] = $situacionId;
                }
                if (in_array($tipo, ['ASIGNAR', 'TRANSFERENCIA', 'PRESTAMO'], true)) {
                    $updates['id_responsable_actual'] = $colaboradorDestino;
                } elseif (in_array($tipo, ['DEVOLUCION', 'BAJA'], true)) {
                    $updates['id_responsable_actual'] = null;
                }
                if ($ubicacionDestino && $tipo !== 'BAJA') {
                    $updates['id_ubicacion_actual'] = $ubicacionDestino;
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

    private function situacionId(string $nombre): int
    {
        return (int) EstadoActivo::where('tipo_estado', 'SITUACION')
            ->where('nombre', $nombre)
            ->value('id_estado_activo');
    }

    /** Mensaje legible que explica por qué un tipo de movimiento fue rechazado. */
    private function motivoRegla(string $tipo): string
    {
        return match ($tipo) {
            'ASIGNAR', 'PRESTAMO' => 'Solo se permite con activos disponibles (operativos y en almacén).',
            'TRANSFERENCIA'       => 'Solo se permite con activos que estén ASIGNADOS.',
            'DEVOLUCION'          => 'Solo se permite con activos que estén EN PRÉSTAMO.',
            'BAJA'                => 'No se permite si el activo está EN PRÉSTAMO (devuélvelo primero) o ya está DADO DE BAJA.',
            'REUBICACION'         => 'No se permite con activos DADOS DE BAJA.',
            default               => '',
        };
    }

    private function formatMovimiento(Movimiento $m): array
    {
        $nombre = fn($c) => $c
            ? trim("{$c->per_apepat} " . ($c->per_apemat ? "{$c->per_apemat}, " : ', ') . "{$c->per_nombre}")
            : null;

        return [
            'id_movimiento'       => $m->id_movimiento,
            'codigo'              => $m->codigo_movimiento,
            'tipo'                => $m->tipo,
            'estado'              => $m->estado,
            'fecha'               => $m->fecha_movimiento?->format('Y-m-d H:i'),
            'fecha_devolucion'    => $m->fecha_devolucion_programada?->format('Y-m-d'),
            'fecha_cierre'        => $m->fecha_cierre?->format('Y-m-d H:i'),
            'activos'             => $m->detalles->map(fn($d) => $d->activo?->codigo_interno)->filter()->values(),
            'colaborador_origen'  => $nombre($m->colaboradorOrigen),
            'colaborador_destino' => $nombre($m->colaboradorDestino),
            'ubicacion_origen'    => $m->ubicacionOrigen?->nombre,
            'ubicacion_destino'   => $m->ubicacionDestino?->nombre,
            'motivo'              => $m->motivo,
            'registrado_por'      => $m->registradoPor?->nombre_usuario,
        ];
    }
}
