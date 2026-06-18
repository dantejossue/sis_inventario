@extends('layouts/contentNavbarLayout')

@section('title', 'Dependencias - OTI')

@section('content')
  <h4 class="mb-4 fw-bold text-primary d-flex align-items-center">
    <i class="bx bx-sitemap me-2"></i> Gestión de Dependencias
  </h4>

  <div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold">Lista de Dependencias</h5>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaDependencia">
        <i class="bx bx-plus me-1"></i> Nueva Dependencia
      </button>
    </div>

    <div class="card-body pt-6">
      <table class="table table-hover" id="miTablaDependencias">
        <thead>
          <tr>
            <th class="fw-bold">#</th>
            <th class="fw-bold">Nombre</th>
            <th class="fw-bold">Descripción</th>
            <th class="fw-bold">Sedes asignadas</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0"></tbody>
      </table>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — NUEVA DEPENDENCIA
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalNuevaDependencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-plus-circle me-2"></i>Nueva Dependencia
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formNuevaDependencia" action="{{ route('dependencias.store') }}" method="POST">
          @csrf
          <div class="modal-body py-5">

            <div class="form-floating form-floating-outline mb-4">
              <input type="text" class="form-control text-uppercase" id="nombre_dependencia" name="nombre_dependencia"
                placeholder="Ej: OFICINA DE TECNOLOGÍA DE LA INFORMACIÓN">
              <label>Nombre de la Dependencia <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="descripcion_dep" name="descripcion"
                placeholder="Descripción breve (opcional)">
              <label>Descripción</label>
              <div class="invalid-feedback"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarDep">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — EDITAR DEPENDENCIA
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalEditarDependencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-edit-alt me-2"></i>Editar Dependencia
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formEditarDependencia" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body py-5">

            <div class="form-floating form-floating-outline mb-4">
              <input type="text" class="form-control text-uppercase" id="edit-nombre-dep" name="nombre_dependencia"
                placeholder="Ej: OFICINA DE TECNOLOGÍA">
              <label>Nombre de la Dependencia <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="edit-descripcion-dep" name="descripcion"
                placeholder="Descripción breve (opcional)">
              <label>Descripción</label>
              <div class="invalid-feedback"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnActualizarDep">
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
    window.dependencias = @json($dependencias);
    window.routes = {
      store:        '{{ route('dependencias.store') }}',
      update:       '/dependencias/{id}',
      toggleEstado: '/dependencias/{id}/toggle-estado',
    };
  </script>
  @vite([
    'resources/js/vendors/index.js',
    'resources/js/pages/dependencias/dependencias-table.js',
    'resources/js/pages/dependencias/dependencias-create.js',
    'resources/js/pages/dependencias/dependencias-edit.js',
    'resources/js/pages/dependencias/dependencias-actions.js',
  ])
@endsection
