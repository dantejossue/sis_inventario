@extends('layouts/blankLayout')

@section('title', 'Inicio de Sesión - Inventario OTI')

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
  @vite(['resources/css/auth.css'])
@endsection

@section('content')
  {{-- <div class="position-relative bg-degrade-moderno min-vh-100 d-flex justify-content-center align-items-center">
    <div class="authentication-wrapper authentication-basic">
      <div class="authentication-inner py-4">
        <!-- Register -->
        <div class="card px-sm-6 px-0">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand mb-2 justify-content-center">
              <a href="{{ url('/') }}" class="app-brand-link">
                <img src="{{ asset('assets/img/undc/oti-inventario.png') }}" alt="Logo OTI UNDC" width="300"
                  class="img-fluid">
              </a>
            </div>
            <!-- /Logo -->
            <div class="divider border-top"></div>
            <h4 class="mb-1 text-center text-primary fw-bold pb-2">Inicio de Sesión</h4>

            <form id="formAuthentication" class="mb-6" action="{{ route('login.post') }}" method="POST">
              @csrf
              <div class="mb-3">
                <div class="form-floating">
                  <input type="text" class="form-control @error('nombre_usuario') is-invalid @enderror" id="user"
                    name='nombre_usuario' placeholder="Ingrese el nombre de usuario" aria-describedby="nombre_usuario"
                    required>
                  <label for="user">
                    Usuario Institucional
                  </label>
                  <div id="floatingInputHelp" class="form-text">
                  </div>
                  @error('nombre_usuario')
                    <div class="text-danger mt-1" style="font-size: 0.85rem;">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="mb-6 form-password-toggle">
                <div class="form-floating">
                  <input type="password" class="form-control" id="password" placeholder="Ingrese su contraseña"
                    name="contrasena" aria-describedby="password" required>
                  <label for="password">
                    Contraseña
                  </label>
                  <div id="floatingInputHelp" class="form-text">
                  </div>
                  @error('contrasena')
                    <div class="text-danger mt-1" style="font-size: 0.85rem;">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="mb-8">
                <div class="d-flex justify-content-between">
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="remember-me" />
                    <label class="form-check-label" for="remember-me"> Recuérdame </label>
                  </div>
                </div>
              </div>
              <div class="mb-6">
                <button class="btn btn-primary d-grid w-100" type="submit" id="btnIngresar">
                  <span class="d-flex align-items-center justify-content-center gap-2">
                    <span>
                      <span class="spinner-border spinner-border-sm d-none" id="loginSpinner" role="status"
                        aria-hidden="true"></span>
                      <span id="btnTexto">Ingresar</span>
                    </span>
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
        <!-- /Register -->
      </div>
    </div>
  </div> --}}

  <div class="login-page">
    <main class="login-main">
      {{-- ========================= --}}
      {{-- PANEL IZQUIERDO --}}
      {{-- ========================= --}}
      <section class="login-panel">
        <div class="login-content">
          {{-- logo institucional --}}
          <div class="login-logo">
            <img src="{{ asset('assets/img/undc/escudo-undc.png') }}"
              alt="Oficina de Tecnología de la Información - Inventario" width="300" class="img-fluid">
          </div>
          <div class="divider border-top"></div>

          <div class="login-heading">
            <h1>Inicio de Sesión</h1>
            <p>Ingrese sus credenciales institucionales</p>
          </div>

          {{-- Formulario --}}
          <form id="formAuthentication" action="{{ route('login.post') }}" method="POST">

            @csrf

            {{-- Usuario --}}
            <div class="login-field">
              <label for="user" class="form-label">
                Usuario institucional
              </label>

              <div class="login-input-group">
                <span class="login-input-icon">
                  <i class="bx bx-user"></i>
                </span>

                <input type="text" id="user" name="nombre_usuario" value="{{ old('nombre_usuario') }}"
                  class="form-control @error('nombre_usuario') is-invalid @enderror" placeholder="Ingrese su usuario"
                  autocomplete="username" autofocus required>
              </div>

              @error('nombre_usuario')
                <div class="login-error">
                  {{ $message }}
                </div>
              @enderror
            </div>

            {{-- Contraseña --}}
            <div class="login-field">

              <label for="password" class="form-label">
                Contraseña
              </label>

              <div class="login-input-group">

                <span class="login-input-icon">
                  <i class="bx bx-lock-alt"></i>
                </span>

                <input type="password" id="password" name="contrasena"
                  class="form-control @error('contrasena') is-invalid @enderror" placeholder="Ingrese su contraseña"
                  autocomplete="current-password" required>

                <button type="button" class="login-password-toggle" id="togglePassword"
                  aria-label="Mostrar u ocultar contraseña">

                  <i class="bx bx-hide" id="passwordIcon"></i>
                </button>

              </div>

              @error('contrasena')
                <div class="login-error">
                  {{ $message }}
                </div>
              @enderror

            </div>

            {{-- Opciones --}}
            {{-- <div class="login-options">

              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember-me">

                <label class="form-check-label" for="remember-me">
                  Recuérdame
                </label>
              </div>

            </div> --}}

            {{-- Botón --}}
            <button class="btn-login" type="submit" id="btnIngresar">

              <span class="spinner-border spinner-border-sm d-none" id="loginSpinner" role="status" aria-hidden="true">
              </span>

              <span id="btnTexto">
                Ingresar
              </span>

            </button>

          </form>
        </div>
      </section>
      {{-- ========================= --}}
      {{-- PANEL DERECHO --}}
      {{-- ========================= --}}
      <section class="login-cover" style="background-image: url('{{ asset('assets/img/undc/campus.jpeg') }}');">

        <div class="login-cover-overlay"></div>

        <div class="login-cover-content">

          <span class="login-cover-badge">
            Universidad Nacional de Cañete
          </span>

          <h2>
            Sistema de Gestión de<br>
            Activos Tecnológicos
          </h2>

          <p>
            Oficina de Tecnología de la Información
          </p>

        </div>

      </section>

    </main>


    {{-- ========================= --}}
    {{-- FOOTER --}}
    {{-- ========================= --}}
    <footer class="login-footer">

      <p>
        &copy; {{ date('Y') }} Universidad Nacional de Cañete -
        Oficina de Tecnología de la Información
      </p>

    </footer>

  </div>

@endsection

@section('page-script')

  <script>
    document.addEventListener('DOMContentLoaded', function() {

      const formulario = document.querySelector('#formAuthentication');
      const boton = document.querySelector('#btnIngresar');
      const spinner = document.querySelector('#loginSpinner');
      const textoBoton = document.querySelector('#btnTexto');

      const password = document.querySelector('#password');
      const togglePassword = document.querySelector('#togglePassword');
      const passwordIcon = document.querySelector('#passwordIcon');


      // =====================================
      // Mostrar / ocultar contraseña
      // =====================================

      togglePassword?.addEventListener('click', function() {

        const oculto = password.type === 'password';

        password.type = oculto ? 'text' : 'password';

        passwordIcon.classList.toggle('bx-hide', !oculto);
        passwordIcon.classList.toggle('bx-show', oculto);

      });


      // =====================================
      // Estado de carga del formulario
      // =====================================

      formulario?.addEventListener('submit', function() {

        boton.disabled = true;

        spinner.classList.remove('d-none');

        textoBoton.textContent = 'Ingresando...';

      });

    });
  </script>

@endsection

{{-- @section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const formulario = document.querySelector('#formAuthentication');
      const boton = document.querySelector('#btnIngresar');
      const spinner = document.querySelector('#loginSpinner');
      const textoBoton = document.querySelector('#btnTexto');

      // Escuchamos el evento 'submit' del formulario
      formulario.addEventListener('submit', function(event) {

        // 1. Desactivamos el botón para evitar múltiples clics accidentales
        boton.setAttribute('disabled', 'true');

        // 2. Removemos 'd-none' para que el spinner se vuelva visible
        spinner.classList.remove('d-none');

        // 3. Opcional: Cambiamos el texto para indicar que está procesando
        textoBoton.textContent = 'Ingresando...';

      });
    });
  </script>
@endsection --}}
