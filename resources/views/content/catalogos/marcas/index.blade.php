@extends('layouts/contentNavbarLayout')

@section('title', 'Marcas y Modelos - OTI')

@section('content')
  <h4 class="mb-4 fw-bold text-primary d-flex align-items-center">
    <i class="bx bx-cube me-2"></i> Marcas y Modelos
  </h4>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-4" id="marcasTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active fw-semibold" id="tab-marcas" data-bs-toggle="tab" data-bs-target="#pane-marcas"
        type="button" role="tab">
        Marcas
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link fw-semibold" id="tab-modelos" data-bs-toggle="tab" data-bs-target="#pane-modelos"
        type="button" role="tab">
        Modelos
      </button>
    </li>
  </ul>

  <div class="tab-content" id="marcasTabContent">

    {{-- ══ Tab Marcas ══════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="pane-marcas" role="tabpanel">
      <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold">Lista de Marcas</h5>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaMarca">
            <i class="bx bx-plus me-1"></i> Nueva Marca
          </button>
        </div>
        <div class="card-body pt-6">
          <table class="table table-hover" id="miTablaMarcas">
            <thead>
              <tr>
                <th class="fw-bold">#</th>
                <th class="fw-bold">Nombre</th>
                <th class="fw-bold">Modelos</th>
                <th class="fw-bold">Estado</th>
                <th class="fw-bold">Acciones</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0"></tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ══ Tab Modelos ══════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="pane-modelos" role="tabpanel">
      <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold">Lista de Modelos</h5>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoModelo">
            <i class="bx bx-plus me-1"></i> Nuevo Modelo
          </button>
        </div>
        <div class="card-body pt-6">
          <table class="table table-hover" id="miTablaModelos">
            <thead>
              <tr>
                <th class="fw-bold">#</th>
                <th class="fw-bold">Marca</th>
                <th class="fw-bold">Categoría</th>
                <th class="fw-bold">Nombre del Modelo</th>
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

  </div>{{-- end tab-content --}}

  {{-- ═══════════════════════════════════════════
       MODAL — NUEVA MARCA
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalNuevaMarca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-plus-circle me-2"></i>Nueva Marca
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formNuevaMarca" action="{{ route('marcas.store') }}" method="POST">
          @csrf
          <div class="modal-body py-5">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control text-uppercase" id="nombre_marca" name="nombre"
                placeholder="Ej: HP">
              <label>Nombre de la Marca <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarMarca">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — EDITAR MARCA
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalEditarMarca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-edit-alt me-2"></i>Editar Marca
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formEditarMarca" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body py-5">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control text-uppercase" id="edit-nombre-marca" name="nombre"
                placeholder="Ej: HP">
              <label>Nombre de la Marca <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnActualizarMarca">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Actualizar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — NUEVO MODELO
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalNuevoModelo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-plus-circle me-2"></i>Nuevo Modelo
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formNuevoModelo" action="{{ route('modelos.store') }}" method="POST">
          @csrf
          <div class="modal-body py-5">

            <div class="form-floating form-floating-outline mb-4">
              <select class="form-select" id="modelo_id_marca" name="id_marca">
                <option value="">Seleccionar marca...</option>
                @foreach ($marcas as $marca)
                  <option value="{{ $marca->id_marca }}">{{ $marca->nombre }}</option>
                @endforeach
              </select>
              <label>Marca <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline mb-4">
              <select class="form-select" id="modelo_id_categoria" name="id_categoria">
                <option value="">Sin categoría</option>
                @foreach ($categorias as $cat)
                  <option value="{{ $cat->id_categoria }}">{{ $cat->nombre }}</option>
                @endforeach
              </select>
              <label>Categoría</label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline mb-4">
              <input type="text" class="form-control text-uppercase" id="modelo_nombre" name="nombre"
                placeholder="Ej: PAVILION 15">
              <label>Nombre del Modelo <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="modelo_descripcion" name="descripcion"
                placeholder="Descripción breve">
              <label>Descripción</label>
              <div class="invalid-feedback"></div>
            </div>

          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarModelo">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — EDITAR MODELO
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalEditarModelo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-edit-alt me-2"></i>Editar Modelo
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formEditarModelo" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body py-5">

            <div class="form-floating form-floating-outline mb-4">
              <select class="form-select" id="edit-modelo-id-marca" name="id_marca">
                <option value="">Seleccionar marca...</option>
                @foreach ($marcas as $marca)
                  <option value="{{ $marca->id_marca }}">{{ $marca->nombre }}</option>
                @endforeach
              </select>
              <label>Marca <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline mb-4">
              <select class="form-select" id="edit-modelo-id-categoria" name="id_categoria">
                <option value="">Sin categoría</option>
                @foreach ($categorias as $cat)
                  <option value="{{ $cat->id_categoria }}">{{ $cat->nombre }}</option>
                @endforeach
              </select>
              <label>Categoría</label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline mb-4">
              <input type="text" class="form-control text-uppercase" id="edit-modelo-nombre" name="nombre"
                placeholder="Ej: PAVILION 15">
              <label>Nombre del Modelo <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="edit-modelo-descripcion" name="descripcion"
                placeholder="Descripción breve">
              <label>Descripción</label>
              <div class="invalid-feedback"></div>
            </div>

          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnActualizarModelo">
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
    window.marcas = @json($marcas);
    window.modelos = @json($modelos);
    window.categorias = @json($categorias);
    window.routes = {
      store: '{{ route('marcas.store') }}',
      update: '/marcas/{id}',
      toggleEstado: '/marcas/{id}/toggle-estado',
      storeModelo: '{{ route('modelos.store') }}',
      updateModelo: '/modelos/{id}',
      toggleEstadoModelo: '/modelos/{id}/toggle-estado',
    };
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/catalogos/marcas/marcas_modelos.js', 'resources/js/pages/catalogos/marcas/marcas-table.js', 'resources/js/pages/catalogos/marcas/marcas-create.js', 'resources/js/pages/catalogos/marcas/marcas-edit.js', 'resources/js/pages/catalogos/marcas/marcas-actions.js', 'resources/js/pages/catalogos/marcas/modelos-create.js', 'resources/js/pages/catalogos/marcas/modelos-edit.js'])
@endsection
