@extends('layouts/contentNavbarLayout')

@section('title', 'Movimientos - OTI')

@section('content')
  <h4 class="fw-bold mb-0">
    <span class="text-secondary d-block d-md-inline">
      Gestión Principal
    </span>
    <span class="d-none d-md-inline"> / </span>
    <span class="d-block d-md-inline">
      Movimientos de activos
    </span>
  </h4>
  <p class="text-muted fw-light mb-5">Movimientos internos OTI: préstamos, transferencias y regularizaciones.
    Los préstamos se cierran registrando su devolución.</p>

  @php
    $movs = collect($movimientos);
    $kTotal = $movs->count();
    $kPrestamos = $movs->where('tipo', 'PRESTAMO')->count();
    $kPendientes = $movs->where('es_prestamo_pendiente', true)->count();
    $kTransfer = $movs->where('tipo', 'TRANSFERENCIA')->count();
    $kRegular = $movs->where('tipo', 'REGULARIZACION')->count();
  @endphp

  <!-- KPIs -->
  <div class="row g-4">

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card rounded-5">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Total movimientos</span>
              <h3 class="mb-2">{{ $kTotal }}</h3>
              <small class="text-muted fw-semibold">Registrados en el sistema</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-transfer-alt"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card rounded-5 movement-kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Préstamos</span>
              <h3 class="mb-2">{{ $kPrestamos }}</h3>
              <small class="text-warning fw-semibold">{{ $kPendientes }} pendiente(s) de devolución</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="bx bx-time-five"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card rounded-5 movement-kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Transferencias</span>
              <h3 class="mb-2">{{ $kTransfer }}</h3>
              <small class="text-info fw-semibold">Cambios de responsable/ubicación</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info">
                <i class="bx bx-git-compare"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card rounded-5 movement-kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Regularizaciones</span>
              <h3 class="mb-2">{{ $kRegular }}</h3>
              <small class="text-secondary fw-semibold">Correcciones con sustento</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-edit"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>


  <!-- Filtros -->
  <div class="card mb-4 rounded-4">
    <div class="card-header">
      <h5 class="mb-0">Filtros de búsqueda</h5>
      <small class="text-muted">
        Filtra por tipo, estado, devolución, responsable o fechas.
      </small>
    </div>

    <div class="card-body">
      <div class="row g-3">

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Código</label>
          <input type="text" class="form-control" placeholder="MOV-000001..." />
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Tipo de movimiento</label>
          <select class="form-select">
            <option selected>Todos</option>
            <option>Préstamo</option>
            <option>Transferencia</option>
            <option>Regularización</option>
          </select>
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Estado</label>
          <select class="form-select">
            <option selected>Todos</option>
            <option>Borrador</option>
            <option>Ejecutado</option>
            <option>Observado</option>
            <option>Cancelado</option>
          </select>
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Devolución</label>
          <select class="form-select">
            <option selected>Todas</option>
            <option>Pendiente</option>
            <option>Devuelto</option>
            <option>Devuelto (observado)</option>
            <option>Vencido</option>
          </select>
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Responsable / área</label>
          <input type="text" class="form-control" placeholder="Responsable, área o dependencia" />
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Fecha desde</label>
          <input type="date" class="form-control" />
        </div>

        <div class="col-lg-3 col-md-6 d-flex align-items-end">
          <div class="d-flex gap-2 w-100">
            <button class="btn btn-primary w-100">
              <i class="bx bx-search me-1"></i>
              Buscar
            </button>

            <button class="btn btn-outline-secondary">
              <i class="bx bx-reset"></i>
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div class="card rounded-4">
    <div class="card-header border-bottom align-items-center">
      <h5 class="mb-0 fw-bold">Listado de Movimientos</h5>
      <small class="text-muted">
        Control de movimientos formales, internos y trazabilidad operativa.
      </small>
      {{-- <a href="{{ route('activos.index') }}" class="btn btn-label-primary">
        <i class="bx bx-list-ul me-1"></i> Ir a Activos para registrar
      </a> --}}
    </div>

    <div class="card-body pt-6">
      <table class="table table-hover small" id="miTablaMovimientos">
        <thead>
          <tr>
            <th class="fw-bold">Código</th>
            <th class="fw-bold">Tipo</th>
            <th class="fw-bold">Origen</th>
            <th class="fw-bold">Destino</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Devolución</th>
            <th class="fw-bold">Realizado por</th>
            <th class="fw-bold">Fecha</th>
            <th class="fw-bold">Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0"></tbody>
      </table>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    window.movimientos = @json($movimientos);
    window.routes = {
      devolver: '{{ route('movimientos.devolver', ['id' => '__ID__']) }}'
    };
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/movimientos/movimientos-table.js'])
@endsection
