@extends('layouts/contentNavbarLayout')

@section('title', 'Roles y Permisos - OTI')

@section('content')
  <h4 class="mb-4 fw-bold text-primary d-flex align-items-center">
    <i class="bx bx-shield-quarter me-2"></i> Gestión de Roles y Permisos
  </h4>

  <div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold">Lista de Roles</h5>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoRol">
        <i class="bx bx-plus me-1"></i> Nuevo Rol
      </button>
    </div>

    <div class="card-body pt-6">
      <table class="table table-hover" id="miTablaRoles">
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

  {{-- ═══════════════════════════════════════════
       MODAL — NUEVO ROL
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalNuevoRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-plus-circle me-2"></i>Nuevo Rol
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formNuevoRol" action="{{ route('roles.store') }}" method="POST">
          @csrf
          <div class="modal-body py-5">
            <div id="errores-crear"></div>

            <div class="form-floating form-floating-outline mb-4">
              <input type="text" class="form-control text-uppercase" id="nombre_rol" name="nombre"
                placeholder="Ej: ALMACEN">
              <label>Nombre del Rol <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="descripcion_rol" name="descripcion"
                placeholder="Descripción breve">
              <label>Descripción</label>
              <div class="invalid-feedback"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarRol">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — EDITAR ROL
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalEditarRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-edit-alt me-2"></i>Editar Rol
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formEditarRol" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body py-5">
            <div id="errores-editar"></div>

            <div class="form-floating form-floating-outline mb-4">
              <input type="text" class="form-control text-uppercase" id="edit-nombre" name="nombre"
                placeholder="Ej: ALMACEN">
              <label>Nombre del Rol <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="edit-descripcion" name="descripcion"
                placeholder="Descripción breve">
              <label>Descripción</label>
              <div class="invalid-feedback"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnActualizarRol">
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
    window.roles = @json($roles);
    window.routes = {
      store:       '{{ route('roles.store') }}',
      update:      '/roles/{id}',
      toggleEstado:'/roles/{id}/toggle-estado',
    };
  </script>
  @vite([
    'resources/js/vendors/index.js',
    'resources/js/pages/roles/roles-table.js',
    'resources/js/pages/roles/roles-create.js',
    'resources/js/pages/roles/roles-edit.js',
    'resources/js/pages/roles/roles-actions.js',
  ])
@endsection
