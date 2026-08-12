@extends('layouts/contentNavbarLayout')

@section('title', 'Editar Colaborador - OTI')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-4">
    {{-- <div>
      <h4 class="mb-0 fw-bold text-primary">
        <i class="bx bx-edit-alt me-2"></i>Editar Colaborador
      </h4>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('colaboradores.index') }}">Colaboradores</a></li>
          <li class="breadcrumb-item active">{{ $colaborador->per_nombre }} {{ $colaborador->per_apepat }}</li>
        </ol>
      </nav>
    </div> --}}
    <h4 class="fw-bold mb-0">
      <span class="text-secondary d-block d-md-inline">
        <a class="text-secondary" href="{{ route('colaboradores.index') }}">Colaboradores</a>
      </span>
      <span class="d-none d-md-inline"> / </span>
      <span class="d-block d-md-inline">
        Editar{{-- : {{ $colaborador->per_nombre }} {{ $colaborador->per_apepat }} --}}
      </span>
    </h4>
    <a href="{{ route('colaboradores.index') }}" class="btn btn-label-secondary">
      <i class="bx bx-arrow-back me-1"></i> Volver
    </a>
  </div>

  <form action="{{ route('colaboradores.update', $colaborador->id_colaborador) }}" method="POST"
    enctype="multipart/form-data" id="formColaborador">
    @csrf
    @method('PUT')

    @include('content.colaboradores.partials.form-fields')

    <div class="d-flex justify-content-end gap-2 mt-4">
      <a href="{{ route('colaboradores.index') }}" class="btn btn-label-secondary">
        <i class="bx bx-x me-1"></i> Cancelar
      </a>
      <button type="submit" class="btn btn-primary" id="btnGuardar">
        <i class="bx bx-save me-1"></i> Actualizar Colaborador
      </button>
    </div>
  </form>
@endsection

@section('page-script')
  <script>
    window.sedeDependencias = @json($sedeDependencias);
    window.sedeDependenciaActual = {{ $colaborador->id_sede_dependencia ?? 'null' }};
  </script>
  @vite(['resources/js/pages/colaboradores/colaboradores-form.js'])
@endsection
