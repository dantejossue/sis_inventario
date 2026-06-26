@extends('layouts/contentNavbarLayout')

@section('title', 'Importación SIGA - OTI')

@section('content')
  <h4 class="mb-4 fw-bold text-primary d-flex align-items-center">
    <i class="bx bx-import me-2"></i> Importación del Padrón SIGA
  </h4>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
      <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
      <i class="bx bx-error-circle me-2"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bx bx-error-circle me-2"></i> {{ $errors->first() }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ══ Subir archivo ══════════════════════════════════════════════════════ --}}
  <div class="card mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold">Nueva importación</h5>
      <a href="{{ route('importaciones.plantilla') }}" class="btn btn-label-secondary btn-sm">
        <i class="bx bx-download me-1"></i> Descargar plantilla
      </a>
    </div>
    <div class="card-body pt-4">
      <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="bx bx-info-circle me-2"></i>
        <span>Sube el Excel exportado de SIGA con los bienes informáticos. La columna
          <strong>CODIGO_PATRIMONIAL</strong> es obligatoria y los duplicados se omiten. Los activos se crean
          <strong>EN ALMACÉN</strong> y <strong>PENDIENTE_VALIDACION</strong> para que la OTI complete la ficha técnica.</span>
      </div>
      <form action="{{ route('importaciones.store') }}" method="POST" enctype="multipart/form-data"
        class="row g-3 align-items-center">
        @csrf
        <div class="col-md-8">
          <input type="file" name="archivo" class="form-control" accept=".xlsx,.xls" required>
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-upload me-1"></i> Importar
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ══ Historial ══════════════════════════════════════════════════════════ --}}
  <div class="card">
    <div class="card-header border-bottom"><h5 class="mb-0 fw-bold">Historial de importaciones</h5></div>
    <div class="card-body pt-4">
      <table class="table table-hover small">
        <thead>
          <tr>
            <th class="fw-bold">Archivo</th>
            <th class="fw-bold">Fecha</th>
            <th class="fw-bold text-center">Total</th>
            <th class="fw-bold text-center">Correctos</th>
            <th class="fw-bold text-center">Observados</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Por</th>
            <th class="fw-bold text-center">—</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($importaciones as $i)
            <tr>
              <td class="fw-semibold">{{ $i['nombre_archivo'] }}</td>
              <td>{{ $i['fecha'] }}</td>
              <td class="text-center">{{ $i['total'] }}</td>
              <td class="text-center"><span class="badge bg-label-success">{{ $i['correctos'] }}</span></td>
              <td class="text-center"><span class="badge bg-label-warning">{{ $i['observados'] }}</span></td>
              <td>
                @php
                  $badge = match ($i['estado']) {
                      'COMPLETADO' => 'bg-success',
                      'COMPLETADO_CON_OBSERVACIONES' => 'bg-warning',
                      'ERROR' => 'bg-danger',
                      default => 'bg-secondary',
                  };
                @endphp
                <span class="badge {{ $badge }}">{{ str_replace('_', ' ', $i['estado']) }}</span>
              </td>
              <td>{{ $i['importado_por'] ?? '—' }}</td>
              <td class="text-center">
                <a href="{{ route('importaciones.show', $i['id_importacion']) }}" class="btn btn-sm btn-label-primary">
                  <i class="bx bx-show"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-4">Aún no hay importaciones registradas.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection
