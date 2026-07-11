@extends('layouts/contentNavbarLayout')

@section('title', 'Ficha del Activo - OTI')

@php
  use Carbon\Carbon;

  $badgeCondicion = [
      'BUENO' => 'success',
      'REGULAR' => 'warning',
      'MALO' => 'danger',
      'RAEE' => 'dark',
      'CHATARRA' => 'secondary',
  ];
  $badgeSituacion = [
      'EN_USO' => 'info',
      'EN_ALMACEN' => 'primary',
      'EN_MANTENIMIENTO' => 'warning',
      'EN_DESPLAZAMIENTO' => 'warning',
      'PENDIENTE_BAJA' => 'danger',
      'DADO_DE_BAJA' => 'secondary',
  ];
  $badgeValidacion = [
      'VALIDADO' => ['success', 'Validado'],
      'PENDIENTE_VALIDACION' => ['warning', 'Pendiente de validación'],
      'OBSERVADO' => ['danger', 'Observado'],
  ];
  $badgeEstadoMov = [
      'REGISTRADO' => 'primary',
      'PENDIENTE_TRAMITE' => 'warning',
      'EN_TRAMITE' => 'warning',
      'AUTORIZADO' => 'info',
      'EJECUTADO' => 'success',
      'RECHAZADO' => 'danger',
      'CANCELADO' => 'secondary',
  ];
  $badgeEstadoSiga = [
      'NO_APLICA' => 'secondary',
      'PENDIENTE_ACTUALIZACION' => 'warning',
      'REGISTRADO' => 'success',
      'OBSERVADO' => 'danger',
  ];
  $iconoCategoria = [
      'LAPTOP' => 'bx-laptop',
      'CPU' => 'bx-desktop',
      'MONITOR' => 'bx-tv',
      'IMPRESORA' => 'bx-printer',
      'PROYECTOR' => 'bx-video',
      'SWITCH' => 'bx-network-chart',
      'ROUTER' => 'bx-wifi',
      'ACCESS POINT' => 'bx-broadcast',
      'SERVIDOR' => 'bx-server',
      'UPS' => 'bx-plug',
      'ESTABILIZADOR' => 'bx-plug',
  ];
  $iconoDoc = [
      'pdf' => ['bxs-file-pdf', 'danger'],
      'jpg' => ['bx-image', 'primary'],
      'jpeg' => ['bx-image', 'primary'],
      'png' => ['bx-image', 'primary'],
      'webp' => ['bx-image', 'primary'],
      'doc' => ['bx-file', 'info'],
      'docx' => ['bx-file', 'info'],
      'xls' => ['bx-spreadsheet', 'success'],
      'xlsx' => ['bx-spreadsheet', 'success'],
  ];

  $tipoLegible = fn($t) => ucfirst(strtolower(str_replace('_', ' ', (string) $t)));
  $fmtFecha = fn($f) => $f ? Carbon::parse($f)->format('d/m/Y') : null;
  $fmtMoneda = fn($v) => $v !== null ? 'S/ ' . number_format((float) $v, 2) : null;
  $nombreUsuario = fn($u) => $u?->colaborador?->nombre_completo ?: $u?->nombre_usuario ?? '—';

  $categoria = $activo->categoria ?? $activo->modelo?->categoriaActivo;
  $marcaModelo = trim(($activo->modelo?->marca?->nombre ?? '') . ' ' . ($activo->modelo?->nombre ?? ''));
  $titulo =
      trim(($categoria ? $tipoLegible($categoria->nombre) . ' ' : '') . $marcaModelo) ?:
      'Activo #' . $activo->id_activo;
  $condicion = $activo->condicion;
  $situacion = $activo->situacion;
  $responsable = $activo->responsable;
  $dependencia = $responsable?->sedeDependencia?->dependencia?->nombre_dependencia;
  $sedeResp = $responsable?->sedeDependencia?->sede?->nombre_sede;
  $siga = $activo->patrimonialSiga;
  $tec = $activo->activoTecnico;
  $documentos = $activo->documentos->sortByDesc('creado_en')->values();

  $garantiaFin = $activo->garantia_fin ? Carbon::parse($activo->garantia_fin) : null;
  $garantiaVigente = $garantiaFin ? $garantiaFin->endOfDay()->isFuture() : null;

  [$valColor, $valTexto] = $badgeValidacion[$activo->estado_validacion] ?? [
      'secondary',
      $tipoLegible($activo->estado_validacion),
  ];
  $ultimoMovimiento = $movimientos->first()?->movimiento;
@endphp

@section('content')

  <!-- Encabezado -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <div>
      <h4 class="fw-bold mb-0">
        <span class="text-secondary">Activos tecnológicos /</span>
        Ficha del activo
      </h4>

      <p class="text-muted fw-light mb-2">
        Información operativa, patrimonial, técnica, documental y de trazabilidad.
      </p>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
      <a href="{{ route('activos.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i>
        Volver
      </a>

      <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEtiquetaActivo">
        <i class="bx bx-qr me-1"></i>
        Etiqueta
      </button>

      <a href="{{ route('activos.edit', $activo->id_activo) }}" class="btn btn-primary">
        <i class="bx bx-edit me-1"></i>
        Editar activo
      </a>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
      <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @php $bajaVigente = $bajas->first(fn($b) => in_array($b->estado, \App\Models\BajaActivo::ESTADOS_ABIERTOS) || $b->estado === 'EJECUTADA'); @endphp
  @if ($bajaVigente)
    <div
      class="alert {{ $bajaVigente->estado === 'EJECUTADA' ? 'alert-danger' : 'alert-warning' }} d-flex align-items-start"
      role="alert">
      <i class="bx bx-down-arrow-circle fs-4 me-2"></i>
      <div>
        <strong>
          {{ $bajaVigente->estado === 'EJECUTADA' ? 'Activo dado de baja' : 'Propuesta de baja en curso' }}
          ({{ $bajaVigente->codigo }})
        </strong>
        <p class="mb-0">
          Causal: {{ ucfirst(strtolower(str_replace('_', ' ', $bajaVigente->causal_baja))) }}
          · Estado: {{ ucfirst(strtolower(str_replace('_', ' ', $bajaVigente->estado))) }}
          @if ($bajaVigente->fecha_baja)
            · Ejecutada el {{ $bajaVigente->fecha_baja->format('d/m/Y') }}
          @endif
          — gestiona el proceso en
          <a href="{{ route('bajas.index') }}" class="alert-link">Bajas de activos</a>.
        </p>
      </div>
    </div>
  @endif

  <!-- Resumen superior -->
  <div class="row g-4">

    <!-- Perfil del activo -->
    <div class="col-xl-4 col-lg-5 col-md-12 mb-4">
      <div class="card asset-main-card rounded-5 h-100">
        <div class="card-body text-center">

          @if ($activo->imagen)
            <img src="{{ asset('storage/' . $activo->imagen) }}" alt="Imagen del activo"
              class="rounded-4 mb-3 mx-auto d-block" style="max-height: 110px; max-width: 100%; object-fit: contain;">
          @else
            <div class="rounded-5 p-4 d-inline-flex bg-label-primary mx-auto mb-3">
              <i class="bx {{ $iconoCategoria[strtoupper($categoria?->nombre ?? '')] ?? 'bx-devices' }}"
                style="font-size: 3rem;"></i>
            </div>
          @endif

          <h4 class="mb-1" style="line-height: 1.1;">{{ $titulo }}</h4>
          <p class="text-secondary fw-light mb-3" style="line-height: 1.1;">
            {{ $activo->descripcion ?: 'Sin descripción registrada.' }}
          </p>

          <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
            @if ($categoria)
              <span class="badge bg-label-primary">{{ $tipoLegible($categoria->nombre) }}</span>
            @endif
            @if ($condicion)
              <span
                class="badge bg-label-{{ $badgeCondicion[$condicion->codigo] ?? 'secondary' }}">{{ $condicion->nombre }}</span>
            @endif
            @if ($situacion)
              <span
                class="badge bg-label-{{ $badgeSituacion[$situacion->codigo] ?? 'secondary' }}">{{ $situacion->nombre }}</span>
            @endif
            <span class="badge bg-label-{{ $valColor }}">{{ $valTexto }}</span>
          </div>

          <div class="asset-code-box mb-4">
            <small class="text-muted d-block">Código patrimonial</small>
            <strong>{{ $activo->codigo_patrimonial }}</strong>
          </div>

          <div class="row text-center">
            <div class="col-4">
              <div class="asset-mini-stat">
                <h5 class="mb-0">{{ $movimientos->count() }}</h5>
                <small>Mov.</small>
              </div>
            </div>

            <div class="col-4">
              <div class="asset-mini-stat">
                <h5 class="mb-0">{{ $mantenimientos->count() }}</h5>
                <small>Mant.</small>
              </div>
            </div>

            <div class="col-4">
              <div class="asset-mini-stat">
                <h5 class="mb-0">{{ $documentos->count() }}</h5>
                <small>Docs.</small>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Datos clave -->
    <div class="col-xl-8 col-lg-7 col-md-12 mb-4">
      <div class="card rounded-5 h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0">Resumen del activo</h5>
            <small class="text-secondary fw-light">
              Datos principales para identificación y control.
            </small>
          </div>

          @if ($situacion)
            <span class="badge bg-label-{{ $badgeSituacion[$situacion->codigo] ?? 'secondary' }}">
              <div class="d-flex align-items-center">
                <i class="bx bx-check-circle me-1"></i>
                <div>{{ $situacion->nombre }}</div>
              </div>
            </span>
          @endif
        </div>

        <div class="card-body">
          <div class="row g-4">

            <div class="col-md-4">
              <div class="info-box">
                <span class="info-label d-block fw-light">Código interno</span>
                <strong>{{ $activo->codigo_interno ?: '—' }}</strong>
              </div>
            </div>

            <div class="col-md-4">
              <div class="info-box d-block">
                <span class="info-label d-block fw-light">N.º de serie</span>
                <strong>{{ $activo->numero_serie ?: '—' }}</strong>
              </div>
            </div>

            <div class="col-md-4">
              <div class="info-box d-block">
                <span class="info-label fw-light d-block">Marca / Modelo</span>
                <strong>{{ $marcaModelo ?: '—' }}</strong>
              </div>
            </div>

            <div class="col-md-4">
              <div class="info-box d-block">
                <span class="info-label fw-light d-block">Responsable actual</span>
                <strong>{{ $responsable?->nombre_completo ?? 'Sin responsable' }}</strong>
                <small
                  class="text-muted d-block">{{ $dependencia ?: ($responsable ? '—' : 'Bajo custodia de almacén') }}</small>
              </div>
            </div>

            <div class="col-md-4">
              <div class="info-box">
                <span class="info-label fw-light d-block">Ubicación física</span>
                <strong>{{ $activo->ubicacion?->nombre ?? 'Sin ubicación' }}</strong>
                <small class="text-muted d-block">
                  {{ $rutaUbicacion ? ($activo->ubicacion?->sede?->nombre_sede ? $activo->ubicacion->sede->nombre_sede . ' › ' : '') . $rutaUbicacion : '—' }}
                </small>
              </div>
            </div>

            <div class="col-md-4">
              <div class="info-box">
                <span class="info-label d-block fw-light">Garantía</span>
                @if ($garantiaFin)
                  <strong class="{{ $garantiaVigente ? 'text-success' : 'text-danger' }}">
                    {{ $garantiaVigente ? 'Vigente' : 'Vencida' }}
                  </strong>
                  <small class="text-muted d-block">Hasta {{ $garantiaFin->format('d/m/Y') }}</small>
                @else
                  <strong>—</strong>
                  <small class="text-muted d-block">Sin registro de garantía</small>
                @endif
              </div>
            </div>

          </div>

          <hr class="my-6">

          <div class="row g-3">

            <div class="col-md-4">
              <div class="d-flex align-items-center bg-label-secondary rounded-5 p-5">
                <i class="bx bx-server me-2"></i>
                <div>
                  <strong class="d-block">OCS Inventory</strong>
                  <small>No vinculado (módulo pendiente)</small>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="d-flex align-items-center bg-label-primary rounded-5 p-5">
                <i class="bx bx-upload me-2"></i>
                <div>
                  <strong class="d-block">Origen del registro</strong>
                  <small>{{ $tipoLegible($activo->origen_registro) }}</small>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div
                class="d-flex align-items-center bg-label-{{ $badgeEstadoSiga[$activo->estado_siga] ?? 'secondary' }} rounded-5 p-5">
                <i class="bx bx-link me-2"></i>
                <div>
                  <strong class="d-block">Estado SIGA</strong>
                  <small>{{ $tipoLegible($activo->estado_siga) }}</small>
                </div>
              </div>
            </div>

          </div>

        </div>
      </div>
    </div>

  </div>

  <!-- Pestañas principales -->
  <div class="card">

    <div class="card-header py-4 border-bottom ">
      <ul class="nav nav-pills card-header-pills flex-column flex-md-row gap-2" role="tablist">

        <li class="nav-item">
          <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general"
            role="tab">
            <i class="bx bx-info-circle me-1"></i>
            General
          </button>
        </li>

        @if ($tec)
          <li class="nav-item">
            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tecnico"
              role="tab">
              <i class="bx bx-chip me-1"></i>
              Técnico
            </button>
          </li>
        @endif

        <li class="nav-item">
          <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-siga" role="tab">
            <i class="bx bx-upload me-1"></i>
            SIGA
          </button>
        </li>

        <li class="nav-item">
          <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-movimientos"
            role="tab">
            <i class="bx bx-transfer-alt me-1"></i>
            Movimientos
            <span class="badge bg-label-primary ms-1">{{ $movimientos->count() }}</span>
          </button>
        </li>

        <li class="nav-item">
          <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-mantenimientos"
            role="tab">
            <i class="bx bx-wrench me-1"></i>
            Mantenimientos
            <span class="badge bg-label-primary ms-1">{{ $mantenimientos->count() }}</span>
          </button>
        </li>

        <li class="nav-item">
          <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documentos"
            role="tab">
            <i class="bx bx-file me-1"></i>
            Documentos
            <span class="badge bg-label-primary ms-1">{{ $documentos->count() }}</span>
          </button>
        </li>

        <li class="nav-item">
          <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-trazabilidad"
            role="tab">
            <i class="bx bx-history me-1"></i>
            Trazabilidad
          </button>
        </li>

      </ul>
    </div>

    <div class="card-body">
      <div class="tab-content p-0 mt-5">

        <!-- TAB GENERAL -->
        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">

          <div class="row g-4">

            <div class="col-lg-6">
              <div class="section-card">
                <div class="section-card-header d-flex align-items-center">
                  <i class="bx bx-id-card me-1"></i>
                  <h6 class="mb-0">Identificación</h6>
                </div>

                <div class="section-card-body">
                  <div class="data-list">
                    <div class="data-list-item">
                      <span>Código patrimonial</span>
                      <strong>{{ $activo->codigo_patrimonial }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Código interno</span>
                      <strong>{{ $activo->codigo_interno ?: '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Código SIGA</span>
                      <strong>{{ $activo->codigo_siga ?: '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Número de serie</span>
                      <strong>{{ $activo->numero_serie ?: '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>N.º PECOSA / Orden compra</span>
                      <strong>{{ $activo->numero_pecosa ?: '—' }} / {{ $activo->numero_orden_compra ?: '—' }}</strong>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="section-card">
                <div class="section-card-header d-flex align-items-center">
                  <i class="bx bx-category me-1"></i>
                  <h6 class="mb-0">Clasificación</h6>
                </div>

                <div class="section-card-body">
                  <div class="data-list">
                    <div class="data-list-item">
                      <span>Categoría</span>
                      <strong>{{ $categoria ? $tipoLegible($categoria->nombre) : '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Marca</span>
                      <strong>{{ $activo->modelo?->marca?->nombre ?? '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Modelo</span>
                      <strong>{{ $activo->modelo?->nombre ?? '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Condición</span>
                      @if ($condicion)
                        <span
                          class="badge bg-label-{{ $badgeCondicion[$condicion->codigo] ?? 'secondary' }}">{{ $condicion->nombre }}</span>
                      @else
                        <strong>—</strong>
                      @endif
                    </div>

                    <div class="data-list-item">
                      <span>Situación</span>
                      @if ($situacion)
                        <span
                          class="badge bg-label-{{ $badgeSituacion[$situacion->codigo] ?? 'secondary' }}">{{ $situacion->nombre }}</span>
                      @else
                        <strong>—</strong>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="section-card">
                <div class="section-card-header d-flex align-items-center">
                  <i class="bx bx-user me-1"></i>
                  <h6 class="mb-0">Responsable y ubicación</h6>
                </div>

                <div class="section-card-body">
                  <div class="data-list">
                    <div class="data-list-item">
                      <span>Responsable actual</span>
                      <strong>{{ $responsable?->nombre_completo ?? 'Sin responsable' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Cargo</span>
                      <strong>{{ $responsable?->cargo ?: '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Dependencia</span>
                      <strong>{{ $dependencia ?: '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Sede</span>
                      <strong>{{ $activo->ubicacion?->sede?->nombre_sede ?? ($sedeResp ?: '—') }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Ubicación física</span>
                      <strong>{{ $rutaUbicacion ?: 'Sin ubicación registrada' }}</strong>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="section-card">
                <div class="section-card-header d-flex align-items-center">
                  <i class="bx bx-purchase-tag me-1"></i>
                  <h6 class="mb-0">Compra y garantía</h6>
                </div>

                <div class="section-card-body">
                  <div class="data-list">
                    <div class="data-list-item">
                      <span>Proveedor</span>
                      <strong>{{ $activo->proveedor ?: '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Orden de compra</span>
                      <strong>{{ $activo->numero_orden_compra ?: '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Fecha adquisición</span>
                      <strong>{{ $fmtFecha($activo->fecha_adquisicion) ?: '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Valor compra</span>
                      <strong>{{ $fmtMoneda($activo->valor_compra) ?: '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Garantía</span>
                      @if ($garantiaFin)
                        <span class="badge bg-label-{{ $garantiaVigente ? 'success' : 'danger' }}">
                          {{ $garantiaVigente ? 'Vigente' : 'Vencida' }} · hasta {{ $garantiaFin->format('d/m/Y') }}
                        </span>
                      @else
                        <strong>—</strong>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>

            @if ($activo->observaciones)
              <div class="col-12">
                <div class="section-card">
                  <div class="section-card-header d-flex align-items-center">
                    <i class="bx bx-message-square-detail me-1"></i>
                    <h6 class="mb-0">Observaciones</h6>
                  </div>
                  <div class="section-card-body">
                    <p class="mb-0 text-muted">{{ $activo->observaciones }}</p>
                  </div>
                </div>
              </div>
            @endif

          </div>

        </div>
        <!-- / TAB GENERAL -->

        @if ($tec)
          <!-- TAB TÉCNICO -->
          <div class="tab-pane fade" id="tab-tecnico" role="tabpanel">

            <div class="row g-4">

              <div class="col-lg-8">
                <div class="section-card">
                  <div class="section-card-header d-flex justify-content-between align-items-center">
                    <h6 class="d-flex align-items-center m-0">
                      <i class="bx bx-chip me-1"></i>
                      Especificaciones técnicas internas
                    </h6>

                    <span class="badge bg-label-info">Ficha OTI</span>
                  </div>

                  <div class="section-card-body">
                    <div class="row g-3">

                      @foreach ([['Procesador', $tec->procesador, 'bx-chip text-primary'], ['Memoria RAM', $tec->memoria_ram, 'bx-memory-card text-info'], ['Almacenamiento', trim(($tec->almacenamiento ?? '') . ' ' . ($tec->tipo_almacenamiento ?? '')) ?: null, 'bx-hdd text-warning'], ['Sistema operativo', $tec->sistema_operativo, 'bx-desktop text-success'], ['Dirección IP', $tec->direccion_ip, 'bx-wifi text-primary'], ['Dirección MAC', $tec->direccion_mac, 'bx-barcode text-secondary'], ['Nombre de equipo', $tec->nombre_equipo, 'bx-laptop text-info'], ['Accesorios', $tec->accesorios, 'bx-plug text-secondary']] as [$label, $valor, $icono])
                        @if ($valor)
                          <div class="col-md-6">
                            <div class="tech-detail-card">
                              <i class="bx {{ $icono }}"></i>
                              <span>{{ $label }}</span>
                              <strong>{{ $valor }}</strong>
                            </div>
                          </div>
                        @endif
                      @endforeach

                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="section-card h-100">
                  <div class="section-card-header">
                    <h6 class="mb-0">
                      <i class="bx bx-check-shield me-1"></i>
                      Estado operativo
                    </h6>
                  </div>

                  <div class="section-card-body">
                    <div class="data-list">
                      <div class="data-list-item">
                        <span>Estado</span>
                        <span
                          class="badge bg-label-{{ $tec->estado_operativo === 'OPERATIVO' ? 'success' : ($tec->estado_operativo === 'DADO_DE_BAJA' ? 'secondary' : 'warning') }}">
                          {{ $tipoLegible($tec->estado_operativo) }}
                        </span>
                      </div>

                      <div class="data-list-item">
                        <span>Antivirus</span>
                        <strong>{{ $tec->antivirus ?: '—' }}</strong>
                      </div>

                      <div class="data-list-item">
                        <span>Office</span>
                        <strong>{{ $tec->licencia_office ?: '—' }}</strong>
                      </div>

                      <div class="data-list-item">
                        <span>Dominio</span>
                        <strong>{{ $tec->dominio ?: '—' }}</strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              @if ($tec->observaciones_tecnicas)
                <div class="col-12">
                  <div class="section-card">
                    <div class="section-card-header">
                      <h6 class="mb-0">
                        <i class="bx bx-message-square-detail me-1"></i>
                        Observaciones técnicas
                      </h6>
                    </div>

                    <div class="section-card-body">
                      <p class="mb-0 text-muted">{{ $tec->observaciones_tecnicas }}</p>
                    </div>
                  </div>
                </div>
              @endif

            </div>

          </div>
          <!-- / TAB TÉCNICO -->
        @endif

        <!-- TAB SIGA -->
        <div class="tab-pane fade" id="tab-siga" role="tabpanel">

          @if ($siga)
            <div class="alert alert-primary d-flex align-items-start">
              <i class="bx bx-info-circle fs-4 me-2"></i>
              <div>
                <strong>Información patrimonial importada desde SIGA.</strong>
                <p class="mb-0">
                  Estos datos sirven como referencia oficial para el control operativo interno.
                  @if ($activo->importacionSiga)
                    Archivo: <strong>{{ $activo->importacionSiga->nombre_archivo }}</strong>
                    ({{ $fmtFecha($activo->importacionSiga->creado_en) }}).
                  @endif
                </p>
              </div>
            </div>

            <div class="row g-4">

              <div class="col-lg-6">
                <div class="section-card">
                  <div class="section-card-header">
                    <h6 class="mb-0">
                      <i class="bx bx-file me-1"></i>
                      Datos patrimoniales
                    </h6>
                  </div>

                  <div class="section-card-body">
                    <div class="data-list">
                      <div class="data-list-item">
                        <span>SBN</span>
                        <strong>{{ $siga->sbn ?: '—' }}</strong>
                      </div>

                      <div class="data-list-item">
                        <span>Descripción SIGA</span>
                        <strong>{{ $siga->descripcion_siga ?: '—' }}</strong>
                      </div>

                      <div class="data-list-item">
                        <span>Centro de costos</span>
                        <strong>{{ $siga->centro_costos ?: '—' }}</strong>
                      </div>

                      <div class="data-list-item">
                        <span>Unidad ejecutora</span>
                        <strong>{{ $siga->unidad_ejecutora ?: '—' }}</strong>
                      </div>

                      <div class="data-list-item">
                        <span>Cuenta contable</span>
                        <strong>{{ $siga->cuenta_contable ?: '—' }}</strong>
                      </div>

                      <div class="data-list-item">
                        <span>Estado conservación SIGA</span>
                        <strong>{{ $siga->estado_conservacion_siga ?: '—' }}</strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="section-card">
                  <div class="section-card-header">
                    <h6 class="mb-0">
                      <i class="bx bx-money me-1"></i>
                      Datos de adquisición
                    </h6>
                  </div>

                  <div class="section-card-body">
                    <div class="data-list">
                      <div class="data-list-item">
                        <span>Proveedor SIGA</span>
                        <strong>{{ $siga->proveedor_siga ?: '—' }}</strong>
                      </div>

                      <div class="data-list-item">
                        <span>Fecha compra</span>
                        <strong>{{ $fmtFecha($siga->fecha_compra) ?: '—' }}</strong>
                      </div>

                      <div class="data-list-item">
                        <span>Fecha alta</span>
                        <strong>{{ $fmtFecha($siga->fecha_alta) ?: '—' }}</strong>
                      </div>

                      <div class="data-list-item">
                        <span>Valor adquisición</span>
                        <strong>{{ $fmtMoneda($siga->valor_adquisicion) ?: '—' }}</strong>
                      </div>

                      <div class="data-list-item">
                        <span>Valor en libros</span>
                        <strong>{{ $fmtMoneda($siga->valor_libros) ?: '—' }}</strong>
                      </div>

                      <div class="data-list-item">
                        <span>Valor neto</span>
                        <strong>{{ $fmtMoneda($siga->valor_neto) ?: '—' }}</strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12">
                <div class="section-card">
                  <div class="section-card-header">
                    <h6 class="mb-0">
                      <i class="bx bx-map me-1"></i>
                      Ubicación SIGA vs ubicación operativa
                    </h6>
                  </div>

                  <div class="section-card-body">
                    <div class="row g-3">

                      <div class="col-md-6">
                        <div class="comparison-box">
                          <span class="comparison-label">Ubicación registrada en SIGA</span>
                          <strong>{{ $siga->sede_ubicacion_siga ?: ($siga->sede_siga ?: '—') }}</strong>
                          <small>Código ubicación SIGA: {{ $siga->codigo_ubicacion_siga ?: '—' }}</small>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="comparison-box">
                          <span class="comparison-label">Ubicación operativa actual</span>
                          <strong>
                            {{ $activo->ubicacion?->sede?->nombre_sede ? $activo->ubicacion->sede->nombre_sede . ' › ' : '' }}{{ $rutaUbicacion ?: 'Sin ubicación registrada' }}
                          </strong>
                          <small>Ubicación física controlada por OTI</small>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>

            </div>
          @else
            <div class="text-center py-5">
              <div class="rounded-5 p-4 d-inline-flex bg-label-secondary mb-3">
                <i class="bx bx-unlink" style="font-size: 2.5rem;"></i>
              </div>
              <h5 class="mb-1">Sin información patrimonial SIGA</h5>
              <p class="text-muted mb-3">
                Este activo aún no está conciliado con el padrón patrimonial.
                Puedes vincularlo mediante una importación SIGA.
              </p>
              <a href="{{ route('importaciones.index') }}" class="btn btn-outline-primary">
                <i class="bx bx-upload me-1"></i>
                Ir a Importación SIGA
              </a>
            </div>
          @endif

        </div>
        <!-- / TAB SIGA -->

        <!-- TAB MOVIMIENTOS -->
        <div class="tab-pane fade" id="tab-movimientos" role="tabpanel">

          <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div>
              <h5 class="mb-1">Historial de movimientos</h5>
              <p class="text-muted mb-0">
                Asignaciones, transferencias, desplazamientos internos y regularizaciones.
              </p>
            </div>

            <a href="{{ route('activos.index') }}" class="btn btn-primary mt-3 mt-md-0">
              <i class="bx bx-plus me-1"></i>
              Nuevo movimiento
            </a>
          </div>

          @if ($movimientos->isEmpty())
            <div class="text-center py-5">
              <div class="rounded-5 p-4 d-inline-flex bg-label-secondary mb-3">
                <i class="bx bx-transfer-alt" style="font-size: 2.5rem;"></i>
              </div>
              <h5 class="mb-1">Sin movimientos registrados</h5>
              <p class="text-muted mb-0">
                Este activo aún no tiene asignaciones ni traslados registrados.
              </p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Tipo</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                  </tr>
                </thead>

                <tbody>
                  @foreach ($movimientos as $det)
                    @php $mov = $det->movimiento; @endphp
                    @if ($mov)
                      <tr>
                        <td>
                          <strong>{{ $mov->codigo_movimiento ?: 'MOV-' . str_pad($mov->id_movimiento, 6, '0', STR_PAD_LEFT) }}</strong>
                        </td>
                        <td>{{ $tipoLegible($mov->tipo) }}</td>
                        <td>
                          {{ $det->responsableOrigen?->nombre_completo ?? ($det->ubicacionOrigen?->nombre ?? 'Almacén') }}
                          @if ($det->responsableOrigen && $det->ubicacionOrigen)
                            <small class="text-muted d-block">{{ $det->ubicacionOrigen->nombre }}</small>
                          @endif
                        </td>
                        <td>
                          {{ $det->responsableDestino?->nombre_completo ?? ($det->ubicacionDestino?->nombre ?? '—') }}
                          @if ($det->responsableDestino && $det->ubicacionDestino)
                            <small class="text-muted d-block">{{ $det->ubicacionDestino->nombre }}</small>
                          @endif
                        </td>
                        <td>
                          <span class="badge bg-label-{{ $badgeEstadoMov[$mov->estado] ?? 'secondary' }}">
                            {{ $tipoLegible($mov->estado) }}
                          </span>
                        </td>
                        <td>{{ $fmtFecha($mov->fecha_movimiento ?: $mov->fecha_registro) ?: '—' }}</td>
                      </tr>
                    @endif
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif

        </div>
        <!-- / TAB MOVIMIENTOS -->

        <!-- TAB MANTENIMIENTOS -->
        <div class="tab-pane fade" id="tab-mantenimientos" role="tabpanel">

          @php
            $badgeEstadoMant = [
                'SOLICITADO' => 'primary',
                'EN_REVISION' => 'info',
                'EN_MANTENIMIENTO' => 'warning',
                'DERIVADO_PROVEEDOR' => 'warning',
                'ATENDIDO' => 'success',
                'SIN_REPARACION' => 'danger',
                'RECOMENDADO_BAJA' => 'danger',
                'CERRADO' => 'success',
                'CANCELADO' => 'secondary',
            ];
            $badgeTipoMant = [
                'PREVENTIVO' => 'info',
                'CORRECTIVO' => 'danger',
                'GARANTIA' => 'primary',
                'REVISION_TECNICA' => 'warning',
            ];
          @endphp

          <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div>
              <h5 class="mb-1">Historial de mantenimientos</h5>
              <p class="text-muted mb-0">
                Preventivos, correctivos, garantías y revisiones técnicas del activo.
              </p>
            </div>

            <a href="{{ route('mantenimientos.index') }}" class="btn btn-primary mt-3 mt-md-0">
              <i class="bx bx-wrench me-1"></i>
              Ir a Mantenimientos
            </a>
          </div>

          @if ($mantenimientos->isEmpty())
            <div class="text-center py-5">
              <div class="rounded-5 p-4 d-inline-flex bg-label-secondary mb-3">
                <i class="bx bx-wrench" style="font-size: 2.5rem;"></i>
              </div>
              <h5 class="mb-1">Sin mantenimientos registrados</h5>
              <p class="text-muted mb-0">
                Este activo aún no tiene mantenimientos ni revisiones técnicas.
              </p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Tipo</th>
                    <th>Problema / Diagnóstico</th>
                    <th>Técnico</th>
                    <th>Estado</th>
                    <th>Costo</th>
                    <th>Fechas</th>
                  </tr>
                </thead>

                <tbody>
                  @foreach ($mantenimientos as $mant)
                    <tr>
                      <td><strong>{{ $mant->codigo }}</strong></td>
                      <td>
                        <span class="badge bg-label-{{ $badgeTipoMant[$mant->tipo_mantenimiento] ?? 'secondary' }}">
                          {{ $tipoLegible($mant->tipo_mantenimiento) }}
                        </span>
                      </td>
                      <td>
                        <span class="d-block">{{ \Illuminate\Support\Str::limit($mant->descripcion, 60) }}</span>
                        @if ($mant->diagnostico)
                          <small
                            class="text-muted">{{ \Illuminate\Support\Str::limit($mant->diagnostico, 60) }}</small>
                        @endif
                      </td>
                      <td>{{ $mant->tecnicoResponsable?->nombre_completo ?? ($mant->proveedor ?: 'Por asignar') }}</td>
                      <td>
                        <span class="badge bg-label-{{ $badgeEstadoMant[$mant->estado] ?? 'secondary' }}">
                          {{ $tipoLegible($mant->estado) }}
                        </span>
                        @if ($mant->recomienda_baja)
                          <span class="badge bg-label-danger d-block mt-1">Recomienda baja</span>
                        @endif
                      </td>
                      <td>{{ $fmtMoneda($mant->costo) ?: '—' }}</td>
                      <td>
                        <span class="d-block">{{ $fmtFecha($mant->fecha_reporte) ?: '—' }}</span>
                        @if ($mant->fecha_fin)
                          <small class="text-muted">Fin: {{ $fmtFecha($mant->fecha_fin) }}</small>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif

        </div>
        <!-- / TAB MANTENIMIENTOS -->

        <!-- TAB DOCUMENTOS -->
        <div class="tab-pane fade" id="tab-documentos" role="tabpanel">

          <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div>
              <h5 class="mb-1">Documentos adjuntos</h5>
              <p class="text-muted mb-0">
                Actas, guías, fotos, informes, trámites y evidencias del activo.
              </p>
            </div>

            <button class="btn btn-primary mt-3 mt-md-0" data-bs-toggle="modal" data-bs-target="#modalSubirDocumento">
              <i class="bx bx-upload me-1"></i>
              Subir documento
            </button>
          </div>

          @if ($documentos->isEmpty())
            <div class="text-center py-5">
              <div class="rounded-5 p-4 d-inline-flex bg-label-secondary mb-3">
                <i class="bx bx-file-blank" style="font-size: 2.5rem;"></i>
              </div>
              <h5 class="mb-1">Sin documentos adjuntos</h5>
              <p class="text-muted mb-0">
                Adjunta actas de asignación, guías, fotos o informes técnicos del activo.
              </p>
            </div>
          @else
            <div class="row g-4">
              @foreach ($documentos as $doc)
                @php [$dIcono, $dColor] = $iconoDoc[$doc->extension] ?? ['bx-file', 'secondary']; @endphp
                <div class="col-md-6 col-lg-4">
                  <div class="document-card">
                    <div class="document-icon bg-label-{{ $dColor }}">
                      <i class="bx {{ $dIcono }}"></i>
                    </div>

                    <div class="document-content">
                      <h6 class="mb-1">
                        {{ $doc->tipo_documento }}{{ $doc->numero_documento ? ' · ' . $doc->numero_documento : '' }}
                      </h6>
                      <small class="text-muted d-block">
                        {{ strtoupper($doc->extension ?: 'ARCHIVO') }}{{ $doc->tamano_kb ? ' · ' . ($doc->tamano_kb >= 1024 ? number_format($doc->tamano_kb / 1024, 1) . ' MB' : $doc->tamano_kb . ' KB') : '' }}
                      </small>
                      <small class="text-muted d-block">
                        Subido: {{ $doc->creado_en?->format('d/m/Y') ?? '—' }} · {{ $nombreUsuario($doc->subidoPor) }}
                      </small>
                    </div>

                    <div class="d-flex flex-column gap-1">
                      <a href="{{ route('documentos.download', $doc->id_documento) }}"
                        class="btn btn-sm btn-icon btn-outline-primary" title="Descargar">
                        <i class="bx bx-download"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-eliminar-doc"
                        data-id="{{ $doc->id_documento }}" data-nombre="{{ $doc->tipo_documento }}"
                        title="Eliminar">
                        <i class="bx bx-trash"></i>
                      </button>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @endif

        </div>
        <!-- / TAB DOCUMENTOS -->

        <!-- TAB TRAZABILIDAD -->
        <div class="tab-pane fade" id="tab-trazabilidad" role="tabpanel">

          <div class="row">

            <div class="col-lg-8">
              <div class="timeline-wrapper">

                @forelse ($eventos as $evento)
                  <div class="timeline-item">
                    <div class="timeline-icon bg-label-{{ $evento['color'] }}">
                      <i class="bx {{ $evento['icono'] }}"></i>
                    </div>

                    <div class="timeline-content">
                      <h6 class="mb-1">{{ $evento['titulo'] }}</h6>
                      <p class="text-muted mb-1">{{ $evento['detalle'] }}</p>
                      <small class="text-muted">{{ $evento['fecha']->format('d/m/Y H:i') }}</small>
                    </div>
                  </div>
                @empty
                  <p class="text-muted">Sin eventos registrados.</p>
                @endforelse

              </div>
            </div>

            <div class="col-lg-4">
              <div class="section-card">
                <div class="section-card-header">
                  <h6 class="mb-0">
                    <i class="bx bx-bar-chart me-1"></i>
                    Resumen de trazabilidad
                  </h6>
                </div>

                <div class="section-card-body">
                  <div class="data-list">
                    <div class="data-list-item">
                      <span>Fecha registro</span>
                      <strong>{{ $fmtFecha($activo->creado_en) ?: '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Registrado por</span>
                      <strong>{{ $nombreUsuario($activo->creadoPor) }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Última modificación</span>
                      <strong>{{ $fmtFecha($activo->actualizado_en) ?: '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Último movimiento</span>
                      <strong>{{ $ultimoMovimiento ? $fmtFecha($ultimoMovimiento->fecha_movimiento ?: $ultimoMovimiento->fecha_registro) : '—' }}</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Estado de validación</span>
                      <span class="badge bg-label-{{ $valColor }}">{{ $valTexto }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>
        <!-- / TAB TRAZABILIDAD -->

      </div>
    </div>
  </div>


  <!-- Modal: etiqueta -->
  <div class="modal fade" id="modalEtiquetaActivo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header border-bottom py-4">
          <div>
            <h5 class="modal-title">
              <i class="bx bx-qr me-1"></i>
              Etiqueta del activo
            </h5>
            <small class="text-muted">
              Vista previa para impresión.
            </small>
          </div>

          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="d-flex justify-content-center">

            <div class="d-inline-flex flex-column align-items-center rounded-4 p-5" style="border: 2px dashed #d9dee3;">

              <div class="text-center border-bottom pb-3" style="line-height: 1;">
                <strong class="d-block">UNDC - Activo Tecnológico</strong>
                <small class="text-secondary">Oficina de Tecnologías de la Información</small>
              </div>
              <div class="d-flex py-3 justify-content-center border-bottom">
                <div class="me-2" id="etiqueta-qr">
                  <i class="bx bx-qr"></i>
                </div>

                <div class="d-flex flex-column align-items-start lh-2">
                  <small><strong>{{ $activo->codigo_interno ?: $activo->codigo_patrimonial }}</strong></small>
                  <small>Patrimonial: {{ $activo->codigo_patrimonial }}</small>
                  <small>Serie: {{ $activo->numero_serie ?: '—' }}</small>
                  <small>{{ $marcaModelo ?: '—' }}</small>
                </div>

              </div>

              <div class="text-center pt-3">
                <svg id="etiqueta-barcode"></svg><br>
                <span>{{ $activo->codigo_patrimonial }}</span>
              </div>

            </div>
          </div>

        </div>

        <div class="modal-footer border-top py-4">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cerrar
          </button>

          <a href="{{ route('activos.etiquetas', ['ids' => $activo->id_activo]) }}" target="_blank"
            class="btn btn-primary">
            <i class="bx bx-printer me-1"></i>
            Imprimir
          </a>
        </div>

      </div>
    </div>
  </div>
  <!-- / Modal: etiqueta -->


  <!-- Modal: subir documento -->
  <div class="modal fade" id="modalSubirDocumento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <form action="{{ route('documentos.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="entidad_tipo" value="ACTIVO">
          <input type="hidden" name="entidad_id" value="{{ $activo->id_activo }}">

          <div class="modal-header">
            <div>
              <h5 class="modal-title">
                <i class="bx bx-upload me-1"></i>
                Subir documento
              </h5>
              <small class="text-muted">
                Adjunta actas, fotos, guías o evidencias del activo.
              </small>
            </div>

            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label" for="doc-tipo">Tipo de documento</label>
                <select class="form-select" id="doc-tipo" name="tipo_documento" required>
                  <option value="">Selecciona…</option>
                  @foreach (['Acta de asignación', 'Acta de devolución', 'Foto', 'Guía de remisión', 'Informe técnico', 'Documento de trámite', 'Otro'] as $tipoDoc)
                    <option value="{{ $tipoDoc }}" @selected(old('tipo_documento') === $tipoDoc)>{{ $tipoDoc }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="doc-numero">Número de documento (opcional)</label>
                <input type="text" class="form-control" id="doc-numero" name="numero_documento"
                  value="{{ old('numero_documento') }}" placeholder="Ej. ACTA-2026-001" maxlength="100" />
              </div>

              <div class="col-md-6">
                <label class="form-label" for="doc-fecha">Fecha del documento (opcional)</label>
                <input type="date" class="form-control" id="doc-fecha" name="fecha_documento"
                  value="{{ old('fecha_documento') }}" />
              </div>

              <div class="col-md-6">
                <label class="form-label" for="doc-archivo">Archivo (PDF, imagen, Word o Excel · máx. 5 MB)</label>
                <input type="file" class="form-control" id="doc-archivo" name="archivo"
                  accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" required />
              </div>

              <div class="col-md-12">
                <label class="form-label" for="doc-descripcion">Descripción (opcional)</label>
                <textarea class="form-control" id="doc-descripcion" name="descripcion" rows="3" maxlength="255"
                  placeholder="Descripción breve del documento...">{{ old('descripcion') }}</textarea>
              </div>

            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              Cancelar
            </button>

            <button type="submit" class="btn btn-primary">
              <i class="bx bx-save me-1"></i>
              Guardar documento
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
  <!-- / Modal: subir documento -->

@endsection

@section('page-script')
  <script>
    window.routesFicha = {
      documentosDestroy: @json(url('/documentos')) + '/{id}',
      qrUrl: @json($activo->qr_token ? route('activos.qr', $activo->qr_token) : null),
      codigoPatrimonial: @json($activo->codigo_patrimonial)
    };
    window.reabrirModalDocumento = @json($errors->any());
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/activos/activos-ver.js'])
@endsection
