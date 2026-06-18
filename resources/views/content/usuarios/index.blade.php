@extends('layouts/contentNavbarLayout')

@section('title', 'Gestión de Usuarios - OTI')

@section('content')
  <h4 class="mb-4 fw-bold text-primary d-flex align-items-center">
    <i class="bx bx-shield-quarter me-2"></i> Gestión de Usuarios
  </h4>
  <!-- Tabla -->
  <div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold">Lista de Usuarios</h5>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
        <i class="bx bx-plus me-1"></i> Nuevo Usuario
      </button>
    </div>

    <div id="contenedorTabla" class="card-body pt-6">
      <table class="table table-hover" id="miTablaUsuarios">
        <thead>
          <tr>
            <th class="fw-bold">#</th>
            <th class="fw-bold">Nombre Completo</th>
            <th class="fw-bold">Usuario</th>
            <th class="fw-bold">Rol</th>
            <th class="fw-bold">Dependencia</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0"></tbody>
      </table>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       MODAL — NUEVO USUARIO
  ═══════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center"><i class="bx bx-user-plus me-2"></i>Nuevo Usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formNuevoUsuario" action="{{ route('usuarios.store') }}" method="POST">
          @csrf
          <div class="modal-body py-5">
            <div id="errores-crear"></div>

            <div class="form-floating form-floating-outline mb-3">
              <select class="form-select" id="selectColaborador" name="id_colaborador">
                <option value="" disabled selected>Selecciona un colaborador ...</option>
                @foreach ($colaboradores as $col)
                  <option value="{{ $col->id_colaborador }}">
                    {{ $col->per_nombre }} {{ $col->per_apepat }} {{ $col->per_apemat }}
                  </option>
                @endforeach
              </select>
              <label>Colaborador</label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline mb-3">
              <select class="form-select" id="selectRol" name="id_rol">
                <option value="" disabled selected>Seleccione un rol ...</option>
                @foreach ($roles as $rol)
                  <option value="{{ $rol->id_rol }}">{{ $rol->nombre }}</option>
                @endforeach
              </select>
              <label>Rol</label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="row g-2">
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario"
                    placeholder="Ej: dcampos">
                  <label>Usuario Institucional</label>
                  <div class="invalid-feedback"></div>
                </div>
              </div>

              <div class="col-md-6 form-password-toggle">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="••••••••">
                    <label>Contraseña</label>
                  </div>
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
                <div class="invalid-feedback d-block" id="error-contrasena-crear"></div>
              </div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarUsuario">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       MODAL — EDITAR USUARIO
  ═══════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center"><i class="bx bx-edit-alt me-2"></i>Editar Usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formEditarUsuario" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body py-5">
            <div id="errores-editar"></div>

            <div class="mb-3 d-flex align-items-center">
              <p class="text-muted mb-0">
                Colaborador: <strong id="edit-nombre-colaborador">—</strong>
              </p>
              {{-- <label class="form-label fw-semibold text-muted me-2 mb-0">Colaborador: </label>
              <p class="fw-bold mb-0" id="edit-nombre-colaborador">—</p> --}}
            </div>

            <div class="form-floating form-floating-outline mb-3">
              <select class="form-select" id="editSelectRol" name="id_rol">
                @foreach ($roles as $rol)
                  <option value="{{ $rol->id_rol }}">{{ $rol->nombre }}</option>
                @endforeach
              </select>
              <label>Rol</label>
              <div class="invalid-feedback"></div>
            </div>

            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="edit-nombre-usuario" name="nombre_usuario"
                placeholder="Ej: dcampos">
              <label>Usuario Institucional</label>
              <div class="invalid-feedback"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnActualizarUsuario">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Actualizar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       MODAL — CAMBIAR CONTRASEÑA
  ═══════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center"><i class="bx bx-key me-2"></i>Cambiar Contraseña
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formCambiarPassword" method="POST">
          @csrf
          <div class="modal-body py-5">
            <div id="errores-password"></div>

            <p class="text-muted mb-3">
              Usuario: <strong id="pwd-nombre-usuario">—</strong>
            </p>

            <div class="mb-3 form-password-toggle">
              <div class="input-group group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena"
                    placeholder="••••••••">
                  <label>Nueva Contraseña</label>
                </div>
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
              </div>
              <div class="invalid-feedback d-block" id="error-nueva-pwd"></div>
            </div>

            <div class="form-password-toggle">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input type="password" class="form-control" id="nueva_contrasena_confirmation"
                    name="nueva_contrasena_confirmation" placeholder="••••••••">
                  <label>Confirmar Contraseña</label>
                </div>
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
              </div>
              <div class="invalid-feedback d-block" id="error-confirm-pwd"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning" id="btnCambiarPassword">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              Cambiar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    window.usuarios = @json($usuarios);
    window.currentUserId = {{ auth()->id() }};
    window.avatarDefault = "{{ asset('assets/img/avatars/1.png') }}";
    window.routes = {
      toggleEstado: '/usuarios/{id}/toggle-estado',
      update: '/usuarios/{id}',
      changePassword: '/usuarios/{id}/change-password',
    };
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/usuarios/usuarios.js', 'resources/js/pages/usuarios/usuarios-table.js', 'resources/js/pages/usuarios/usuarios-create.js', 'resources/js/pages/usuarios/usuarios-edit.js', 'resources/js/pages/usuarios/usuarios-password.js', 'resources/js/pages/usuarios/usuarios-actions.js'])
@endsection
