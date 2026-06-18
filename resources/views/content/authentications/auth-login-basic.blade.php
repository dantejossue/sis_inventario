@extends('layouts/blankLayout')

@section('title', 'Login Basic - Pages')

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
  @vite(['resources/css/auth.css'])
@endsection

@section('content')
  <div class="position-relative bg-degrade-moderno min-vh-100 d-flex justify-content-center align-items-center">
    <div class="authentication-wrapper authentication-basic">
      <div class="authentication-inner py-4">
        <!-- Register -->
        <div class="card px-sm-6 px-0">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand mb-2 justify-content-center">
              <a href="{{ url('/') }}" class="app-brand-link">
                {{-- <span class="app-brand-logo demo">@include('_partials.macros')</span>
                <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span> --}}
                <img src="{{ asset('assets/img/undc/escudo-undc.png') }}" alt="Logo OTI UNDC" width="120"
                  class="img-fluid">
              </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-1 text-center text-primary font-w-bold">Inicio de Sesión</h4>
            <p class="mb-6 text-center">Sistema de Gestión de Activos Tecnlógicos</p>

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
                {{-- <label for="email" class="form-label">Email or Username</label>
                <input type="text" class="form-control" id="email" name="email-username"
                  placeholder="Enter your email or username" autofocus /> --}}
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
                {{-- <label class="form-label" for="password">Password</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control" name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                </div> --}}
              </div>
              <div class="mb-8">
                <div class="d-flex justify-content-between">
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="remember-me" />
                    <label class="form-check-label" for="remember-me"> Recuérdame </label>
                  </div>
                  <a href="{{ url('auth/forgot-password-basic') }}">
                    <span>Forgot Password?</span>
                  </a>
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

            <p class="text-center">
              <span>New on our platform?</span>
              <a href="{{ url('auth/register-basic') }}">
                <span>Create an account</span>
              </a>
            </p>
          </div>
        </div>
        <!-- /Register -->
      </div>
    </div>
  </div>
@endsection

@section('page-script')
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
@endsection
