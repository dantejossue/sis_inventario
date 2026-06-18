@extends('layouts/contentNavbarLayout')

@section('title', 'Estados de Activos - OTI')

@section('content')
  <h4 class="mb-4 fw-bold text-primary d-flex align-items-center">
    <i class="bx bx-check-circle me-2"></i> Estados de Activos
  </h4>

  <div class="row g-4">

    {{-- ══ Card CONDICIONES ════════════════════════════════════════════════════ --}}
    <div class="col-lg-6">
      <div class="card border-primary h-100">
        <div class="card-header border-bottom border-primary d-flex justify-content-between align-items-center bg-label-primary">
          <h5 class="mb-0 fw-bold text-primary">
            <i class="bx bx-health me-2"></i>Condiciones
          </h5>
          <button type="button" class="btn btn-primary btn-sm btn-nueva-condicion"
            data-tipo="CONDICION"
            data-bs-toggle="modal" data-bs-target="#modalNuevoEstado">
            <i class="bx bx-plus me-1"></i> Nueva Condición
          </button>
        </div>
        <div class="card-body pt-4">
          <table class="table table-hover" id="miTablaCondiciones">
            <thead>
              <tr>
                <th class="fw-bold">#</th>
                <th class="fw-bold">Nombre</th>
                <th class="fw-bold">Descripción</th>
                <th class="fw-bold">Estado</th>
                <th class="fw-bold">Acciones</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0"></tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ══ Card SITUACIONES ════════════════════════════════════════════════════ --}}
    <div class="col-lg-6">
      <div class="card border-success h-100">
        <div class="card-header border-bottom border-success d-flex justify-content-between align-items-center bg-label-success">
          <h5 class="mb-0 fw-bold text-success">
            <i class="bx bx-map-alt me-2"></i>Situaciones
          </h5>
          <button type="button" class="btn btn-success btn-sm btn-nueva-situacion"
            data-tipo="SITUACION"
            data-bs-toggle="modal" data-bs-target="#modalNuevoEstado">
            <i class="bx bx-plus me-1"></i> Nueva Situación
          </button>
        </div>
        <div class="card-body pt-4">
          <table class="table table-hover" id="miTablaSituaciones">
            <thead>
              <tr>
                <th class="fw-bold">#</th>
                <th class="fw-bold">Nombre</th>
                <th class="fw-bold">Descripción</th>
                <th class="fw-bold">Estado</th>
                <th class="fw-bold">Acciones</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0"></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — NUEVO ESTADO
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalNuevoEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center" id="modalNuevoEstadoLabel">
            <i class="bx bx-plus-circle me-2"></i>Nuevo Estado
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formNuevoEstado" action="{{ route('estados.store') }}" method="POST">
          @csrf
          <input type="hidden" name="tipo_estado" id="nuevo_tipo_estado" value="">

          <div class="modal-body py-5">

            <div class="form-floating form-floating-outline mb-4">
              <input type="text" class="form-control text-uppercase" id="nombre_estado" name="nombre"
                placeholder="Ej: BUENO">
              <label>Nombre <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="descripcion_estado" name="descripcion"
                placeholder="Descripción breve">
              <label>Descripción</label>
              <div class="invalid-feedback"></div>
            </div>

          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarEstado">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — EDITAR ESTADO
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalEditarEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-edit-alt me-2"></i>Editar Estado
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formEditarEstado" method="POST">
          @csrf
          @method('PUT')
          <input type="hidden" name="tipo_estado" id="edit-tipo-estado" value="">

          <div class="modal-body py-5">

            <div class="form-floating form-floating-outline mb-4">
              <input type="text" class="form-control text-uppercase" id="edit-nombre-estado" name="nombre"
                placeholder="Ej: BUENO">
              <label>Nombre <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="edit-descripcion-estado" name="descripcion"
                placeholder="Descripción breve">
              <label>Descripción</label>
              <div class="invalid-feedback"></div>
            </div>

          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnActualizarEstado">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Actualizar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    window.condiciones = @json($condiciones);
    window.situaciones = @json($situaciones);
    window.routes = {
      store:        '{{ route('estados.store') }}',
      update:       '/estados/{id}',
      toggleEstado: '/estados/{id}/toggle-estado',
    };
  </script>
  @vite([
    'resources/js/vendors/index.js',
    'resources/js/pages/catalogos/estados/estados-table.js',
    'resources/js/pages/catalogos/estados/estados-create.js',
    'resources/js/pages/catalogos/estados/estados-edit.js',
    'resources/js/pages/catalogos/estados/estados-actions.js',
  ])
@endsection
