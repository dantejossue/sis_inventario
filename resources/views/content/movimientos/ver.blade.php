@extends('layouts/contentNavbarLayout')

@section('title', 'Detalle de Movimiento - OTI')

@php
  use App\Models\Activo;

  $tipoBadge = ['PRESTAMO' => 'warning', 'TRANSFERENCIA' => 'info', 'REGULARIZACION' => 'primary'];
  $tipoLabel = ['PRESTAMO' => 'Préstamo', 'TRANSFERENCIA' => 'Transferencia', 'REGULARIZACION' => 'Regularización'];
  $estadoBadge = ['BORRADOR' => 'secondary', 'EJECUTADO' => 'success', 'OBSERVADO' => 'warning', 'CANCELADO' => 'dark'];
  $devBadge = [
      'NO_APLICA' => 'secondary', 'PENDIENTE_DEVOLUCION' => 'warning',
      'DEVUELTO' => 'success', 'DEVUELTO_OBSERVADO' => 'danger', 'VENCIDO' => 'danger',
  ];
  $devLabel = [
      'NO_APLICA' => 'No aplica', 'PENDIENTE_DEVOLUCION' => 'Pendiente',
      'DEVUELTO' => 'Devuelto', 'DEVUELTO_OBSERVADO' => 'Devuelto (obs.)', 'VENCIDO' => 'Vencido',
  ];
  $condBadge = ['NUEVO' => 'primary', 'BUENO' => 'success', 'REGULAR' => 'warning', 'MALO' => 'danger'];
  $resultadoBadge = [
      'PENDIENTE' => 'warning', 'APLICADO' => 'success', 'DEVUELTO' => 'info',
      'DEVUELTO_OBSERVADO' => 'danger', 'OBSERVADO' => 'warning', 'CANCELADO' => 'dark',
  ];

  $fmt = fn($f) => $f ? \Carbon\Carbon::parse($f)->format('d/m/Y') : '—';
  $nombreColab = fn($c) => $c ? trim("{$c->per_apepat} " . ($c->per_apemat ?? '') . ", {$c->per_nombre}") : '—';
  $responsableMov = $mov->registradoPor?->colaborador?->nombre_completo ?: $mov->registradoPor?->nombre_usuario ?: '—';
  $esPrestamoPendiente = $mov->tipo === 'PRESTAMO' && $mov->estado_devolucion === 'PENDIENTE_DEVOLUCION';
@endphp

@section('content')

  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">
        <span class="text-muted fw-light"><a class="text-secondary" href="{{ route('movimientos.index') }}">Movimientos</a> /</span>
        Detalle {{ $mov->codigo_movimiento }}
      </h4>
      <p class="text-muted mb-0">
        <span class="badge bg-label-{{ $tipoBadge[$mov->tipo] ?? 'secondary' }}">{{ $tipoLabel[$mov->tipo] ?? $mov->tipo }}</span>
        · Registrado el {{ $fmt($mov->fecha_movimiento ?: $mov->fecha_registro) }}
      </p>
    </div>
    <div class="d-flex gap-2 mt-3 mt-md-0">
      @if ($esPrestamoPendiente)
        <button class="btn btn-success btn-devolver" data-id="{{ $mov->id_movimiento }}"
          data-codigo="{{ $mov->codigo_movimiento }}">
          <i class="bx bx-undo me-1"></i> Registrar devolución
        </button>
      @endif
      <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Volver
      </a>
    </div>
  </div>

  <div class="row g-4">

    {{-- Datos del movimiento --}}
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header"><h5 class="mb-0">Datos del movimiento</h5></div>
        <div class="card-body">
          <div class="data-list">
            <div class="data-list-item"><span>Código</span><strong>{{ $mov->codigo_movimiento }}</strong></div>
            <div class="data-list-item"><span>Tipo</span>
              <span class="badge bg-label-{{ $tipoBadge[$mov->tipo] ?? 'secondary' }}">{{ $tipoLabel[$mov->tipo] ?? $mov->tipo }}</span>
            </div>
            <div class="data-list-item"><span>Estado</span>
              <span class="badge bg-label-{{ $estadoBadge[$mov->estado] ?? 'secondary' }}">{{ ucfirst(strtolower($mov->estado)) }}</span>
            </div>
            <div class="data-list-item"><span>Devolución</span>
              <span class="badge bg-label-{{ $devBadge[$mov->estado_devolucion] ?? 'secondary' }}">{{ $devLabel[$mov->estado_devolucion] ?? $mov->estado_devolucion }}</span>
            </div>
            <div class="data-list-item"><span>Responsable</span><strong>{{ $responsableMov }}</strong></div>
            <div class="data-list-item"><span>Fecha</span><strong>{{ $fmt($mov->fecha_movimiento) }}</strong></div>
            @if ($mov->tipo === 'PRESTAMO')
              <div class="data-list-item"><span>Dev. estimada</span><strong>{{ $fmt($mov->fecha_devolucion_estimada) }}</strong></div>
              <div class="data-list-item"><span>Dev. real</span><strong>{{ $fmt($mov->fecha_devolucion_real) }}</strong></div>
            @endif
          </div>

          @if ($mov->motivo || $mov->observaciones)
            <hr class="my-3">
            <span class="text-muted small">Motivo / observaciones</span>
            <p class="mb-0">{{ $mov->motivo ?: $mov->observaciones }}</p>
          @endif
        </div>
      </div>
    </div>

    {{-- Documentos de sustento --}}
    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-header"><h5 class="mb-0">Documentos de sustento</h5></div>
        <div class="card-body">
          @forelse ($mov->documentos->sortBy('creado_en') as $doc)
            <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
              <div class="d-flex align-items-center gap-2">
                <span class="avatar avatar-sm"><span class="avatar-initial rounded bg-label-primary"><i class="bx bx-file"></i></span></span>
                <div>
                  <strong class="d-block">{{ $doc->tipo_documento }}</strong>
                  <small class="text-muted">{{ $doc->nombre_original }} · {{ $doc->tamano_kb }} KB
                    · {{ $doc->subidoPor?->colaborador?->nombre_completo ?: $doc->subidoPor?->nombre_usuario }}</small>
                </div>
              </div>
              <a href="{{ route('documentos.download', $doc->id_documento) }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-download me-1"></i> Descargar
              </a>
            </div>
          @empty
            <p class="text-muted mb-0">Sin documentos de sustento.</p>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Tabla de activos del movimiento --}}
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom">
          <h5 class="mb-0 fw-bold">Activos del movimiento ({{ $mov->detalles->count() }})</h5>
          <small class="text-muted">Cada activo con su origen/destino y estado. Usa “Ver” para abrir su ficha completa.</small>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Activo</th>
                <th>Responsable (origen → destino)</th>
                <th>Ubicación (origen → destino)</th>
                <th>Condición (salida → retorno)</th>
                <th>Situación (antes → después)</th>
                <th>Resultado</th>
                <th class="text-end">Acción</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($mov->detalles as $d)
                @php $a = $d->activo; @endphp
                <tr>
                  <td>
                    <span class="fw-semibold d-block">{{ $a?->codigo_patrimonial ?? '—' }}</span>
                    <small class="text-muted">{{ $a?->codigo_interno }} ·
                      {{ trim(($a?->modelo?->marca?->nombre ?? '') . ' ' . ($a?->modelo?->nombre ?? '')) ?: '—' }}</small>
                  </td>
                  <td>{{ $nombreColab($d->responsableOrigen) }} <i class="bx bx-right-arrow-alt"></i>
                    <span class="fw-semibold">{{ $nombreColab($d->responsableDestino) }}</span></td>
                  <td>{{ $d->ubicacionOrigen?->nombre ?? '—' }} <i class="bx bx-right-arrow-alt"></i>
                    <span class="fw-semibold">{{ $d->ubicacionDestino?->nombre ?? '—' }}</span></td>
                  <td>
                    <span class="badge bg-label-{{ $condBadge[$d->condicion_salida] ?? 'secondary' }}">{{ Activo::CONDICION_LABELS[$d->condicion_salida] ?? '—' }}</span>
                    <i class="bx bx-right-arrow-alt"></i>
                    <span class="badge bg-label-{{ $condBadge[$d->condicion_retorno] ?? 'secondary' }}">{{ Activo::CONDICION_LABELS[$d->condicion_retorno] ?? '—' }}</span>
                  </td>
                  <td>
                    <small>{{ Activo::SITUACION_LABELS[$d->situacion_anterior] ?? '—' }} <i class="bx bx-right-arrow-alt"></i>
                      <span class="fw-semibold">{{ Activo::SITUACION_LABELS[$d->situacion_resultante] ?? '—' }}</span></small>
                  </td>
                  <td><span class="badge bg-label-{{ $resultadoBadge[$d->resultado] ?? 'secondary' }}">{{ ucfirst(strtolower(str_replace('_', ' ', $d->resultado))) }}</span></td>
                  <td class="text-end">
                    @if ($a)
                      <a href="{{ route('activos.ver', $a->id_activo) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-show me-1"></i> Ver
                      </a>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Sin activos.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  @include('content.movimientos.partials.modal-devolucion')
@endsection

@section('page-script')
  <script>
    window.routes = {
      devolver: '{{ route('movimientos.devolver', ['id' => '__ID__']) }}'
    };
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/movimientos/movimientos-devolucion.js'])
@endsection
