<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\BajaActivo;
use App\Models\Mantenimiento;
use App\Models\Movimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Reportes operativos OTI (F-F, brief §20). Trabaja sobre el modelo ENUM:
 * situación/condición del activo, préstamos y su devolución, mantenimientos
 * y bajas técnicas. Sin reportes de inventario/saneamiento (fuera de alcance).
 */
class ReporteController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();

        // ── KPIs por situación ────────────────────────────────────────
        $porSituacion = Activo::query()
            ->selectRaw('situacion_actual, COUNT(*) as total')
            ->groupBy('situacion_actual')
            ->pluck('total', 'situacion_actual');

        $totalActivos = (int) $porSituacion->sum();

        $kpis = [
            'total'            => $totalActivos,
            'disponible'       => (int) ($porSituacion['DISPONIBLE'] ?? 0),
            'en_uso'           => (int) ($porSituacion['EN_USO'] ?? 0),
            'en_prestamo'      => (int) ($porSituacion['EN_PRESTAMO'] ?? 0),
            'en_mantenimiento' => (int) ($porSituacion['EN_MANTENIMIENTO'] ?? 0),
            'en_proveedor'     => (int) ($porSituacion['EN_PROVEEDOR'] ?? 0),
            'observado'        => (int) ($porSituacion['OBSERVADO'] ?? 0),
            'dado_de_baja'     => (int) ($porSituacion['DADO_DE_BAJA'] ?? 0),
        ];

        // ── Distribuciones (situación / condición / categoría) ────────
        $distSituacion = collect(Activo::SITUACIONES)->map(fn($s) => [
            'clave'   => $s,
            'etiqueta' => Activo::SITUACION_LABELS[$s] ?? $s,
            'total'   => (int) ($porSituacion[$s] ?? 0),
        ])->filter(fn($r) => $r['total'] > 0)->values();

        $porCondicion = Activo::query()
            ->selectRaw('condicion_actual, COUNT(*) as total')
            ->groupBy('condicion_actual')
            ->pluck('total', 'condicion_actual');

        $distCondicion = collect(Activo::CONDICIONES)->map(fn($c) => [
            'clave'    => $c,
            'etiqueta' => Activo::CONDICION_LABELS[$c] ?? $c,
            'total'    => (int) ($porCondicion[$c] ?? 0),
        ])->filter(fn($r) => $r['total'] > 0)->values();

        $porCategoria = Activo::query()
            ->join('categoria_activo', 'activo.id_categoria', '=', 'categoria_activo.id_categoria')
            ->selectRaw('categoria_activo.nombre as nombre, COUNT(*) as total')
            ->groupBy('categoria_activo.nombre')
            ->orderByDesc('total')
            ->get();

        // ── Por responsable / ubicación ───────────────────────────────
        $porResponsable = Activo::query()
            ->join('colaboradores', 'activo.id_responsable_actual', '=', 'colaboradores.id_colaborador')
            ->selectRaw("CONCAT(colaboradores.per_apepat,' ',COALESCE(colaboradores.per_apemat,''),', ',colaboradores.per_nombre) as nombre, COUNT(*) as total")
            ->groupBy('nombre')
            ->orderByDesc('total')
            ->get();

        $porUbicacion = Activo::query()
            ->join('ubicaciones', 'activo.id_ubicacion_actual', '=', 'ubicaciones.id_ubicacion')
            ->selectRaw('ubicaciones.nombre as nombre, COUNT(*) as total')
            ->groupBy('ubicaciones.nombre')
            ->orderByDesc('total')
            ->get();

        // ── Préstamos pendientes / vencidos ───────────────────────────
        $prestamos = Movimiento::with('detalles.activo:id_activo,codigo_interno', 'detalles.responsableDestino')
            ->where('tipo', 'PRESTAMO')
            ->where('estado_devolucion', 'PENDIENTE_DEVOLUCION')
            ->orderBy('fecha_devolucion_estimada')
            ->get()
            ->map(function ($m) use ($hoy) {
                $vencido = $m->fecha_devolucion_estimada && $m->fecha_devolucion_estimada->lt($hoy);
                $r = $m->detalles->first()?->responsableDestino;
                return [
                    'codigo'      => $m->codigo_movimiento,
                    'activos'     => $m->detalles->map(fn($d) => $d->activo?->codigo_interno)->filter()->implode(', '),
                    'responsable' => $r ? trim("{$r->per_apepat} {$r->per_apemat}, {$r->per_nombre}") : '—',
                    'estimada'    => $m->fecha_devolucion_estimada?->format('Y-m-d'),
                    'dias'        => $m->fecha_devolucion_estimada ? $hoy->diffInDays($m->fecha_devolucion_estimada, false) : null,
                    'vencido'     => $vencido,
                ];
            });

        // ── Garantías vigentes / por vencer / vencidas ────────────────
        $conGarantia = Activo::whereNotNull('garantia_fin')->get(['id_activo', 'codigo_interno', 'garantia_fin']);
        $garantia = [
            'vigentes'   => $conGarantia->filter(fn($a) => Carbon::parse($a->garantia_fin)->gte($hoy))->count(),
            'por_vencer' => $conGarantia->filter(fn($a) => Carbon::parse($a->garantia_fin)->between($hoy, $hoy->copy()->addDays(90)))
                ->map(fn($a) => [
                    'codigo' => $a->codigo_interno,
                    'fin'    => Carbon::parse($a->garantia_fin)->format('Y-m-d'),
                    'dias'   => $hoy->diffInDays(Carbon::parse($a->garantia_fin), false),
                ])->sortBy('dias')->values(),
            'vencidas'   => $conGarantia->filter(fn($a) => Carbon::parse($a->garantia_fin)->lt($hoy))->count(),
        ];

        // ── Mantenimientos abiertos / en proveedor ────────────────────
        $mantAbiertos = Mantenimiento::with('activo:id_activo,codigo_interno')
            ->whereIn('estado', Mantenimiento::ESTADOS_ABIERTOS)
            ->orderByDesc('id_mantenimiento')
            ->get()
            ->map(fn($m) => [
                'codigo'    => $m->codigo,
                'activo'    => $m->activo?->codigo_interno,
                'tipo'      => $m->tipo_mantenimiento,
                'estado'    => $m->estado,
                'proveedor' => $m->proveedor,
            ]);

        // ── Bajas: recomendadas y ejecutadas ──────────────────────────
        $bajas = BajaActivo::with('activo:id_activo,codigo_interno')
            ->orderByDesc('id_baja')
            ->get()
            ->map(fn($b) => [
                'codigo'        => $b->codigo,
                'activo'        => $b->activo?->codigo_interno,
                'causal'        => $b->causal_baja,
                'clasificacion' => $b->clasificacion_final,
                'estado'        => $b->estado,
                'fecha_baja'    => $b->fecha_baja?->format('Y-m-d'),
            ]);

        $bajaResumen = [
            'recomendadas' => $bajas->where('estado', 'RECOMENDADA')->count(),
            'validadas'    => $bajas->where('estado', 'VALIDADA')->count(),
            'ejecutadas'   => $bajas->where('estado', 'EJECUTADA')->count(),
        ];

        return view('content.reportes.index', compact(
            'kpis', 'distSituacion', 'distCondicion', 'porCategoria',
            'porResponsable', 'porUbicacion', 'prestamos', 'garantia',
            'mantAbiertos', 'bajas', 'bajaResumen'
        ));
    }
}
