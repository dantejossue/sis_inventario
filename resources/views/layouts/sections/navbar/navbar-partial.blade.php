@php
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Route;
@endphp

{{-- Brand demo --}}
@if (isset($navbarFull))
  <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
    <a href="{{ url('/') }}" class="app-brand-link gap-2">
      <span class="app-brand-logo demo">
        @include('_partials.macros')
      </span>

      <span class="app-brand-text demo menu-text fw-bold text-heading">
        {{ config('variables.templateName') }}
      </span>
    </a>
  </div>
@endif

{{-- Menu toggle --}}
@if (!isset($navbarHideToggle))
  <div
    class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0
    {{ isset($contentNavbar) ? 'd-xl-none' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
      <i class="icon-base bx bx-menu icon-md"></i>
    </a>
  </div>
@endif

<div class="navbar-nav-right d-flex align-items-center flex-grow-1 min-w-0" id="navbar-collapse">

  {{-- Reloj --}}
  <div class="navbar-nav align-items-center d-none d-md-flex min-w-0">
    <div class="nav-item d-flex align-items-center min-w-0">
      <i class="icon-base bx bx-time-five icon-md me-2 text-primary flex-shrink-0"></i>

      <span id="navbar-reloj" class="fw-semibold text-heading text-nowrap" style="font-variant-numeric: tabular-nums;">
        —
      </span>
    </div>
  </div>

  <script>
    (function() {
      function tick() {
        const el = document.getElementById('navbar-reloj');
        if (!el) return;

        const now = new Date();

        const opts = {
          timeZone: 'America/Lima'
        };

        const fecha = now.toLocaleDateString('es-PE', {
          ...opts,
          weekday: 'short',
          day: '2-digit',
          month: 'short',
          year: 'numeric'
        });

        const hora = now.toLocaleTimeString('es-PE', {
          ...opts,
          hour12: false
        });

        el.textContent = fecha + ' · ' + hora;
      }

      tick();
      setInterval(tick, 1000);
    })();
  </script>
  {{-- /Reloj --}}

  <ul class="navbar-nav flex-row align-items-center ms-auto flex-shrink-0">

    @php
      $usuario = auth()->user()->colaborador?->nombre_completo ?? auth()->user()->nombre_usuario;
    @endphp

    {{-- Nombre usuario --}}
    <li class="nav-item lh-1 me-3 d-none d-lg-block">
      <span class="text-secondary text-nowrap" style="max-width: 220px;" title="{{ $usuario }}">
        {{ $usuario }}
      </span>
    </li>

    {{-- User --}}
    <li class="nav-item navbar-dropdown dropdown-user dropdown">

      <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
        <div class="avatar avatar-online">
          <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Usuario" class="w-px-40 h-auto rounded-circle">
        </div>
      </a>

      <ul class="dropdown-menu dropdown-menu-end">

        <li>
          <a class="dropdown-item" href="javascript:void(0);">

            <div class="d-flex align-items-center">

              <div class="flex-shrink-0 me-3">
                <div class="avatar avatar-online">
                  <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Usuario"
                    class="w-px-40 h-auto rounded-circle">
                </div>
              </div>

              <div class="flex-grow-1 min-w-0">
                <h6 class="mb-0 text-truncate">
                  {{ Auth::user()->nombre_usuario }}
                </h6>

                <small class="text-muted">
                  Activo
                </small>
              </div>

            </div>

          </a>
        </li>

        <li>
          <div class="dropdown-divider my-1"></div>
        </li>

        {{-- <li>
          <a class="dropdown-item" href="javascript:void(0);">
            <i class="icon-base bx bx-user icon-md me-3"></i>
            <span>My Profile</span>
          </a>
        </li>

        <li>
          <a class="dropdown-item" href="javascript:void(0);">
            <i class="icon-base bx bx-cog icon-md me-3"></i>
            <span>Settings</span>
          </a>
        </li>

        <li>
          <a class="dropdown-item" href="javascript:void(0);">
            <span class="d-flex align-items-center">

              <i class="flex-shrink-0 icon-base bx bx-credit-card icon-md me-3"></i>

              <span class="flex-grow-1">
                Billing Plan
              </span>

              <span class="flex-shrink-0 badge rounded-pill bg-danger">
                4
              </span>

            </span>
          </a>
        </li> --}}

        {{-- <li>
          <div class="dropdown-divider my-1"></div>
        </li> --}}

        <li>
          <a class="dropdown-item" href="{{ route('logout') }}"
            onclick="
              event.preventDefault();
              document.getElementById('logout-form').submit();
            ">
            <i class="icon-base bx bx-power-off icon-md me-3 text-danger"></i>
            <span class="text-danger">
              Cerrar Sesión
            </span>
          </a>

          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
          </form>
        </li>

      </ul>
    </li>
    {{-- /User --}}

  </ul>
</div>
