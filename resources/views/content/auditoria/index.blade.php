@extends('layouts/contentNavbarLayout')

@section('title', 'Auditoría de Cambios - OTI')

@php
  $accionBadge = [
      'CREAR' => 'success', 'ACTUALIZAR' => 'info', 'ELIMINAR' => 'danger',
      'EJECUTAR' => 'primary', 'CANCELAR' => 'warning', 'CERRAR' => 'secondary',
      'SINCRONIZAR' => 'dark', 'OTRO' => 'secondary',
  ];
  $entidadBadge = [
      'ACTIVO' => 'primary', 'MOVIMIENTO' => 'info', 'MANTENIMIENTO' => 'warning',
      'BAJA' => 'danger', 'TRAMITE_REFERENCIA' => 'secondary', 'OTRO' => 'secondary',
  ];
  $nombreUsuario = fn($u) => $u?->colaborador?->nombre_completo ?: ($u?->nombre_usuario ?? 'Sistema');
  $resumen = function ($arr) {
      if (!$arr) return null;
      return collect($arr)->map(fn($v, $k) => $k . ': ' . (is_array($v) ? implode('/', $v) : $v))->implode(' · ');
  };
@endphp

@section('content')

  <div class="mb-4">
    <h4 class="fw-bold mb-1">
      <span class="text-muted fw-light">Reportería y Auditoría /</span>
      Auditoría de cambios
    </h4>
    <p class="text-muted mb-0">Traza de las acciones sensibles sobre activos, movimientos, mantenimientos y bajas.</p>
  </div>

  {{-- Filtros --}}
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-lg-3 col-md-6">
          <label class="form-label">Entidad</label>
          <select class="form-select" name="entidad">
            <option value="">Todas</option>
            @foreach ($entidades as $e)
              <option value="{{ $e }}" @selected(request('entidad') === $e)>{{ $e }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-lg-3 col-md-6">
          <label class="form-label">Acción</label>
          <select class="form-select" name="accion">
            <option value="">Todas</option>
            @foreach (\App\Models\AuditoriaCambio::ACCIONES as $a)
              <option value="{{ $a }}" @selected(request('accion') === $a)>{{ ucfirst(strtolower($a)) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-lg-3 col-md-6">
          <label class="form-label">Desde</label>
          <input type="date" class="form-control" name="desde" value="{{ request('desde') }}">
        </div>
        <div class="col-lg-3 col-md-6 d-flex gap-2">
          <button class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Filtrar</button>
          <a href="{{ route('auditoria.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="mb-0">Registros ({{ $registros->total() }})</h5>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 small">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Entidad</th>
            <th>Acción</th>
            <th>Detalle</th>
            <th>Usuario</th>
            <th>IP</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($registros as $r)
            <tr>
              <td class="text-nowrap">{{ $r->creado_en?->format('d/m/Y H:i') }}</td>
              <td>
                <span class="badge bg-label-{{ $entidadBadge[$r->entidad_tipo] ?? 'secondary' }}">{{ $r->entidad_tipo }}</span>
                <small class="text-muted d-block">#{{ $r->entidad_id }}</small>
              </td>
              <td><span class="badge bg-label-{{ $accionBadge[$r->accion] ?? 'secondary' }}">{{ ucfirst(strtolower($r->accion)) }}</span></td>
              <td style="max-width: 380px">
                @if ($r->valores_nuevos)
                  <div>{{ \Illuminate\Support\Str::limit($resumen($r->valores_nuevos), 120) }}</div>
                @endif
                @if ($r->valores_anteriores)
                  <small class="text-muted d-block">Antes: {{ \Illuminate\Support\Str::limit($resumen($r->valores_anteriores), 90) }}</small>
                @endif
                @if ($r->motivo)
                  <small class="text-muted fst-italic d-block">“{{ \Illuminate\Support\Str::limit($r->motivo, 90) }}”</small>
                @endif
                @if (!$r->valores_nuevos && !$r->valores_anteriores && !$r->motivo)
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>{{ $nombreUsuario($r->usuario) }}</td>
              <td><small class="text-muted">{{ $r->ip ?? '—' }}</small></td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Sin registros de auditoría.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($registros->hasPages())
      <div class="card-footer">{{ $registros->links('pagination::bootstrap-5') }}</div>
    @endif
  </div>

@endsection
