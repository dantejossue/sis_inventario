<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\BajaActivo;
use App\Models\Colaborador;
use App\Models\Mantenimiento;
use App\Models\Movimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Analytics extends Controller
{
  public function index()
  {
    if (auth()->user()->esProveedor()) {
      return redirect()->route('proveedor.dashboard');
    }

    // ── Distribución de activos por situación / condición / categoría ──────
    $porSituacion = Activo::select('situacion_actual', DB::raw('COUNT(*) as total'))
      ->groupBy('situacion_actual')
      ->pluck('total', 'situacion_actual');

    $porCondicion = Activo::select('condicion_actual', DB::raw('COUNT(*) as total'))
      ->groupBy('condicion_actual')
      ->pluck('total', 'condicion_actual');

    $porCategoria = Activo::select('categoria_activo.nombre', DB::raw('COUNT(*) as total'))
      ->join('categoria_activo', 'activo.id_categoria', '=', 'categoria_activo.id_categoria')
      ->groupBy('categoria_activo.nombre')
      ->orderByDesc('total')
      ->pluck('total', 'nombre');

    $totalActivos = (int) $porSituacion->sum();

    // ── Indicadores de proceso (procesos abiertos) ────────────────────────
    $mantAbiertosCount = Mantenimiento::whereIn('estado', Mantenimiento::ESTADOS_ABIERTOS)->count();
    $bajasAbiertasCount = BajaActivo::whereIn('estado', BajaActivo::ESTADOS_ABIERTOS)->count();

    // Préstamos ejecutados aún sin devolver.
    $prestamosVigentes = Movimiento::where('tipo', 'PRESTAMO')
      ->where('estado', 'EJECUTADO')
      ->where('estado_devolucion', 'PENDIENTE_DEVOLUCION')
      ->count();

    // ── Tarjetas KPI ───────────────────────────────────────────────────────
    $kpis = [
      [
        'titulo' => 'Total de activos',
        'valor'  => $totalActivos,
        'color'  => 'primary',
        'icono'  => 'bx-devices',
        'sub'    => ($porSituacion['DISPONIBLE'] ?? 0) . ' disponibles',
        'ruta'   => route('activos.index'),
      ],
      [
        'titulo' => 'En uso',
        'valor'  => $porSituacion['EN_USO'] ?? 0,
        'color'  => 'success',
        'icono'  => 'bx-user-check',
        'sub'    => 'Asignados a un responsable',
        'ruta'   => route('activos.index'),
      ],
      [
        'titulo' => 'En mantenimiento',
        'valor'  => $porSituacion['EN_MANTENIMIENTO'] ?? 0,
        'color'  => 'warning',
        'icono'  => 'bx-wrench',
        'sub'    => $mantAbiertosCount . ' procesos abiertos',
        'ruta'   => route('mantenimientos.index'),
      ],
      [
        'titulo' => 'Dados de baja',
        'valor'  => $porSituacion['DADO_DE_BAJA'] ?? 0,
        'color'  => 'danger',
        'icono'  => 'bx-down-arrow-circle',
        'sub'    => $bajasAbiertasCount . ' propuestas pendientes',
        'ruta'   => route('bajas.index'),
      ],
    ];

    // ── Datos para gráficos (ApexCharts) ───────────────────────────────────
    $situacionChart = [
      'labels' => $porSituacion->keys()->map(fn($k) => Activo::SITUACION_LABELS[$k] ?? $k)->values(),
      'data'   => $porSituacion->values(),
    ];

    $condicionChart = [
      'labels' => $porCondicion->keys()->map(fn($k) => Activo::CONDICION_LABELS[$k] ?? $k)->values(),
      'data'   => $porCondicion->values(),
    ];

    $categoriaChart = [
      'labels' => $porCategoria->keys()->values(),
      'data'   => $porCategoria->values(),
    ];

    // ── Tendencia: actividad de los últimos 6 meses ────────────────────────
    // Altas de activos, movimientos y mantenimientos agrupados por mes. Los
    // meses sin registros se rellenan con 0 para que la serie sea continua.
    $meses       = collect(range(5, 0))->map(fn($i) => Carbon::now()->startOfMonth()->subMonths($i));
    $mesesClaves = $meses->map(fn($m) => $m->format('Y-m'));
    $nombresMes  = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
    $mesesLabels = $meses->map(fn($m) => $nombresMes[$m->month] . ' ' . $m->format('y'));
    $desde       = $meses->first();

    $conteoPorMes = fn($modelo, $columna) => $modelo::query()
      ->where($columna, '>=', $desde)
      ->selectRaw("DATE_FORMAT($columna, '%Y-%m') as ym, COUNT(*) as total")
      ->groupBy('ym')
      ->pluck('total', 'ym');

    $altasPorMes = $conteoPorMes(Activo::class, 'creado_en');
    $movPorMes   = $conteoPorMes(Movimiento::class, 'fecha_registro');
    $mantPorMes  = $conteoPorMes(Mantenimiento::class, 'creado_en');

    $serie = fn($conteo) => $mesesClaves->map(fn($k) => (int) ($conteo[$k] ?? 0))->values();

    $actividadChart = [
      'labels'         => $mesesLabels->values(),
      'altas'          => $serie($altasPorMes),
      'movimientos'    => $serie($movPorMes),
      'mantenimientos' => $serie($mantPorMes),
    ];

    // ── Movimientos por tipo (histórico) ───────────────────────────────────
    $porTipoMov = Movimiento::select('tipo', DB::raw('COUNT(*) as total'))
      ->groupBy('tipo')
      ->pluck('total', 'tipo');

    $movTipoChart = [
      'labels' => $porTipoMov->keys()->map(fn($t) => ucfirst(strtolower($t)))->values(),
      'data'   => $porTipoMov->values(),
    ];

    // ── Rankings: responsables y ubicaciones con más activos ───────────────
    $topResponsables = Activo::query()
      ->join('colaboradores', 'activo.id_responsable_actual', '=', 'colaboradores.id_colaborador')
      ->selectRaw("CONCAT(colaboradores.per_apepat, ', ', colaboradores.per_nombre) as nombre, COUNT(*) as total")
      ->groupBy('nombre')
      ->orderByDesc('total')
      ->limit(6)
      ->get();

    $topResponsablesChart = [
      'labels' => $topResponsables->pluck('nombre')->values(),
      'data'   => $topResponsables->pluck('total')->map(fn($v) => (int) $v)->values(),
    ];

    $topUbicaciones = Activo::query()
      ->join('ubicaciones', 'activo.id_ubicacion_actual', '=', 'ubicaciones.id_ubicacion')
      ->selectRaw('ubicaciones.nombre as nombre, COUNT(*) as total')
      ->groupBy('nombre')
      ->orderByDesc('total')
      ->limit(6)
      ->get();

    $topUbicacionesChart = [
      'labels' => $topUbicaciones->pluck('nombre')->values(),
      'data'   => $topUbicaciones->pluck('total')->map(fn($v) => (int) $v)->values(),
    ];

    // ── Valor referencial del inventario por categoría ─────────────────────
    $valorPorCategoria = Activo::query()
      ->join('categoria_activo', 'activo.id_categoria', '=', 'categoria_activo.id_categoria')
      ->selectRaw('categoria_activo.nombre as nombre, SUM(COALESCE(valor_compra, 0)) as total')
      ->groupBy('nombre')
      ->orderByDesc('total')
      ->get();

    $valorCategoriaChart = [
      'labels' => $valorPorCategoria->pluck('nombre')->values(),
      'data'   => $valorPorCategoria->pluck('total')->map(fn($v) => round((float) $v, 2))->values(),
    ];

    // ── Valor referencial del inventario ───────────────────────────────────
    $valorInventario = (float) Activo::sum('valor_compra');

    // ── Garantías próximas a vencer (siguientes 90 días) ───────────────────
    $hoy = Carbon::today();
    $garantiasPorVencer = Activo::with('modelo.marca')
      ->whereNotNull('garantia_fin')
      ->whereBetween('garantia_fin', [$hoy, $hoy->copy()->addDays(90)])
      ->orderBy('garantia_fin')
      ->take(6)
      ->get();

    // ── Últimos movimientos registrados ────────────────────────────────────
    $ultimosMovimientos = Movimiento::withCount('detalles')
      ->orderByDesc('fecha_registro')
      ->take(6)
      ->get();

    // ── Mantenimientos abiertos (equipos intervenidos) ─────────────────────
    $mantenimientosAbiertos = Mantenimiento::with('activo.modelo.marca', 'tecnicoResponsable')
      ->whereIn('estado', Mantenimiento::ESTADOS_ABIERTOS)
      ->orderByDesc('id_mantenimiento')
      ->take(6)
      ->get();

    $colaboradoresActivos = Colaborador::where('estado', 'ACTIVO')->count();

    return view('content.dashboard.dashboards-analytics', compact(
      'kpis',
      'situacionChart',
      'condicionChart',
      'categoriaChart',
      'actividadChart',
      'movTipoChart',
      'topResponsablesChart',
      'topUbicacionesChart',
      'valorCategoriaChart',
      'valorInventario',
      'totalActivos',
      'prestamosVigentes',
      'colaboradoresActivos',
      'garantiasPorVencer',
      'ultimosMovimientos',
      'mantenimientosAbiertos'
    ));
  }
}
