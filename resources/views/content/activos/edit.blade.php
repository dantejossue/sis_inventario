@extends('layouts/contentNavbarLayout')

@section('title', 'Editar Activo - OTI')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-2">
    <div>
      <h4 class="fw-bold mb-0">
        <span class="d-block d-md-inline"><a class="text-secondary" href="{{ route('activos.index') }}">
            Activos Tecnológicos</a>
        </span>
        <span class="d-none d-md-inline"> / </span>
        <span class="d-block d-md-inline">
          Editar Activo
        </span>
      </h4>
      <p class="text-muted fw-light mb-5">Actualiza datos del activo respetando trazabilidad, historial, movimientos,
        inventarios, SIGA, OCS y reportes.
      </p>
    </div>
    <a href="{{ route('activos.index') }}" class="btn btn-secondary">
      <i class="bx bx-arrow-back me-1"></i> Volver
    </a>
  </div>

  <!-- Flujo de edición -->
  <div class="card mb-4 rounded-5">
    <div class="card-body">
      <div class="edit-asset-flow">

        <div class="edit-asset-flow-step active">
          <div class="edit-asset-flow-icon bg-label-primary">
            <i class="bx bx-edit"></i>
          </div>
          <div>
            <h6 class="m-0">Corrección</h6>
            <small>Datos generales permitidos</small>
          </div>
        </div>

        <div class="edit-asset-flow-line"></div>

        <div class="edit-asset-flow-step warning">
          <div class="edit-asset-flow-icon bg-label-warning">
            <i class="bx bx-transfer-alt"></i>
          </div>
          <div>
            <h6 class="m-0">Movimiento</h6>
            <small class="fw-light">Ubicación / responsable</small>
          </div>
        </div>

        <div class="edit-asset-flow-line"></div>

        <div class="edit-asset-flow-step info">
          <div class="edit-asset-flow-icon bg-label-info">
            <i class="bx bx-server"></i>
          </div>
          <div>
            <h6 class="m-0">OCS</h6>
            <small class="fw-light">Datos técnicos</small>
          </div>
        </div>

        <div class="edit-asset-flow-line"></div>

        <div class="edit-asset-flow-step danger">
          <div class="edit-asset-flow-icon bg-label-danger">
            <i class="bx bx-shield-quarter"></i>
          </div>
          <div>
            <h6 class="m-0">Control</h6>
            <small class="fw-light">SIGA / baja / saneamiento</small>
          </div>
        </div>

      </div>
    </div>
  </div>

  <form action="{{ route('activos.update', $activo->id_activo) }}" method="POST" id="formActivo"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @include('content.activos.partials.form-fields')

    <div class="d-flex justify-content-end gap-2 mt-2">
      <a href="{{ route('activos.index') }}" class="btn btn-label-secondary fw-bold">
        <i class="bx bx-x me-1"></i> Cancelar
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="bx bx-save me-1"></i> Actualizar Activo
      </button>
    </div>
  </form>
@endsection

@section('page-script')
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/activos/activos-form.js'])
@endsection
