@extends('layouts/contentNavbarLayout')

@section('title', 'Sedes - OTI')

@section('content')
  <h4 class="mb-4 fw-bold text-secondary d-flex align-items-center">
    <i class="bx bx-buildings me-2"></i> Gestión de Sedes
  </h4>

  <div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold">Lista de Sedes</h5>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaSede">
        <i class="bx bx-plus me-1"></i> Nueva Sede
      </button>
    </div>

    <div class="card-body pt-6">
      <table class="table table-hover" id="miTablaSedes">
        <thead>
          <tr>
            <th class="fw-bold">#</th>
            <th class="fw-bold">Nombre del Campus</th>
            <th class="fw-bold">Ubicación</th>
            <th class="fw-bold">Dependencias</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0"></tbody>
      </table>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — NUEVA SEDE
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalNuevaSede" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-building-house me-2"></i>Nueva Sede
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formNuevaSede" action="{{ route('sedes.store') }}" method="POST">
          @csrf
          <div class="modal-body py-5">

            <div class="form-floating form-floating-outline mb-4">
              <input type="text" class="form-control text-uppercase" id="nombre_sede" name="nombre_sede"
                placeholder="Ej: CAMPUS HUALCARÁ">
              <label>Nombre del Campus <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="ubicacion" name="ubicacion"
                placeholder="Dirección o referencia">
              <label>Ubicación / Dirección</label>
              <div class="invalid-feedback"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarSede">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — EDITAR SEDE
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalEditarSede" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-edit-alt me-2"></i>Editar Sede
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formEditarSede" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body py-5">

            <div class="form-floating form-floating-outline mb-4">
              <input type="text" class="form-control text-uppercase" id="edit-nombre-sede" name="nombre_sede"
                placeholder="Ej: CAMPUS HUALCARÁ">
              <label>Nombre del Campus <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="edit-ubicacion" name="ubicacion"
                placeholder="Dirección o referencia">
              <label>Ubicación / Dirección</label>
              <div class="invalid-feedback"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnActualizarSede">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Actualizar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — GESTIONAR DEPENDENCIAS DE LA SEDE
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalDependencias" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-sitemap me-2"></i>Dependencias en <span id="dep-sede-nombre"
              class="ms-1 text-primary"></span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body py-5" id="dep-loading">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
          </div>
        </div>

        {{-- <div class="modal-body py-5 d-none" id="dep-body">
          @if ($dependencias->isEmpty())
            <div class="alert alert-warning mb-0">
              No hay dependencias registradas. Crea una desde el módulo de Dependencias.
            </div>
          @else
            <div class="row g-3">
              @foreach ($dependencias as $dep)
                <div class="col-md-6">
                  <div class="border rounded p-3 d-flex align-items-center" style="gap:12px;">
                    <input class="form-check-input dep-check" type="checkbox" id="dep_{{ $dep->id_dependencia }}"
                      value="{{ $dep->id_dependencia }}">
                    <label class="form-check-label w-100" for="dep_{{ $dep->id_dependencia }}">
                      <span class="fw-semibold d-block">{{ $dep->nombre_dependencia }}</span>
                      @if ($dep->descripcion)
                        <small class="text-secondary">{{ $dep->descripcion }}</small>
                      @else
                        <small class="text-secondary">Sin descripción</small>
                      @endif

                    </label>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div> --}}

        <div class="modal-body py-5 d-none" id="dep-body">

          @if ($dependencias->isEmpty())
            <div class="alert alert-warning mb-0">
              No hay dependencias registradas. Crea una desde el módulo de Dependencias.
            </div>
          @else
            {{-- BUSCADOR --}}
            <div class="input-group input-group-merge mb-3">
              <span class="input-group-text">
                <i class="bx bx-search"></i>
              </span>

              <input type="text" class="form-control" id="buscarDependencia" placeholder="Buscar dependencia..."
                autocomplete="off">
            </div>

            {{-- CONTROLES --}}
            <div class="d-flex justify-content-between align-items-center mb-3">

              <div class="d-flex align-items-center gap-3">

                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" id="seleccionarTodasDeps">

                  <label class="form-check-label" for="seleccionarTodasDeps">
                    Seleccionar visibles
                  </label>
                </div>

                <button type="button" class="btn btn-sm btn-label-secondary" id="limpiarDependencias">
                  Limpiar
                </button>

              </div>

              <span class="badge bg-label-primary" id="contadorDependencias">
                0 seleccionadas
              </span>

            </div>

            {{-- LISTADO --}}
            <div id="listaDependencias" class="border rounded" style="max-height: 400px; overflow-y: auto;">

              @foreach ($dependencias as $dep)
                <label class="dep-item d-flex align-items-start gap-3 px-3 py-3 border-bottom mb-0"
                  for="dep_{{ $dep->id_dependencia }}" data-nombre="{{ strtolower($dep->nombre_dependencia) }}"
                  style="cursor: pointer;">

                  <input class="form-check-input dep-check mt-1" type="checkbox" id="dep_{{ $dep->id_dependencia }}"
                    value="{{ $dep->id_dependencia }}">

                  <div class="flex-grow-1">

                    <span class="fw-semibold d-block">
                      {{ $dep->nombre_dependencia }}
                    </span>

                    <small class="text-secondary">
                      {{ $dep->descripcion ?: 'Sin descripción' }}
                    </small>

                  </div>

                </label>
              @endforeach

            </div>

            <div id="sinResultadosDependencias" class="text-center text-muted py-4 d-none">
              <i class="bx bx-search fs-3 d-block mb-2"></i>
              No se encontraron dependencias.
            </div>

          @endif

        </div>

        <div class="modal-footer border-top py-5">
          <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btnGuardarDeps">
            <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
            Guardar
          </button>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    window.sedes = @json($sedes);
    window.routes = {
      store: '{{ route('sedes.store') }}',
      update: '/sedes/{id}',
      toggleEstado: '/sedes/{id}/toggle-estado',
      dependencias: '/sedes/{id}/dependencias',
    };
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/sedes/sedes-table.js', 'resources/js/pages/sedes/sedes-create.js', 'resources/js/pages/sedes/sedes-edit.js', 'resources/js/pages/sedes/sedes-dependencias.js', 'resources/js/pages/sedes/sedes-actions.js'])
@endsection
