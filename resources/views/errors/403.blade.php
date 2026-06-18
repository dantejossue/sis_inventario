@extends('layouts/blankLayout')

@section('title', 'Acceso Denegado')

@section('content')
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="text-center px-4">
      <div class="mb-4">
        <i class="bx bx-lock-alt text-danger" style="font-size: 5rem;"></i>
      </div>
      <h1 class="display-1 fw-bold text-danger">403</h1>
      <h4 class="mb-2">Acceso Denegado</h4>
      <p class="text-muted mb-4">
        {{ $exception->getMessage() ?: 'No tiene permiso para acceder a esta sección.' }}
      </p>
      <a href="{{ route('home') }}" class="btn btn-primary">
        <i class="bx bx-home me-1"></i> Volver al inicio
      </a>
    </div>
  </div>
@endsection
