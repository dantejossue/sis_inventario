@extends('layouts/contentNavbarLayout')

@section('title', 'Nuevo Colaborador - OTI')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-0 fw-bold text-primary d-flex align-items-center">
        <i class="bx bx-user-plus me-2"></i>Nuevo Colaborador
      </h4>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('colaboradores.index') }}">Colaboradores</a></li>
          <li class="breadcrumb-item active">Nuevo</li>
        </ol>
      </nav>
    </div>
    <a href="{{ route('colaboradores.index') }}" class="btn btn-label-secondary">
      <i class="bx bx-arrow-back me-1"></i> Volver
    </a>
  </div>

  <form action="{{ route('colaboradores.store') }}" method="POST" enctype="multipart/form-data" id="formColaborador">
    @csrf

    @include('content.colaboradores.partials.form-fields')

    <div class="d-flex justify-content-end gap-2 mt-4">
      <a href="{{ route('colaboradores.index') }}" class="btn btn-label-secondary">
        <i class="bx bx-x me-1"></i> Cancelar
      </a>
      <button type="submit" class="btn btn-primary" id="btnGuardar">
        <i class="bx bx-save me-1"></i> Guardar Colaborador
      </button>
    </div>
  </form>
@endsection

@section('page-script')
  <script>
    window.sedeDependencias = @json($sedeDependencias);
    window.sedeDependenciaActual = null;
  </script>
  @vite(['resources/js/pages/colaboradores/colaboradores-form.js'])
@endsection
