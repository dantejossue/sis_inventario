@extends('layouts/contentNavbarLayout')

@section('title', 'Detalle de Movimiento - OTI')

@php
  use App\Models\Activo;

  $tipoBadge = [
      'PRESTAMO' => 'warning',
      'TRANSFERENCIA' => 'info',
      'REGULARIZACION' => 'primary',
  ];

  $tipoLabel = [
      'PRESTAMO' => 'Préstamo',
      'TRANSFERENCIA' => 'Transferencia',
      'REGULARIZACION' => 'Regularización',
  ];

  $tipoIcon = [
      'PRESTAMO' => 'bx-time-five',
      'TRANSFERENCIA' => 'bx-transfer-alt',
      'REGULARIZACION' => 'bx-edit-alt',
  ];

  $estadoBadge = [
      'BORRADOR' => 'secondary',
      'EJECUTADO' => 'success',
      'OBSERVADO' => 'warning',
      'CANCELADO' => 'dark',
  ];

  $estadoLabel = [
      'BORRADOR' => 'Borrador',
      'EJECUTADO' => 'Registrado',
      'OBSERVADO' => 'Observado',
      'CANCELADO' => 'Cancelado',
  ];

  $devBadge = [
      'NO_APLICA' => 'secondary',
      'PENDIENTE_DEVOLUCION' => 'warning',
      'DEVUELTO' => 'success',
      'DEVUELTO_OBSERVADO' => 'danger',
      'VENCIDO' => 'danger',
  ];

  $devLabel = [
      'NO_APLICA' => 'No aplica',
      'PENDIENTE_DEVOLUCION' => 'Pendiente',
      'DEVUELTO' => 'Devuelto',
      'DEVUELTO_OBSERVADO' => 'Devuelto con observaciones',
      'VENCIDO' => 'Vencido',
  ];

  $condBadge = [
      'NUEVO' => 'primary',
      'BUENO' => 'success',
      'REGULAR' => 'warning',
      'MALO' => 'danger',
  ];

  $resultadoBadge = [
      'PENDIENTE' => 'warning',
      'APLICADO' => 'success',
      'DEVUELTO' => 'info',
      'DEVUELTO_OBSERVADO' => 'danger',
      'OBSERVADO' => 'warning',
      'CANCELADO' => 'dark',
  ];

  $resultadoLabel = [
      'PENDIENTE' => 'Pendiente',
      'APLICADO' => 'Aplicado',
      'DEVUELTO' => 'Devuelto',
      'DEVUELTO_OBSERVADO' => 'Devuelto observado',
      'OBSERVADO' => 'Observado',
      'CANCELADO' => 'Cancelado',
  ];

  $fmt = fn($fecha) => $fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y') : '—';

  $fmtFechaHora = fn($fecha) => $fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y H:i') : '—';

  $nombreColab = fn($colaborador) => $colaborador
      ? trim("{$colaborador->per_apepat} " . ($colaborador->per_apemat ?? '') . ", {$colaborador->per_nombre}")
      : '—';

  $registradoPor = $mov->registradoPor?->colaborador?->nombre_completo ?: $mov->registradoPor?->nombre_usuario ?: '—';

  $esPrestamo = $mov->tipo === 'PRESTAMO';

  $esPrestamoPendiente = $esPrestamo && $mov->estado_devolucion === 'PENDIENTE_DEVOLUCION';

  $documentos = $mov->documentos->sortBy('creado_en');
@endphp

@section('content')

  {{-- Encabezado --}}
  <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">

    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('movimientos.index') }}" class="btn btn-sm btn-icon btn-secondary" title="Volver">

          <i class="bx bx-arrow-back"></i>
        </a>

        <div>
          <div class="d-flex align-items-center flex-wrap gap-2">
            <h4 class="fw-bold mb-0">
              {{ $mov->codigo_movimiento }}
            </h4>

            {{-- <span class="badge bg-label-{{ $tipoBadge[$mov->tipo] ?? 'secondary' }}">
              <i class="bx {{ $tipoIcon[$mov->tipo] ?? 'bx-transfer' }} me-1"></i>
              {{ $tipoLabel[$mov->tipo] ?? $mov->tipo }}
            </span>

            <span class="badge bg-label-{{ $estadoBadge[$mov->estado] ?? 'secondary' }}">
              {{ $estadoLabel[$mov->estado] ?? $mov->estado }}
            </span> --}}
          </div>

          <p class="text-muted mb-0 m-0">
            Detalle y trazabilidad de los activos involucrados en el movimiento.
          </p>
        </div>
      </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
      @if ($esPrestamoPendiente)
        <button type="button" class="btn btn-success btn-devolver" data-id="{{ $mov->id_movimiento }}"
          data-codigo="{{ $mov->codigo_movimiento }}">

          <i class="bx bx-undo me-1"></i>
          Registrar devolución
        </button>
      @endif

      <a href="{{ route('movimientos.index') }}" class="btn btn-secondary">

        <i class="bx bx-list-ul me-1"></i>
        Ver movimientos
      </a>
    </div>
  </div>

  {{-- Resumen superior --}}
  <div class="row g-4 mb-4">

    {{-- Información principal --}}
    <div class="col-xl-8">
      <div class="card h-100 rounded-5">

        <div class="card-header border-bottom py-4">
          <div class="d-flex align-items-center justify-content-between">
            <div class="m-0 p-0">
              <h5 class="m-0 fw-bold">Información del movimiento</h5>
              <small class="text-muted">
                Datos generales y fechas de la operación.
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded-4 bg-label-{{ $tipoBadge[$mov->tipo] ?? 'primary' }}">
                <i class="bx {{ $tipoIcon[$mov->tipo] ?? 'bx-transfer' }} fs-4"></i>
              </span>
            </div>
          </div>
        </div>

        <div class="card-body py-4">

          <div class="row g-4">

            <div class="col-sm-6 col-lg-4">
              <div class="d-flex align-items-start gap-3">
                <span class="avatar avatar-sm">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="bx bx-hash"></i>
                  </span>
                </span>

                <div>
                  <small class="text-muted d-block mb-1">Código</small>
                  <span class="fw-semibold">
                    {{ $mov->codigo_movimiento }}
                  </span>
                </div>
              </div>
            </div>

            <div class="col-sm-6 col-lg-4">
              <div class="d-flex align-items-start gap-3">
                <span class="avatar avatar-sm">
                  <span class="avatar-initial rounded bg-label-info">
                    <i class="bx bx-calendar"></i>
                  </span>
                </span>

                <div>
                  <small class="text-muted d-block mb-1">Fecha del movimiento</small>
                  <span class="fw-semibold">
                    {{ $fmtFechaHora($mov->fecha_movimiento ?: $mov->fecha_registro) }}
                  </span>
                </div>
              </div>
            </div>

            <div class="col-sm-6 col-lg-4">
              <div class="d-flex align-items-start gap-3">
                <span class="avatar avatar-sm">
                  <span class="avatar-initial rounded bg-label-success">
                    <i class="bx bx-user-check"></i>
                  </span>
                </span>

                <div>
                  <small class="text-muted d-block mb-1">Registrado por</small>
                  <span class="fw-semibold">
                    {{ $registradoPor }}
                  </span>
                </div>
              </div>
            </div>

            @if ($esPrestamo)
              <div class="col-sm-6 col-lg-4">
                <div class="d-flex align-items-start gap-3">
                  <span class="avatar avatar-sm">
                    <span class="avatar-initial rounded bg-label-warning">
                      <i class="bx bx-calendar-event"></i>
                    </span>
                  </span>

                  <div>
                    <small class="text-muted d-block mb-1">
                      Devolución estimada
                    </small>

                    <span class="fw-semibold">
                      {{ $fmt($mov->fecha_devolucion_estimada) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="col-sm-6 col-lg-4">
                <div class="d-flex align-items-start gap-3">
                  <span class="avatar avatar-sm">
                    <span class="avatar-initial rounded bg-label-secondary">
                      <i class="bx bx-calendar-check"></i>
                    </span>
                  </span>

                  <div>
                    <small class="text-muted d-block mb-1">
                      Devolución real
                    </small>

                    <span class="fw-semibold">
                      {{ $fmt($mov->fecha_devolucion_real) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="col-sm-6 col-lg-4">
                <div class="d-flex align-items-start gap-3">
                  <span class="avatar avatar-sm">
                    <span
                      class="avatar-initial rounded bg-label-{{ $devBadge[$mov->estado_devolucion] ?? 'secondary' }}">
                      <i class="bx bx-undo"></i>
                    </span>
                  </span>

                  <div>
                    <small class="text-muted d-block mb-1">
                      Estado de devolución
                    </small>

                    <span class="badge fw-bold bg-label-{{ $devBadge[$mov->estado_devolucion] ?? 'secondary' }}">
                      {{ $devLabel[$mov->estado_devolucion] ?? $mov->estado_devolucion }}
                    </span>
                  </div>
                </div>
              </div>
            @endif

          </div>

          @if ($mov->motivo || $mov->observaciones)
            <hr class="my-4">

            <div class="row g-3">

              @if ($mov->motivo)
                <div class="col-md-6">
                  <div class="border rounded-4 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">
                      Motivo
                    </small>

                    <p class="mb-0">
                      {{ $mov->motivo }}
                    </p>
                  </div>
                </div>
              @endif

              @if ($mov->observaciones)
                <div class="col-md-6">
                  <div class="border rounded-4 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">
                      Observaciones
                    </small>

                    <p class="mb-0">
                      {{ $mov->observaciones }}
                    </p>
                  </div>
                </div>
              @endif

            </div>
          @endif

        </div>
      </div>
    </div>

    {{-- Resumen lateral --}}
    <div class="col-xl-4">
      <div class="card rounded-5 h-100">

        <div class="card-header border-bottom py-4">
          <h5 class="m-0 fw-bold">Resumen</h5>
          <small class="text-muted">
            Estado actual del movimiento.
          </small>
        </div>

        <div class="card-body py-4">

          <div class="d-flex align-items-center gap-3 mb-4">
            <div class="avatar avatar-lg">
              <span class="avatar-initial rounded-4 bg-label-{{ $tipoBadge[$mov->tipo] ?? 'primary' }}">
                <i class="bx {{ $tipoIcon[$mov->tipo] ?? 'bx-transfer' }} fs-3"></i>
              </span>
            </div>

            <div>
              <h5 class="mb-1">
                {{ $tipoLabel[$mov->tipo] ?? $mov->tipo }}
              </h5>

              <span class="badge bg-label-{{ $estadoBadge[$mov->estado] ?? 'secondary' }}">
                {{ $estadoLabel[$mov->estado] ?? $mov->estado }}
              </span>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center py-3 border-top">
            <span class="text-muted">
              Activos involucrados
            </span>

            <span class="badge bg-label-primary rounded-circle d-flex justify-content-center align-items-center"
              style="width: 28px; height: 28px;">
              {{ $mov->detalles->count() }}
            </span>
          </div>

          <div class="d-flex justify-content-between align-items-center py-3 border-top">
            <span class="text-muted">
              Documentos
            </span>

            <span class="badge bg-label-info rounded-circle d-flex justify-content-center align-items-center"
              style="width: 28px; height: 28px;">
              {{ $documentos->count() }}
            </span>
          </div>

          @if ($esPrestamo)
            <div class="d-flex justify-content-between align-items-center py-3 border-top">
              <span class="text-muted">
                Devolución
              </span>

              <span class="badge bg-label-{{ $devBadge[$mov->estado_devolucion] ?? 'secondary' }}">
                {{ $devLabel[$mov->estado_devolucion] ?? $mov->estado_devolucion }}
              </span>
            </div>
          @endif

          <div class="alert alert-secondary rounded-4 mb-0 mt-3">
            <div class="d-flex">
              <i class="bx bx-info-circle me-2 mt-1"></i>

              <small>
                Los responsables y ubicaciones de origen y destino se conservan individualmente por cada activo.
              </small>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>

  {{-- Documentos --}}
  <div class="card rounded-5 mb-4">

    <div class="card-header border-bottom py-4">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h5 class="m-0 fw-bold">Documentos de sustento</h5>
          <small class="text-muted">
            Actas, oficios y evidencias relacionadas con el movimiento.
          </small>
        </div>

        <span class="badge bg-label-primary rounded-pill">
          {{ $documentos->count() }}
        </span>
      </div>
    </div>

    <div class="card-body py-4">

      @forelse ($documentos as $doc)
        <div
          class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between border rounded p-3 mb-3 gap-3">

          <div class="d-flex align-items-center gap-3">

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-file"></i>
              </span>
            </div>

            <div>
              <h6 class="mb-1">
                {{ str_replace('_', ' ', $doc->tipo_documento) }}
              </h6>

              <div class="text-muted small">
                <span class="me-2">
                  <i class="bx bx-file-blank me-1"></i>
                  {{ $doc->nombre_original }}
                </span>

                <span class="me-2">
                  <i class="bx bx-data me-1"></i>
                  {{ $doc->tamano_kb }} KB
                </span>

                <span>
                  <i class="bx bx-user me-1"></i>
                  {{ $doc->subidoPor?->colaborador?->nombre_completo ?: $doc->subidoPor?->nombre_usuario ?: '—' }}
                </span>
              </div>
            </div>

          </div>

          <a href="{{ route('documentos.download', $doc->id_documento) }}" class="btn btn-sm btn-outline-primary">

            <i class="bx bx-download me-1"></i>
            Descargar
          </a>

        </div>
      @empty
        <div class="text-center py-5">
          <div class="avatar avatar-lg mb-3">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-file fs-3"></i>
            </span>
          </div>

          <h6 class="mb-1">Sin documentos</h6>
          <p class="text-muted mb-0">
            Este movimiento no tiene documentos de sustento registrados.
          </p>
        </div>
      @endforelse

    </div>
  </div>

  {{-- Activos --}}
  <div class="card rounded-5">

    <div class="card-header border-bottom py-4">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">

        <div>
          <h5 class="m-0 fw-bold">Activos involucrados</h5>
          <small class="text-muted">
            Trazabilidad individual de responsable, ubicación, condición y resultado.
          </small>
        </div>

        <span class="badge bg-label-primary rounded-pill">
          {{ $mov->detalles->count() }} activo{{ $mov->detalles->count() !== 1 ? 's' : '' }}
        </span>

      </div>
    </div>

    <div class="card-body py-4">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">

          <thead>
            <tr>
              <th>Activo</th>
              <th>Responsable</th>
              <th>Ubicación</th>
              <th>Condición</th>
              <th>Resultado</th>
              <th class="text-end">Acción</th>
            </tr>
          </thead>

          <tbody>

            @forelse ($mov->detalles as $detalle)
              @php
                $activo = $detalle->activo;

                $modelo = trim(($activo?->modelo?->marca?->nombre ?? '') . ' ' . ($activo?->modelo?->nombre ?? ''));
                $categoriaIcono = $activo?->categoria?->icono ?? 'bx-desktop';

                $responsableOrigen = $nombreColab($detalle->responsableOrigen);
                $responsableDestino = $nombreColab($detalle->responsableDestino);

                $ubicacionOrigen = $detalle->ubicacionOrigen?->nombre ?? '—';
                $ubicacionDestino = $detalle->ubicacionDestino?->nombre ?? '—';

                $mismoResponsable = $detalle->id_responsable_origen === $detalle->id_responsable_destino;
                $mismaUbicacion = $detalle->id_ubicacion_origen === $detalle->id_ubicacion_destino;
              @endphp

              <tr>

                {{-- Activo --}}
                <td style="min-width: 190px;">
                  <div class="d-flex align-items-center gap-3">

                    <div class="avatar avatar-sm">
                      <span class="avatar-initial rounded bg-label-primary">
                        <i class="bx {{ $categoriaIcono }}"></i>
                      </span>
                    </div>

                    <div>
                      <span class="fw-semibold d-block">
                        {{ $activo?->codigo_interno ?? 'Sin código' }}
                      </span>

                      <small class="text-muted d-block">
                        Patrimonial: {{ $activo?->codigo_patrimonial ?? '—' }}
                      </small>

                      <small class="text-muted">
                        {{ $modelo ?: 'Sin modelo registrado' }}
                      </small>
                    </div>

                  </div>
                </td>

                {{-- Responsable --}}
                <td style="min-width: 220px;">
                  @if ($mismoResponsable)
                    <div class="d-flex align-items-center gap-2">
                      <span class="avatar avatar-xs">
                        <span class="avatar-initial rounded-circle bg-label-secondary">
                          <i class="bx bx-user"></i>
                        </span>
                      </span>

                      <span class="fw-semibold">
                        {{ $responsableOrigen }}
                      </span>
                    </div>

                    <small class="text-muted">
                      Sin cambio de responsable
                    </small>
                  @else
                    <div class="small text-muted mb-1">
                      Origen
                    </div>

                    <div class="fw-semibold mb-2">
                      {{ $responsableOrigen }}
                    </div>

                    <div class="d-flex align-items-center gap-2">
                      <i class="bx bx-right-arrow-alt text-primary"></i>

                      <span class="fw-semibold text-primary">
                        {{ $responsableDestino }}
                      </span>
                    </div>
                  @endif
                </td>

                {{-- Ubicación --}}
                <td style="min-width: 200px;">
                  @if ($mismaUbicacion)
                    <div class="d-flex align-items-center gap-2">
                      <i class="bx bx-map text-muted"></i>

                      <span class="fw-semibold">
                        {{ $ubicacionOrigen }}
                      </span>
                    </div>

                    <small class="text-muted">
                      Sin cambio de ubicación
                    </small>
                  @else
                    <div class="small text-muted mb-1">
                      {{ $ubicacionOrigen }}
                    </div>

                    <div class="d-flex align-items-center gap-2">
                      <i class="bx bx-right-arrow-alt text-primary"></i>

                      <span class="fw-semibold text-primary">
                        {{ $ubicacionDestino }}
                      </span>
                    </div>
                  @endif
                </td>

                {{-- Condición --}}
                <td style="min-width: 170px;">
                  <div class="d-flex align-items-center flex-wrap gap-2">

                    <span class="badge bg-label-{{ $condBadge[$detalle->condicion_salida] ?? 'secondary' }}">
                      {{ Activo::CONDICION_LABELS[$detalle->condicion_salida] ?? ($detalle->condicion_salida ?? '—') }}
                    </span>

                    @if ($detalle->condicion_retorno)
                      <i class="bx bx-right-arrow-alt text-muted"></i>

                      <span class="badge bg-label-{{ $condBadge[$detalle->condicion_retorno] ?? 'secondary' }}">
                        {{ Activo::CONDICION_LABELS[$detalle->condicion_retorno] ?? $detalle->condicion_retorno }}
                      </span>
                    @endif

                  </div>

                  <small class="text-muted d-block mt-1">
                    {{ $detalle->condicion_retorno ? 'Salida → retorno' : 'Condición de salida' }}
                  </small>
                </td>

                {{-- Resultado --}}
                <td>
                  <span class="badge bg-label-{{ $resultadoBadge[$detalle->resultado] ?? 'secondary' }}">
                    {{ $resultadoLabel[$detalle->resultado] ?? ucfirst(strtolower(str_replace('_', ' ', $detalle->resultado))) }}
                  </span>
                </td>

                {{-- Acción --}}
                <td class="text-end">
                  @if ($activo)
                    <a href="{{ route('activos.ver', $activo->id_activo) }}"
                      class="btn btn-sm btn-icon btn-outline-primary" title="Ver activo">

                      <i class="bx bx-show"></i>
                    </a>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>

              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-5">

                  <div class="avatar avatar-lg mb-3">
                    <span class="avatar-initial rounded bg-label-secondary">
                      <i class="bx bx-devices fs-3"></i>
                    </span>
                  </div>

                  <h6 class="mb-1">Sin activos registrados</h6>

                  <p class="text-muted mb-0">
                    El movimiento no tiene activos asociados.
                  </p>

                </td>
              </tr>
            @endforelse

          </tbody>
        </table>
      </div>
    </div>

  </div>

  @include('content.movimientos.partials.modal-devolucion')

@endsection

@section('page-script')
  <script>
    window.routes = {
      devolver: '{{ route('movimientos.devolver', ['id' => '__ID__']) }}',
      datosDevolucion: '{{ route('movimientos.devolucion.datos', ['id' => '__ID__']) }}'
    };
  </script>

  @vite(['resources/js/vendors/index.js', 'resources/js/pages/movimientos/movimientos-devolucion.js'])
@endsection
