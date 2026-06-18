@extends('layouts/contentNavbarLayout')

@section('title', 'Categorías de Activos - OTI')

@section('content')
  <h4 class="mb-4 fw-bold text-primary d-flex align-items-center">
    <i class="bx bx-category me-2"></i> Categorías de Activos
  </h4>

  <div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold">Lista de Categorías</h5>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria">
        <i class="bx bx-plus me-1"></i> Nueva Categoría
      </button>
    </div>

    <div class="card-body pt-6">
      <table class="table table-hover" id="miTablaCategorias">
        <thead>
          <tr>
            <th class="fw-bold">#</th>
            <th class="fw-bold">Nombre</th>
            <th class="fw-bold">Descripción</th>
            <th class="fw-bold">Modelos</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0"></tbody>
      </table>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — NUEVA CATEGORÍA
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalNuevaCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-plus-circle me-2"></i>Nueva Categoría
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formNuevaCategoria" action="{{ route('categorias.store') }}" method="POST">
          @csrf
          <div class="modal-body py-5">

            <div class="form-floating form-floating-outline mb-4">
              <input type="text" class="form-control text-uppercase" id="nombre_categoria" name="nombre"
                placeholder="Ej: LAPTOP">
              <label>Nombre de la Categoría <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="descripcion_categoria" name="descripcion"
                placeholder="Descripción breve">
              <label>Descripción</label>
              <div class="invalid-feedback"></div>
            </div>

          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarCategoria">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — EDITAR CATEGORÍA
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-edit-alt me-2"></i>Editar Categoría
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formEditarCategoria" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body py-5">

            <div class="form-floating form-floating-outline mb-4">
              <input type="text" class="form-control text-uppercase" id="edit-nombre-categoria" name="nombre"
                placeholder="Ej: LAPTOP">
              <label>Nombre de la Categoría <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="edit-descripcion-categoria" name="descripcion"
                placeholder="Descripción breve">
              <label>Descripción</label>
              <div class="invalid-feedback"></div>
            </div>

          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnActualizarCategoria">
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
    window.categorias = @json($categorias);
    window.routes = {
      store:        '{{ route('categorias.store') }}',
      update:       '/categorias/{id}',
      toggleEstado: '/categorias/{id}/toggle-estado',
    };
  </script>
  @vite([
    'resources/js/vendors/index.js',
    'resources/js/pages/catalogos/categorias/categorias-table.js',
    'resources/js/pages/catalogos/categorias/categorias-create.js',
    'resources/js/pages/catalogos/categorias/categorias-edit.js',
    'resources/js/pages/catalogos/categorias/categorias-actions.js',
  ])
@endsection
