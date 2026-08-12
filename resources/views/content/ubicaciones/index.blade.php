@extends('layouts/contentNavbarLayout')

@section('title', 'Ubicaciones - OTI')

@php
  $tipos = ['EDIFICIO', 'PABELLON', 'PISO', 'OFICINA', 'AULA', 'LABORATORIO', 'ALMACEN', 'OTRO'];
  // Opciones de "ubicación superior": solo nodos activos, ordenados por su ruta.
  $padres = collect($ubicaciones)->where('estado', 'ACTIVO')->sortBy('ruta');
@endphp

@section('content')
  <h4 class="mb-4 fw-bold text-primary d-flex align-items-center">
    <i class="bx bx-map me-2"></i> Gestión de Ubicaciones Físicas
  </h4>

  <div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold">Árbol de Ubicaciones</h5>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaUbicacion"
        @if ($sedes->isEmpty()) disabled @endif>
        <i class="bx bx-plus me-1"></i> Nueva Ubicación
      </button>
    </div>

    <div class="card-body pt-6">
      @if ($sedes->isEmpty())
        <div class="alert alert-warning">
          Primero registra al menos una <strong>Sede</strong> activa para poder crear ubicaciones.
        </div>
      @endif

      <table class="table table-hover" id="miTablaUbicaciones">
        <thead>
          <tr>
            {{-- <th class="fw-bold">#</th> --}}
            {{-- <th class="fw-bold">Sede</th> --}}
            <th class="fw-bold">Ubicación</th>
            <th class="fw-bold">Tipo</th>
            <th class="fw-bold">Activos</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0"></tbody>
      </table>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — NUEVA UBICACIÓN
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalNuevaUbicacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-map-pin me-2"></i>Nueva Ubicación
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formNuevaUbicacion" action="{{ route('ubicaciones.store') }}" method="POST">
          @csrf
          <div class="modal-body py-5">

            <div class="form-floating form-floating-outline mb-4">
              <select class="form-select" id="id_sede" name="id_sede">
                <option value="">Seleccionar sede...</option>
                @foreach ($sedes as $sede)
                  <option value="{{ $sede->id_sede }}">{{ $sede->nombre_sede }}</option>
                @endforeach
              </select>
              <label>Sede <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline mb-4">
              <select class="form-select" id="id_ubicacion_padre" name="id_ubicacion_padre">
                <option value="">— Raíz de la sede —</option>
                @foreach ($padres as $p)
                  <option value="{{ $p['id_ubicacion'] }}" data-sede="{{ $p['id_sede'] }}">{{ $p['ruta'] }}</option>
                @endforeach
              </select>
              <label>Ubicación superior</label>
              <div class="invalid-feedback"></div>
              <small class="text-muted">Déjalo en «Raíz» para un edificio/zona de primer nivel.</small>
            </div>

            <div class="row g-4">
              <div class="col-md-7">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control text-uppercase" id="nombre" name="nombre"
                    placeholder="Ej: PABELLÓN A">
                  <label>Nombre <span class="text-danger">*</span></label>
                  <div class="invalid-feedback"></div>
                </div>
              </div>
              <div class="col-md-5">
                <div class="form-floating form-floating-outline">
                  <select class="form-select" id="tipo" name="tipo">
                    @foreach ($tipos as $t)
                      <option value="{{ $t }}" @if ($t === 'OFICINA') selected @endif>
                        {{ $t }}</option>
                    @endforeach
                  </select>
                  <label>Tipo <span class="text-danger">*</span></label>
                  <div class="invalid-feedback"></div>
                </div>
              </div>
            </div>

            <div class="form-floating form-floating-outline mt-4 mb-4">
              <input type="text" class="form-control text-uppercase" id="codigo" name="codigo"
                placeholder="Código de ambiente (opcional)">
              <label>Código</label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Descripción">
              <label>Descripción</label>
              <div class="invalid-feedback"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarUbicacion">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       MODAL — EDITAR UBICACIÓN
  ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalEditarUbicacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-edit-alt me-2"></i>Editar Ubicación
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formEditarUbicacion" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body py-5">

            <div class="form-floating form-floating-outline mb-4">
              <select class="form-select" id="edit-id_sede" name="id_sede">
                <option value="">Seleccionar sede...</option>
                @foreach ($sedes as $sede)
                  <option value="{{ $sede->id_sede }}">{{ $sede->nombre_sede }}</option>
                @endforeach
              </select>
              <label>Sede <span class="text-danger">*</span></label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline mb-4">
              <select class="form-select" id="edit-id_ubicacion_padre" name="id_ubicacion_padre">
                <option value="">— Raíz de la sede —</option>
                @foreach ($padres as $p)
                  <option value="{{ $p['id_ubicacion'] }}" data-sede="{{ $p['id_sede'] }}">{{ $p['ruta'] }}
                  </option>
                @endforeach
              </select>
              <label>Ubicación superior</label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="row g-4">
              <div class="col-md-7">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control text-uppercase" id="edit-nombre" name="nombre"
                    placeholder="Ej: PABELLÓN A">
                  <label>Nombre <span class="text-danger">*</span></label>
                  <div class="invalid-feedback"></div>
                </div>
              </div>
              <div class="col-md-5">
                <div class="form-floating form-floating-outline">
                  <select class="form-select" id="edit-tipo" name="tipo">
                    @foreach ($tipos as $t)
                      <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                  </select>
                  <label>Tipo <span class="text-danger">*</span></label>
                  <div class="invalid-feedback"></div>
                </div>
              </div>
            </div>

            <div class="form-floating form-floating-outline mt-4 mb-4">
              <input type="text" class="form-control text-uppercase" id="edit-codigo" name="codigo"
                placeholder="Código de ambiente (opcional)">
              <label>Código</label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="edit-descripcion" name="descripcion"
                placeholder="Descripción">
              <label>Descripción</label>
              <div class="invalid-feedback"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnActualizarUbicacion">
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
    window.ubicaciones = @json($ubicaciones);
    window.routes = {
      store: '{{ route('ubicaciones.store') }}',
      update: '/ubicaciones/{id}',
      toggleEstado: '/ubicaciones/{id}/toggle-estado',
    };
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/ubicaciones/ubicaciones-table.js', 'resources/js/pages/ubicaciones/ubicaciones-create.js', 'resources/js/pages/ubicaciones/ubicaciones-edit.js', 'resources/js/pages/ubicaciones/ubicaciones-actions.js'])
@endsection
