@extends('layouts/contentNavbarLayout')

@section('title', 'Nuevo Activo - OTI')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-2">
    <div>
      <h4 class="fw-bold mb-0">
        <span class="d-block d-md-inline"><a class="text-secondary" href="{{ route('activos.index') }}">
            Activos Tecnológicos</a>
        </span>
        <span class="d-none d-md-inline"> / </span>
        <span class="d-block d-md-inline">
          Registrar Activo
        </span>
      </h4>
      <p class="text-muted fw-light mb-5">Registra un activo tecnológico con información general, ubicación física,
        responsable, ficha técnica, SIGA, OCS, garantía y documentos.
      </p>

      {{-- <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('activos.index') }}">Activos TI</a></li>
          <li class="breadcrumb-item active">Nuevo</li>
        </ol>
      </nav> --}}
    </div>
    <a href="{{ route('activos.index') }}" class="btn btn-secondary">
      <i class="bx bx-arrow-back me-1"></i> Volver
    </a>
  </div>

  <!-- Pasos -->
  <div class="card mb-4 rounded-5">
    <div class="card-body">
      <div class="register-asset-steps">

        <div class="register-asset-step active">
          <div class="register-asset-step-number">1</div>
          <div>
            <strong>Datos generales</strong>
            <small>Identificación y clasificación</small>
          </div>
        </div>

        <div class="register-asset-step">
          <div class="register-asset-step-number">2</div>
          <div>
            <strong>Responsable y ubicación</strong>
            <small>Colaborador y ambiente físico</small>
          </div>
        </div>

        <div class="register-asset-step">
          <div class="register-asset-step-number">3</div>
          <div>
            <strong>Ficha técnica</strong>
            <small>Hardware, red y software</small>
          </div>
        </div>

        <div class="register-asset-step">
          <div class="register-asset-step-number">4</div>
          <div>
            <strong>Patrimonio</strong>
            <small>SIGA, garantía y documentos</small>
          </div>
        </div>

        {{-- <div class="register-asset-step">
          <div class="register-asset-step-number">5</div>
          <div>
            <strong>Etiqueta</strong>
            <small>QR o código de barras</small>
          </div>
        </div> --}}

      </div>
    </div>
  </div>

  <form action="{{ route('activos.store') }}" method="POST" id="formActivo" enctype="multipart/form-data">
    @csrf

    @include('content.activos.partials.form-fields')

    <div class="d-flex justify-content-end gap-2 mt-2">
      <a href="{{ route('activos.index') }}" class="btn btn-label-secondary fw-bold">
        <i class="bx bx-x me-1"></i> Cancelar
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="bx bx-save me-1"></i> Guardar Activo
      </button>
    </div>
  </form>
@endsection
@section('page-script')
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/activos/activos-form.js'])
@endsection
