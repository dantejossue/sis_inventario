@extends('layouts/contentNavbarLayout')

@section('title', 'Reportes - OTI')

@php
  $sitBadge = [
      'DISPONIBLE' => 'success',
      'EN_USO' => 'primary',
      'EN_PRESTAMO' => 'info',
      'EN_MANTENIMIENTO' => 'warning',
      'EN_PROVEEDOR' => 'warning',
      'OBSERVADO' => 'danger',
      'DADO_DE_BAJA' => 'secondary',
  ];
  $condBadge = ['NUEVO' => 'primary', 'BUENO' => 'success', 'REGULAR' => 'warning', 'MALO' => 'danger'];
  $estadoBajaBadge = [
      'REGISTRADA' => 'warning', 'EN_EVALUACION' => 'info', 'RECOMENDADA' => 'primary',
      'VALIDADA' => 'success', 'EJECUTADA' => 'dark', 'RECHAZADA' => 'secondary',
  ];
  $legible = fn($v) => ucfirst(strtolower(str_replace('_', ' ', (string) $v)));
@endphp

@section('content')

  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">
        <span class="text-muted fw-light">Reportería y Auditoría /</span>
        Reportes OTI
      </h4>
      <p class="text-muted mb-0">Panorama operativo de los activos tecnológicos bajo custodia de OTI.</p>
    </div>
    <button class="btn btn-outline-secondary mt-3 mt-md-0" onclick="window.print()">
      <i class="bx bx-printer me-1"></i> Imprimir / PDF
    </button>
  </div>

  {{-- ── KPIs por situación ─────────────────────────────────────────── --}}
  <div class="row g-3 mb-2">
    @php
      $tarjetas = [
          ['Total activos', $kpis['total'], 'primary', 'bx-devices'],
          ['Disponibles', $kpis['disponible'], 'success', 'bx-check-circle'],
          ['En uso', $kpis['en_uso'], 'info', 'bx-user-check'],
          ['En préstamo', $kpis['en_prestamo'], 'info', 'bx-time-five'],
          ['En mantenimiento', $kpis['en_mantenimiento'], 'warning', 'bx-wrench'],
          ['En proveedor', $kpis['en_proveedor'], 'warning', 'bx-store'],
          ['Observados', $kpis['observado'], 'danger', 'bx-error-circle'],
          ['Dados de baja', $kpis['dado_de_baja'], 'secondary', 'bx-x-circle'],
      ];
    @endphp
    @foreach ($tarjetas as [$titulo, $valor, $color, $icono])
      <div class="col-xl-3 col-md-4 col-sm-6">
        <div class="card h-100">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <span class="d-block text-muted small">{{ $titulo }}</span>
              <h3 class="mb-0">{{ $valor }}</h3>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-{{ $color }}"><i class="bx {{ $icono }}"></i></span></div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="row g-4">

    {{-- ── Distribución por situación ──────────────────────────────── --}}
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header"><h5 class="mb-0">Por situación</h5></div>
        <div class="card-body">
          @forelse ($distSituacion as $r)
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span><span class="badge bg-label-{{ $sitBadge[$r['clave']] ?? 'secondary' }} me-2">{{ $r['etiqueta'] }}</span></span>
              <div class="d-flex align-items-center gap-2" style="flex:1;max-width:60%">
                <div class="progress w-100" style="height:8px">
                  <div class="progress-bar bg-{{ $sitBadge[$r['clave']] ?? 'secondary' }}"
                    style="width: {{ $kpis['total'] ? round($r['total'] / $kpis['total'] * 100) : 0 }}%"></div>
                </div>
                <strong>{{ $r['total'] }}</strong>
              </div>
            </div>
          @empty
            <p class="text-muted mb-0">Sin activos registrados.</p>
          @endforelse
        </div>
      </div>
    </div>

    {{-- ── Distribución por condición ──────────────────────────────── --}}
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header"><h5 class="mb-0">Por condición</h5></div>
        <div class="card-body">
          @forelse ($distCondicion as $r)
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="badge bg-label-{{ $condBadge[$r['clave']] ?? 'secondary' }}">{{ $r['etiqueta'] }}</span>
              <strong>{{ $r['total'] }}</strong>
            </div>
          @empty
            <p class="text-muted mb-0">Sin activos registrados.</p>
          @endforelse
        </div>
      </div>
    </div>

    {{-- ── Por categoría ───────────────────────────────────────────── --}}
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header"><h5 class="mb-0">Por categoría</h5></div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0">
            <tbody>
              @forelse ($porCategoria as $c)
                <tr>
                  <td>{{ $c->nombre }}</td>
                  <td class="text-end fw-semibold">{{ $c->total }}</td>
                </tr>
              @empty
                <tr><td class="text-muted">Sin datos.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ── Préstamos pendientes de devolución ──────────────────────── --}}
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0">Préstamos pendientes de devolución</h5>
            <small class="text-muted">Los vencidos aparecen resaltados.</small>
          </div>
          <span class="badge bg-label-danger">{{ $prestamos->where('vencido', true)->count() }} vencido(s)</span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Movimiento</th>
                <th>Activo(s)</th>
                <th>Responsable</th>
                <th>Devolución estimada</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($prestamos as $p)
                <tr @class(['table-danger' => $p['vencido']])>
                  <td class="fw-semibold">{{ $p['codigo'] }}</td>
                  <td>{{ $p['activos'] ?: '—' }}</td>
                  <td>{{ $p['responsable'] }}</td>
                  <td>{{ $p['estimada'] ?? '—' }}</td>
                  <td>
                    @if ($p['vencido'])
                      <span class="badge bg-danger">Vencido ({{ abs($p['dias']) }} días)</span>
                    @else
                      <span class="badge bg-label-info">Vigente{{ $p['dias'] !== null ? ' · ' . $p['dias'] . ' días' : '' }}</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-muted text-center py-3">Sin préstamos pendientes.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ── Garantías por vencer ────────────────────────────────────── --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Garantías</h5>
          <small class="text-muted">
            {{ $garantia['vigentes'] }} vigentes · {{ $garantia['por_vencer']->count() }} por vencer (90 días) ·
            {{ $garantia['vencidas'] }} vencidas
          </small>
        </div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead>
              <tr><th>Activo</th><th>Fin garantía</th><th class="text-end">Días</th></tr>
            </thead>
            <tbody>
              @forelse ($garantia['por_vencer'] as $g)
                <tr>
                  <td class="fw-semibold">{{ $g['codigo'] }}</td>
                  <td>{{ $g['fin'] }}</td>
                  <td class="text-end"><span class="badge bg-label-warning">{{ $g['dias'] }} días</span></td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-muted text-center py-3">Ninguna garantía por vencer en 90 días.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ── Mantenimientos abiertos ─────────────────────────────────── --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header"><h5 class="mb-0">Mantenimientos en curso</h5></div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead>
              <tr><th>Código</th><th>Activo</th><th>Tipo</th><th>Estado</th></tr>
            </thead>
            <tbody>
              @forelse ($mantAbiertos as $m)
                <tr>
                  <td class="fw-semibold">{{ $m['codigo'] }}</td>
                  <td>{{ $m['activo'] ?? '—' }}</td>
                  <td>{{ $legible($m['tipo']) }}</td>
                  <td><span class="badge bg-label-warning">{{ $legible($m['estado']) }}</span></td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-muted text-center py-3">Sin mantenimientos en curso.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ── Bajas técnicas ──────────────────────────────────────────── --}}
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Bajas técnicas</h5>
          <span class="text-muted small">
            {{ $bajaResumen['recomendadas'] }} recomendadas · {{ $bajaResumen['validadas'] }} validadas ·
            {{ $bajaResumen['ejecutadas'] }} ejecutadas
          </span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr><th>Código</th><th>Activo</th><th>Causal</th><th>Clasificación</th><th>Estado</th><th>Fecha baja</th></tr>
            </thead>
            <tbody>
              @forelse ($bajas as $b)
                <tr>
                  <td class="fw-semibold">{{ $b['codigo'] }}</td>
                  <td>{{ $b['activo'] ?? '—' }}</td>
                  <td>{{ $legible($b['causal']) }}</td>
                  <td>{{ $b['clasificacion'] ? $legible($b['clasificacion']) : '—' }}</td>
                  <td><span class="badge bg-label-{{ $estadoBajaBadge[$b['estado']] ?? 'secondary' }}">{{ $legible($b['estado']) }}</span></td>
                  <td>{{ $b['fecha_baja'] ?? '—' }}</td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-muted text-center py-3">Sin bajas registradas.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ── Por responsable / ubicación ─────────────────────────────── --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header"><h5 class="mb-0">Activos por responsable</h5></div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <tbody>
              @forelse ($porResponsable as $r)
                <tr><td>{{ $r->nombre }}</td><td class="text-end fw-semibold">{{ $r->total }}</td></tr>
              @empty
                <tr><td class="text-muted text-center py-3">Sin activos con responsable asignado.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header"><h5 class="mb-0">Activos por ubicación</h5></div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <tbody>
              @forelse ($porUbicacion as $u)
                <tr><td>{{ $u->nombre }}</td><td class="text-end fw-semibold">{{ $u->total }}</td></tr>
              @empty
                <tr><td class="text-muted text-center py-3">Sin activos con ubicación asignada.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

@endsection
