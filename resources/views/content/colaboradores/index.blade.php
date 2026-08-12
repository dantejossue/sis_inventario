@extends('layouts/contentNavbarLayout')

@section('title', 'Colaboradores - OTI')

@section('content')
  {{-- <h4 class="mb-4 fw-bold text-secondary d-flex align-items-center">
    <i class="bx bx-group me-1"></i> Gestión de Colaboradores
  </h4> --}}

  <h4 class="fw-bold mb-4">
    <span class="text-secondary d-block d-md-inline">
      Gestión Principal
    </span>
    <span class="d-none d-md-inline"> / </span>
    <span class="d-block d-md-inline">
      Colaboradores
    </span>
  </h4>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
      <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card rounded-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold">Lista de Colaboradores</h5>
      <a href="{{ route('colaboradores.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i> Nuevo Colaborador
      </a>
    </div>

    <div class="card-body pt-6">
      <table class="table table-hover " id="tablaColaboradores">
        <thead>
          <tr>
            <th class="fw-bold">#</th>
            <th class="fw-bold">Colaborador</th>
            <th class="fw-bold">DNI</th>
            <th class="fw-bold">Cargo</th>
            <th class="fw-bold">Dependencia</th>
            <th class="fw-bold">Tipo</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    window.colaboradores = @json($colaboradores);
    window.routesColab = {
      edit: '/colaboradores/{id}/editar',
      toggleEstado: '/colaboradores/{id}/toggle-estado',
    };
    window.avatarDefault = "{{ asset('assets/img/avatars/1.png') }}";
    window.storageUrl = "{{ asset('storage') }}";
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/colaboradores/colaboradores-table.js', 'resources/js/pages/colaboradores/colaboradores-actions.js'])
@endsection
