@extends('layouts/contentNavbarLayout')

@section('title', 'Información OCS - OTI')

@section('content')

  <div id="pagina-ocs" data-url="{{ route('activos.ocs.datos', $activo->id_activo) }}">

    {{-- Encabezado --}}
    <div
      class="d-flex flex-column flex-md-row
             justify-content-between align-items-start
             align-items-md-center mb-4">

      <div>
        <h4 class="fw-bold mb-1">
          <span class="text-secondary fw-bold">
            Activos tecnológicos /
          </span>
          Información OCS Inventory
        </h4>

        <p class="text-muted mb-0">
          Información técnica reportada por el agente OCS.
        </p>
      </div>

      <a href="{{ route('activos.ver', $activo->id_activo) }}" class="btn btn-secondary mt-3 mt-md-0">

        <i class="bx bx-arrow-back me-1"></i>
        Regresar al activo
      </a>
    </div>

    {{-- Identificación del activo Laravel --}}
    <div class="card rounded-5 mb-4">
      <div class="card-body">
        <div class="d-flex flex-column flex-md-row
                 justify-content-between gap-3">

          <div>
            <small class="text-secondary fw-bold d-block">
              Activo registrado
            </small>

            <h5 class="mb-1 fow-bold">
              {{ $activo->codigo_interno }}
            </h5>

            <span class="text-muted">
              Código patrimonial:
              <strong>
                {{ $activo->codigo_patrimonial ?: 'No registrado' }}
              </strong>
            </span>
          </div>

          <div>
            <span class="badge bg-label-info fw-bold">
              Consulta de solo lectura
            </span>
          </div>
        </div>
      </div>
    </div>

    {{-- Estado de carga --}}
    <div id="ocs-estado">
      <div class="card rounded-5">
        <div class="card-body text-center py-5">
          <div class="spinner-border spinner-border-sm me-2" role="status">
          </div>

          Consultando OCS Inventory...
        </div>
      </div>
    </div>

    {{-- Contenido OCS --}}
    <div id="ocs-contenido" class="d-none">

      {{-- Resumen --}}
      <div class="row mb-4" id="ocs-resumen"></div>

      {{-- Hardware --}}
      <div class="card rounded-5 mb-4">
        <div class="card-header rounded-top-5 border-bottom py-4" style="background: #4F46E5">
          <h5 class="m-0 text-white fw-bold d-flex align-items-center">
            <i class="bx bx-chip me-1"></i>
            Hardware
          </h5>

          <small class="text-white opacity-50">
            BIOS, procesador, memoria y almacenamiento.
          </small>
        </div>

        <div class="card-body py-4">

          <ul class="nav nav-tabs mb-4" role="tablist">

            <li class="nav-item">
              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ocs-bios" type="button">
                BIOS
              </button>
            </li>

            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ocs-procesador" type="button">
                Procesador
              </button>
            </li>

            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ocs-memorias" type="button">
                Memorias RAM
              </button>
            </li>

            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ocs-almacenamiento" type="button">
                Almacenamiento
              </button>
            </li>

            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ocs-discos" type="button">
                Unidades
              </button>
            </li>

            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ocs-video" type="button">
                Video
              </button>
            </li>

          </ul>

          <div class="tab-content">

            <div class="tab-pane fade show active" id="ocs-bios">
            </div>

            <div class="tab-pane fade" id="ocs-procesador">
            </div>

            <div class="tab-pane fade" id="ocs-memorias">
            </div>

            <div class="tab-pane fade" id="ocs-almacenamiento">
            </div>

            <div class="tab-pane fade" id="ocs-discos">
            </div>

            <div class="tab-pane fade" id="ocs-video">
            </div>

          </div>
        </div>
      </div>

      {{-- Red --}}
      <div class="card mb-4">
        <div class="card-header rounded-top-5 border-bottom py-4" style="background: #0369A1">
          <h5 class="m-0 text-white fw-bold d-flex align-items-center">
            <i class="bx bx-network-chart me-1"></i>
            Interfaces de red
          </h5>

          <small class="text-white opacity-50">
            Adaptadores físicos y virtuales reportados por OCS.
          </small>
        </div>

        <div class="card-body py-4">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Descripción</th>
                  <th>Tipo</th>
                  <th>Estado</th>
                  <th>IP</th>
                  <th>MAC</th>
                  <th>Velocidad</th>
                  <th>Gateway</th>
                </tr>
              </thead>

              <tbody id="ocs-redes"></tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- Dispositivos --}}
      <div class="card mb-4">
        <div class="card-header rounded-top-5 border-bottom py-4" style="background: #15803D">
          <h5 class="m-0 text-white fw-bold d-flex align-items-center">
            <i class="bx bx-devices me-1"></i>
            Dispositivos vinculados
          </h5>

          <small class="text-white opacity-50">
            Monitores, dispositivos de entrada e impresoras.
          </small>
        </div>

        <div class="card-body py-4">

          <ul class="nav nav-tabs mb-4" role="tablist">

            <li class="nav-item">
              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ocs-monitores" type="button">
                Monitores
              </button>
            </li>

            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ocs-entradas" type="button">
                Dispositivos de entrada
              </button>
            </li>

            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ocs-impresoras" type="button">
                Impresoras
              </button>
            </li>

          </ul>

          <div class="tab-content">

            <div class="tab-pane fade show active" id="ocs-monitores">
            </div>

            <div class="tab-pane fade" id="ocs-entradas">
            </div>

            <div class="tab-pane fade" id="ocs-impresoras">
            </div>

          </div>
        </div>
      </div>

      {{-- Información avanzada --}}
      <div class="card">
        <div class="card-header rounded-top-5 py-5 border-bottom" style="background: #EA580C">
          <h5 class="m-0 text-white fw-bold d-flex align-items-center">
            <i class="bx bx-cog me-1"></i>
            Información avanzada
          </h5>

          <small class="text-white opacity-50">
            Controladores, sonido, puertos y ranuras.
          </small>
        </div>

        <div class="card-body py-4">
          <div class="accordion" id="ocs-avanzado">

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#ocs-controladores">
                  Controladores
                </button>
              </h2>

              <div id="ocs-controladores" class="accordion-collapse collapse show">
                <div class="accordion-body" id="contenido-controladores">
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#ocs-sonidos">
                  Dispositivos de sonido
                </button>
              </h2>

              <div id="ocs-sonidos" class="accordion-collapse collapse">
                <div class="accordion-body" id="contenido-sonidos">
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#ocs-puertos">
                  Puertos
                </button>
              </h2>

              <div id="ocs-puertos" class="accordion-collapse collapse">
                <div class="accordion-body" id="contenido-puertos">
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#ocs-ranuras">
                  Ranuras
                </button>
              </h2>

              <div id="ocs-ranuras" class="accordion-collapse collapse">
                <div class="accordion-body" id="contenido-ranuras">
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>

@endsection

@section('page-script')
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/activos/ocs.js'])
@endsection
