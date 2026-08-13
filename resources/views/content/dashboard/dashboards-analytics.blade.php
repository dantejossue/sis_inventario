@extends('layouts/contentNavbarLayout')

@section('title', 'Panel de control - OTI')

@section('vendor-style')
  @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
  @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')

  @php
    $usuario = auth()->user()->colaborador?->nombre_completo ?? auth()->user()->nombre_usuario;

    $mantEstadoBadge = [
        'REGISTRADO' => 'bg-label-secondary',
        'EN_ATENCION' => 'bg-label-warning',
    ];
    $movTipoBadge = [
        'PRESTAMO' => 'bg-label-info',
        'TRANSFERENCIA' => 'bg-label-primary',
        'REGULARIZACION' => 'bg-label-secondary',
    ];
    $movEstadoBadge = [
        'BORRADOR' => 'bg-label-secondary',
        'EJECUTADO' => 'bg-label-success',
        'OBSERVADO' => 'bg-label-warning',
        'CANCELADO' => 'bg-label-danger',
    ];
  @endphp

  {{-- ── Bienvenida ─────────────────────────────────────────────────────── --}}
  <div class="row">
    <div class="col-12 mb-4">
      <div class="card">
        <div class="d-flex align-items-end row">
          <div class="col-sm-8">
            <div class="card-body">
              <h5 class="card-title text-primary mb-2">¡Hola, {{ $usuario }}! 👋</h5>
              <p class="mb-3">
                Este es el panel de control del inventario de activos tecnológicos.
                Se tiene <strong>{{ $totalActivos }}</strong> activos registrados y
                <strong>{{ $prestamosVigentes }}</strong> préstamos pendientes de devolución.
              </p>
              <a href="{{ route('activos.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-devices me-1"></i> Ver activos
              </a>
            </div>
          </div>
          <div class="col-sm-4 text-center text-sm-end">
            <div class="card-body pb-0 px-0 px-md-4">
              <img src="{{ asset('assets/img/illustrations/man-with-laptop.png') }}" height="150"
                alt="Panel de inventario" onerror="this.style.display='none'" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── KPIs ───────────────────────────────────────────────────────────── --}}
  <div class="row g-4 mb-4">
    @foreach ($kpis as $kpi)
      <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ $kpi['ruta'] }}" class="text-reset text-decoration-none">
          <div class="card h-100">
            <div class="card-body">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <span class="fw-semibold d-block mb-1">{{ $kpi['titulo'] }}</span>
                  <h3 class="card-title mb-2">{{ $kpi['valor'] }}</h3>
                  <small class="text-muted fw-semibold">{{ $kpi['sub'] }}</small>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-{{ $kpi['color'] }}">
                    <i class="bx {{ $kpi['icono'] }} fs-3"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </a>
      </div>
    @endforeach
  </div>

  {{-- ── Gráficos ───────────────────────────────────────────────────────── --}}
  <div class="row g-4 mb-4">
    {{-- Activos por categoría --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h5 class="mb-0">Activos por categoría</h5>
            <small class="text-muted">Distribución del parque tecnológico</small>
          </div>
          <i class="bx bx-category fs-4 text-primary"></i>
        </div>
        <div class="card-body">
          @if ($categoriaChart['data']->isEmpty())
            <p class="text-muted mb-0 text-center py-5">Aún no hay activos registrados.</p>
          @else
            <div id="categoriaChart"></div>
          @endif
        </div>
      </div>
    </div>

    {{-- Activos por situación --}}
    <div class="col-lg-3 col-md-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Por situación</h5>
          <small class="text-muted">Estado operativo</small>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          @if ($situacionChart['data']->isEmpty())
            <p class="text-muted mb-0">Sin datos.</p>
          @else
            <div id="situacionChart" class="w-100"></div>
          @endif
        </div>
      </div>
    </div>

    {{-- Activos por condición --}}
    <div class="col-lg-3 col-md-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Por condición</h5>
          <small class="text-muted">Estado físico</small>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          @if ($condicionChart['data']->isEmpty())
            <p class="text-muted mb-0">Sin datos.</p>
          @else
            <div id="condicionChart" class="w-100"></div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ── Tendencias temporales ──────────────────────────────────────────── --}}
  <div class="row g-4 mb-4">
    {{-- Actividad de los últimos 6 meses --}}
    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h5 class="mb-0">Actividad de los últimos 6 meses</h5>
            <small class="text-muted">Altas de activos, movimientos y mantenimientos por mes</small>
          </div>
          <i class="bx bx-line-chart fs-4 text-primary"></i>
        </div>
        <div class="card-body">
          <div id="actividadChart"></div>
        </div>
      </div>
    </div>

    {{-- Movimientos por tipo --}}
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Movimientos por tipo</h5>
          <small class="text-muted">Distribución histórica</small>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          @if ($movTipoChart['data']->isEmpty())
            <p class="text-muted mb-0 py-5">Aún no hay movimientos.</p>
          @else
            <div id="movTipoChart" class="w-100"></div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ── Rankings y valor ───────────────────────────────────────────────── --}}
  <div class="row g-4 mb-4">
    {{-- Top responsables --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h5 class="mb-0">Responsables con más activos</h5>
            <small class="text-muted">Top 6 por número de activos asignados</small>
          </div>
          <i class="bx bx-user fs-4 text-primary"></i>
        </div>
        <div class="card-body">
          @if ($topResponsablesChart['data']->isEmpty())
            <p class="text-muted mb-0 text-center py-5">Sin activos asignados.</p>
          @else
            <div id="topResponsablesChart"></div>
          @endif
        </div>
      </div>
    </div>

    {{-- Top ubicaciones --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h5 class="mb-0">Ubicaciones con más activos</h5>
            <small class="text-muted">Top 6 por número de activos</small>
          </div>
          <i class="bx bx-map fs-4 text-primary"></i>
        </div>
        <div class="card-body">
          @if ($topUbicacionesChart['data']->isEmpty())
            <p class="text-muted mb-0 text-center py-5">Sin activos ubicados.</p>
          @else
            <div id="topUbicacionesChart"></div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ── Valor del inventario por categoría ──────────────────────────────── --}}
  @if ($valorCategoriaChart['data']->sum() > 0)
    <div class="row g-4 mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div>
              <h5 class="mb-0">Valor del inventario por categoría</h5>
              <small class="text-muted">Suma referencial del valor de compra (S/)</small>
            </div>
            <i class="bx bx-money fs-4 text-success"></i>
          </div>
          <div class="card-body">
            <div id="valorCategoriaChart"></div>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- ── Tablas de seguimiento ──────────────────────────────────────────── --}}
  <div class="row g-4 mb-4">
    {{-- Últimos movimientos --}}
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0">Últimos movimientos</h5>
          <a href="{{ route('movimientos.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Código</th>
                <th>Tipo</th>
                <th>Activos</th>
                <th>Estado</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($ultimosMovimientos as $mov)
                <tr>
                  <td class="fw-semibold">{{ $mov->codigo_movimiento ?: '#' . $mov->id_movimiento }}</td>
                  <td>
                    <span class="badge {{ $movTipoBadge[$mov->tipo] ?? 'bg-label-secondary' }}">
                      {{ ucfirst(strtolower($mov->tipo)) }}
                    </span>
                  </td>
                  <td>{{ $mov->detalles_count }}</td>
                  <td>
                    <span class="badge {{ $movEstadoBadge[$mov->estado] ?? 'bg-label-secondary' }}">
                      {{ ucfirst(strtolower($mov->estado)) }}
                    </span>
                  </td>
                  <td class="text-muted">{{ optional($mov->fecha_registro)->format('d/m/Y') ?? '—' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">No hay movimientos registrados.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Mantenimientos abiertos --}}
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0">Mantenimientos abiertos</h5>
          <a href="{{ route('mantenimientos.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
        </div>
        <div class="card-body">
          @forelse ($mantenimientosAbiertos as $mant)
            <div class="d-flex align-items-center mb-4">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-wrench"></i></span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <h6 class="mb-0">{{ $mant->codigo }}</h6>
                  <small class="text-muted">
                    {{ $mant->activo?->codigo_patrimonial ?? 'Activo #' . $mant->id_activo }}
                    · {{ $mant->activo?->modelo?->nombre ?? '' }}
                  </small>
                </div>
                <span class="badge {{ $mantEstadoBadge[$mant->estado] ?? 'bg-label-secondary' }}">
                  {{ ucfirst(strtolower(str_replace('_', ' ', $mant->estado))) }}
                </span>
              </div>
            </div>
          @empty
            <p class="text-muted text-center py-4 mb-0">
              <i class="bx bx-check-circle fs-3 d-block mb-2 text-success"></i>
              No hay mantenimientos abiertos.
            </p>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- ── Garantías por vencer ───────────────────────────────────────────── --}}
  @if ($garantiasPorVencer->isNotEmpty())
    <div class="row g-4 mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div>
              <h5 class="mb-0">Garantías por vencer</h5>
              <small class="text-muted">Activos cuya garantía termina en los próximos 90 días</small>
            </div>
            <i class="bx bx-shield-x fs-4 text-warning"></i>
          </div>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Código patrimonial</th>
                  <th>Equipo</th>
                  <th>Fin de garantía</th>
                  <th>Días restantes</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($garantiasPorVencer as $act)
                  @php $dias = \Carbon\Carbon::today()->diffInDays($act->garantia_fin, false); @endphp
                  <tr>
                    <td class="fw-semibold">{{ $act->codigo_patrimonial ?: '—' }}</td>
                    <td>{{ trim(($act->modelo?->marca?->nombre ?? '') . ' ' . ($act->modelo?->nombre ?? '')) ?: '—' }}
                    </td>
                    <td>{{ $act->garantia_fin->format('d/m/Y') }}</td>
                    <td>
                      <span class="badge {{ $dias <= 30 ? 'bg-label-danger' : 'bg-label-warning' }}">
                        {{ (int) $dias }} días
                      </span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  @endif

@endsection

@section('page-script')
  <script>
    (function initDash() {
      // ApexCharts se carga con Vite como modulo (type=module), que se ejecuta
      // de forma diferida; este script clasico puede correr antes. Si aun no
      // esta disponible, reintenta hasta que cargue y recien ahi dibuja.
      if (typeof ApexCharts === 'undefined') {
        initDash.intentos = (initDash.intentos || 0) + 1;
        if (initDash.intentos <= 100) setTimeout(initDash, 100);
        return;
      }

      const paleta = ['#696cff', '#03c3e3', '#71dd37', '#ffab00', '#ff3e1d', '#8592a3', '#233446'];
      const ejeColor = '#a1acb8';

      // ── Activos por categoría (barras horizontales) ──────────────────────
      const catEl = document.querySelector('#categoriaChart');
      if (catEl) {
        new ApexCharts(catEl, {
          chart: {
            type: 'bar',
            height: 320,
            toolbar: {
              show: false
            }
          },
          series: [{
            name: 'Activos',
            data: @json($categoriaChart['data'])
          }],
          plotOptions: {
            bar: {
              horizontal: true,
              borderRadius: 6,
              barHeight: '60%',
              distributed: true
            }
          },
          dataLabels: {
            enabled: true
          },
          legend: {
            show: false
          },
          colors: paleta,
          xaxis: {
            categories: @json($categoriaChart['labels']),
            labels: {
              style: {
                colors: ejeColor
              }
            }
          },
          yaxis: {
            labels: {
              style: {
                colors: ejeColor
              }
            }
          },
          grid: {
            borderColor: 'rgba(0,0,0,.06)'
          }
        }).render();
      }

      // ── Activos por situación (dona) ─────────────────────────────────────
      const sitEl = document.querySelector('#situacionChart');
      if (sitEl) {
        new ApexCharts(sitEl, {
          chart: {
            type: 'donut',
            height: 260
          },
          series: @json($situacionChart['data']),
          labels: @json($situacionChart['labels']),
          colors: paleta,
          legend: {
            position: 'bottom',
            labels: {
              colors: ejeColor
            }
          },
          dataLabels: {
            enabled: true
          },
          plotOptions: {
            pie: {
              donut: {
                size: '65%'
              }
            }
          }
        }).render();
      }

      // ── Activos por condición (dona) ─────────────────────────────────────
      const condEl = document.querySelector('#condicionChart');
      if (condEl) {
        new ApexCharts(condEl, {
          chart: {
            type: 'donut',
            height: 260
          },
          series: @json($condicionChart['data']),
          labels: @json($condicionChart['labels']),
          colors: ['#71dd37', '#03c3e3', '#ffab00', '#ff3e1d'],
          legend: {
            position: 'bottom',
            labels: {
              colors: ejeColor
            }
          },
          dataLabels: {
            enabled: true
          },
          plotOptions: {
            pie: {
              donut: {
                size: '65%'
              }
            }
          }
        }).render();
      }

      // ── Actividad de los últimos 6 meses (líneas) ────────────────────────
      const actEl = document.querySelector('#actividadChart');
      if (actEl) {
        new ApexCharts(actEl, {
          chart: {
            type: 'line',
            height: 320,
            toolbar: {
              show: false
            }
          },
          series: [{
              name: 'Altas de activos',
              data: @json($actividadChart['altas'])
            },
            {
              name: 'Movimientos',
              data: @json($actividadChart['movimientos'])
            },
            {
              name: 'Mantenimientos',
              data: @json($actividadChart['mantenimientos'])
            }
          ],
          colors: ['#696cff', '#03c3e3', '#ffab00'],
          stroke: {
            curve: 'smooth',
            width: 3
          },
          markers: {
            size: 4
          },
          dataLabels: {
            enabled: false
          },
          legend: {
            position: 'top',
            labels: {
              colors: ejeColor
            }
          },
          xaxis: {
            categories: @json($actividadChart['labels']),
            labels: {
              style: {
                colors: ejeColor
              }
            }
          },
          yaxis: {
            labels: {
              style: {
                colors: ejeColor
              }
            },
            min: 0,
            forceNiceScale: true
          },
          grid: {
            borderColor: 'rgba(0,0,0,.06)'
          }
        }).render();
      }

      // ── Movimientos por tipo (dona) ──────────────────────────────────────
      const movTipoEl = document.querySelector('#movTipoChart');
      if (movTipoEl) {
        new ApexCharts(movTipoEl, {
          chart: {
            type: 'donut',
            height: 260
          },
          series: @json($movTipoChart['data']),
          labels: @json($movTipoChart['labels']),
          colors: ['#03c3e3', '#696cff', '#8592a3'],
          legend: {
            position: 'bottom',
            labels: {
              colors: ejeColor
            }
          },
          dataLabels: {
            enabled: true
          },
          plotOptions: {
            pie: {
              donut: {
                size: '65%'
              }
            }
          }
        }).render();
      }

      // ── Top responsables (barras horizontales) ───────────────────────────
      const respEl = document.querySelector('#topResponsablesChart');
      if (respEl) {
        new ApexCharts(respEl, {
          chart: {
            type: 'bar',
            height: 320,
            toolbar: {
              show: false
            }
          },
          series: [{
            name: 'Activos',
            data: @json($topResponsablesChart['data'])
          }],
          plotOptions: {
            bar: {
              horizontal: true,
              borderRadius: 6,
              barHeight: '60%',
              distributed: true
            }
          },
          dataLabels: {
            enabled: true
          },
          legend: {
            show: false
          },
          colors: paleta,
          xaxis: {
            categories: @json($topResponsablesChart['labels']),
            labels: {
              style: {
                colors: ejeColor
              }
            }
          },
          yaxis: {
            labels: {
              style: {
                colors: ejeColor
              }
            }
          },
          grid: {
            borderColor: 'rgba(0,0,0,.06)'
          }
        }).render();
      }

      // ── Top ubicaciones (barras horizontales) ────────────────────────────
      const ubiEl = document.querySelector('#topUbicacionesChart');
      if (ubiEl) {
        new ApexCharts(ubiEl, {
          chart: {
            type: 'bar',
            height: 320,
            toolbar: {
              show: false
            }
          },
          series: [{
            name: 'Activos',
            data: @json($topUbicacionesChart['data'])
          }],
          plotOptions: {
            bar: {
              horizontal: true,
              borderRadius: 6,
              barHeight: '60%',
              distributed: true
            }
          },
          dataLabels: {
            enabled: true
          },
          legend: {
            show: false
          },
          colors: paleta,
          xaxis: {
            categories: @json($topUbicacionesChart['labels']),
            labels: {
              style: {
                colors: ejeColor
              }
            }
          },
          yaxis: {
            labels: {
              style: {
                colors: ejeColor
              }
            }
          },
          grid: {
            borderColor: 'rgba(0,0,0,.06)'
          }
        }).render();
      }

      // ── Valor del inventario por categoría (barras) ──────────────────────
      const valCatEl = document.querySelector('#valorCategoriaChart');
      if (valCatEl) {
        const soles = val => 'S/ ' + Number(val).toLocaleString('es-PE', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
        new ApexCharts(valCatEl, {
          chart: {
            type: 'bar',
            height: 320,
            toolbar: {
              show: false
            }
          },
          series: [{
            name: 'Valor (S/)',
            data: @json($valorCategoriaChart['data'])
          }],
          plotOptions: {
            bar: {
              horizontal: false,
              borderRadius: 6,
              columnWidth: '45%',
              distributed: true
            }
          },
          dataLabels: {
            enabled: false
          },
          legend: {
            show: false
          },
          colors: paleta,
          xaxis: {
            categories: @json($valorCategoriaChart['labels']),
            labels: {
              style: {
                colors: ejeColor
              }
            }
          },
          yaxis: {
            labels: {
              style: {
                colors: ejeColor
              },
              formatter: soles
            }
          },
          tooltip: {
            y: {
              formatter: soles
            }
          },
          grid: {
            borderColor: 'rgba(0,0,0,.06)'
          }
        }).render();
      }
    })();
  </script>
@endsection
