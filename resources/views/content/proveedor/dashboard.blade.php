@extends('layouts/contentNavbarLayout')

@section('title', 'Portal Proveedor - OTI')

@section('content')
  <div class="row mb-4 align-items-center">
    <div class="col">
      <h4 class="fw-bold mb-1 text-primary d-flex align-items-center">
        <i class="bx bx-store-alt me-2"></i> Portal Proveedor
      </h4>
      <p class="text-muted mb-0">
        Bienvenido, <strong>{{ auth()->user()->colaborador->nombres ?? auth()->user()->nombre_usuario }}</strong>.
        Aquí podrás gestionar tus entregas y garantías pendientes.
      </p>
    </div>
  </div>

  <div class="row g-4">
    {{-- Tarjeta: Órdenes pendientes --}}
    <div class="col-sm-6 col-xl-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column align-items-center text-center p-5">
          <div class="avatar avatar-lg mb-3">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-package bx-lg"></i>
            </span>
          </div>
          <h5 class="mb-1">Órdenes de Compra</h5>
          <p class="text-muted small mb-3">Consulta tus órdenes pendientes de entrega.</p>
          <span class="badge bg-label-secondary">Próximamente</span>
        </div>
      </div>
    </div>

    {{-- Tarjeta: Garantías --}}
    <div class="col-sm-6 col-xl-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column align-items-center text-center p-5">
          <div class="avatar avatar-lg mb-3">
            <span class="avatar-initial rounded bg-label-info">
              <i class="bx bx-shield-quarter bx-lg"></i>
            </span>
          </div>
          <h5 class="mb-1">Garantías</h5>
          <p class="text-muted small mb-3">Registra y da seguimiento a garantías de equipos.</p>
          <span class="badge bg-label-secondary">Próximamente</span>
        </div>
      </div>
    </div>

    {{-- Tarjeta: Entregas --}}
    <div class="col-sm-6 col-xl-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column align-items-center text-center p-5">
          <div class="avatar avatar-lg mb-3">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-check-circle bx-lg"></i>
            </span>
          </div>
          <h5 class="mb-1">Confirmación de Entregas</h5>
          <p class="text-muted small mb-3">Confirma la entrega de activos a la universidad.</p>
          <span class="badge bg-label-secondary">Próximamente</span>
        </div>
      </div>
    </div>
  </div>
@endsection
