@extends('layouts/contentNavbarLayout')

@section('title', 'Detalle de importación - OTI')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold text-primary d-flex align-items-center">
      <i class="bx bx-file me-2"></i> Importación #{{ $importacion->id_importacion }}
    </h4>
    <a href="{{ route('importaciones.index') }}" class="btn btn-label-secondary btn-sm">
      <i class="bx bx-arrow-back me-1"></i> Volver
    </a>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
      <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ══ Resumen ════════════════════════════════════════════════════════════ --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="card h-100"><div class="card-body text-center py-4">
        <p class="text-muted small mb-1">Total filas</p>
        <h3 class="mb-0 fw-bold">{{ $importacion->total_registros }}</h3>
      </div></div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card h-100 border-success"><div class="card-body text-center py-4">
        <p class="text-muted small mb-1">Correctos</p>
        <h3 class="mb-0 fw-bold text-success">{{ $importacion->registros_correctos }}</h3>
      </div></div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card h-100 border-warning"><div class="card-body text-center py-4">
        <p class="text-muted small mb-1">Observados / Errores</p>
        <h3 class="mb-0 fw-bold text-warning">{{ $importacion->registros_observados }}</h3>
      </div></div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card h-100"><div class="card-body text-center py-4">
        <p class="text-muted small mb-1">Archivo</p>
        <p class="mb-0 fw-semibold text-truncate" title="{{ $importacion->nombre_archivo }}">{{ $importacion->nombre_archivo }}</p>
        <small class="text-muted">{{ $importacion->creado_en?->format('Y-m-d H:i') }}</small>
      </div></div>
    </div>
  </div>

  {{-- ══ Detalle por fila ═══════════════════════════════════════════════════ --}}
  <div class="card">
    <div class="card-header border-bottom"><h5 class="mb-0 fw-bold">Detalle por fila</h5></div>
    <div class="card-body pt-4">
      <table class="table table-hover small">
        <thead>
          <tr>
            <th class="fw-bold text-center">Fila</th>
            <th class="fw-bold">Código Patrimonial</th>
            <th class="fw-bold">N° Serie</th>
            <th class="fw-bold">Denominación</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Mensaje</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($detalles as $d)
            @php
              $b = match ($d['estado']) {
                  'CORRECTO' => 'bg-label-success',
                  'OBSERVADO' => 'bg-label-warning',
                  'DUPLICADO' => 'bg-label-info',
                  'ERROR' => 'bg-label-danger',
                  default => 'bg-label-secondary',
              };
            @endphp
            <tr>
              <td class="text-center">{{ $d['fila'] }}</td>
              <td class="fw-semibold">{{ $d['codigo_patrimonial'] ?? '—' }}</td>
              <td>{{ $d['numero_serie'] ?? '—' }}</td>
              <td>{{ $d['denominacion'] ?? '—' }}</td>
              <td><span class="badge {{ $b }}">{{ $d['estado'] }}</span></td>
              <td class="text-muted">{{ $d['mensaje'] ?? '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
