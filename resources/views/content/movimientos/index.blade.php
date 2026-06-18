@extends('layouts/contentNavbarLayout')

@section('title', 'Movimientos - OTI')

@section('content')
  <h4 class="mb-4 fw-bold text-primary d-flex align-items-center">
    <i class="bx bx-transfer me-2"></i> Movimientos de Activos
  </h4>

  <div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold">Historial de Movimientos</h5>
      <a href="{{ route('activos.index') }}" class="btn btn-label-primary">
        <i class="bx bx-list-ul me-1"></i> Ir a Activos para registrar
      </a>
    </div>

    <div class="card-body pt-6">
      <table class="table table-hover small" id="miTablaMovimientos">
        <thead>
          <tr>
            <th class="fw-bold">Código</th>
            <th class="fw-bold">Tipo</th>
            <th class="fw-bold">Activos</th>
            <th class="fw-bold">Origen → Destino</th>
            <th class="fw-bold">Fecha</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Registrado por</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0"></tbody>
      </table>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    window.movimientos = @json($movimientos);
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/movimientos/movimientos-table.js'])
@endsection
