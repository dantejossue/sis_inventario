@extends('layouts/contentNavbarLayout')

@section('title', 'Nuevo Activo - OTI')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-0 fw-bold text-primary d-flex align-items-center">
        <i class="bx bx-plus-circle me-2"></i>Nuevo Activo
      </h4>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('activos.index') }}">Activos TI</a></li>
          <li class="breadcrumb-item active">Nuevo</li>
        </ol>
      </nav>
    </div>
    <a href="{{ route('activos.index') }}" class="btn btn-secondary">
      <i class="bx bx-arrow-back me-1"></i> Volver
    </a>
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
