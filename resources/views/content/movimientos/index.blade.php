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
  <p class="text-muted fw-light mb-5">Seguimiento de asignaciones, transferencias, órdenes de salida, movimientos internos
    y regularizaciones.</p>

  <!-- KPIs -->
  <div class="row g-4">

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card rounded-5">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Total movimientos</span>
              <h3 class="mb-2">486</h3>
              <small class="text-success fw-semibold">
                <i class="bx bx-up-arrow-alt"></i>
                28 este mes
              </small>
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
              <span class="fw-semibold d-block mb-1">Formales</span>
              <h3 class="mb-2">314</h3>
              <small class="text-warning fw-semibold">
                Requieren trámite / SIGA
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="bx bx-file"></i>
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
              <span class="fw-semibold d-block mb-1">Internos</span>
              <h3 class="mb-2">172</h3>
              <small class="text-info fw-semibold">
                Con evidencia interna
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info">
                <i class="bx bx-support"></i>
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
              <span class="fw-semibold d-block mb-1">Pendientes</span>
              <h3 class="mb-2">14</h3>
              <small class="text-danger fw-semibold">
                Aprobación o ejecución
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="bx bx-time-five"></i>
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
        Filtra por tipo, estado, flujo, SIGA, ticket, responsable o fechas.
      </small>
    </div>

    <div class="card-body">
      <div class="row g-3">

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Código / expediente</label>
          <input type="text" class="form-control" placeholder="MOV-0001, EXP-2026..." />
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Tipo de movimiento</label>
          <select class="form-select">
            <option selected>Todos</option>
            <option>Asignación</option>
            <option>Transferencia</option>
            <option>Orden de salida</option>
            <option>Reingreso</option>
            <option>Desplazamiento interno</option>
            <option>Préstamo temporal</option>
            <option>Devolución interna</option>
            <option>Regularización</option>
          </select>
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Flujo</label>
          <select class="form-select">
            <option selected>Todos</option>
            <option>Formal</option>
            <option>Interno</option>
            <option>Regularización</option>
          </select>
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Estado</label>
          <select class="form-select">
            <option selected>Todos</option>
            <option>Registrado</option>
            <option>Pendiente trámite</option>
            <option>En trámite</option>
            <option>Autorizado</option>
            <option>Ejecutado</option>
            <option>Observado</option>
            <option>Cancelado</option>
          </select>
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Estado SIGA</label>
          <select class="form-select">
            <option selected>Todos</option>
            <option>No aplica</option>
            <option>Pendiente actualización</option>
            <option>Registrado</option>
            <option>Observado</option>
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
            <th class="fw-bold">SIGA</th>
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
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/movimientos/movimientos-table.js'])
@endsection
