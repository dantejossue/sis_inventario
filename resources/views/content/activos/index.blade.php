@extends('layouts/contentNavbarLayout')

@section('title', 'Activos TI - OTI')

@section('content')
  <style>
    @keyframes flashScan {
      0% {
        background-color: rgba(105, 108, 255, .35);
      }

      100% {
        background-color: transparent;
      }
    }

    #miTablaActivos tr.flash-scan>* {
      animation: flashScan 1.2s ease-out;
    }
  </style>

  <h4 class="fw-bold mb-0">
    <span class="text-secondary d-block d-md-inline">
      Gestión Principal
    </span>
    <span class="d-none d-md-inline"> / </span>
    <span class="d-block d-md-inline">
      Activos Tecnológicos
    </span>
  </h4>
  <p class="text-muted fw-light mb-5">Consulta, filtra y administra los activos tecnológicos registrados en el sistema.</p>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
      <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <!-- KPIs rápidos -->
  <div class="row g-4">

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card rounded-5 kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Total activos</span>
              <h3 class="card-title mb-2">1,248</h3>
              <small class="text-success fw-semibold">
                <i class="bx bx-check-circle"></i>
                1,117 validados
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-laptop fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card rounded-5 kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">En uso</span>
              <h3 class="card-title mb-2">842</h3>
              <small class="text-muted fw-semibold">
                Asignados a usuarios
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="bx bx-user-check fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card rounded-5 kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Observados</span>
              <h3 class="card-title mb-2">38</h3>
              <small class="text-warning fw-semibold">
                Requieren revisión
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="bx bx-error-circle fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card rounded-5 kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Sin etiqueta</span>
              <h3 class="card-title mb-2">21</h3>
              <small class="text-danger fw-semibold">
                Pendiente QR / barras
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="bx bx-barcode fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Filtros -->
  <div class="card rounded-4 mb-4">
    <div class="card-header">
      <h5 class="mb-0">Filtros de búsqueda</h5>
      <small class="text-muted">
        Filtra por sede, categoría, estado, responsable o información patrimonial.
      </small>
    </div>

    <div class="card-body">
      <div class="row g-3">

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Código / Serie</label>
          <input type="text" class="form-control" placeholder="Patrimonial, interno o serie" />
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Categoría</label>
          <select class="form-select">
            <option selected>Todas</option>
            <option>Laptop</option>
            <option>CPU</option>
            <option>Monitor</option>
            <option>Impresora</option>
            <option>Switch</option>
            <option>Access Point</option>
          </select>
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Sede</label>
          <select class="form-select">
            <option selected>Todas las sedes</option>
            <option>Sede Central</option>
            <option>Campus Académico</option>
            <option>Filial Administrativa</option>
          </select>
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Situación</label>
          <select class="form-select">
            <option selected>Todas</option>
            <option>En uso</option>
            <option>En almacén</option>
            <option>En mantenimiento</option>
            <option>Pendiente de baja</option>
            <option>Dado de baja</option>
          </select>
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Condición</label>
          <select class="form-select">
            <option selected>Todas</option>
            <option>Bueno</option>
            <option>Regular</option>
            <option>Malo</option>
            <option>RAEE</option>
          </select>
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Responsable</label>
          <input type="text" class="form-control" placeholder="Nombre del responsable" />
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Estado SIGA</label>
          <select class="form-select">
            <option selected>Todos</option>
            <option>Validado</option>
            <option>Pendiente validación</option>
            <option>Observado</option>
          </select>
        </div>

        <div class="col-lg-3 col-md-6 d-flex align-items-end">
          <div class="d-flex gap-2 w-100">
            <button class="btn btn-primary w-100">
              <i class="bx bx-search me-1"></i>
              Buscar
            </button>

            <button class="btn btn-outline-secondary">
              <i class="bx bx-reset"></i>
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div class="card rounded-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold">Inventario de Activos</h5>
      <a href="{{ route('activos.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i> Nuevo Activo
      </a>
    </div>

    <div class="card-body pt-4">

      {{-- ══ Barra de acciones masivas ══════════════════════════════════════════ --}}
      <div id="bulk-bar" class="d-none alert alert-primary py-2 px-3 d-flex align-items-center gap-2 mb-3 rounded">
        <i class="bx bx-check-square fs-5"></i>
        <span id="bulk-count" class="fw-semibold"></span>
        <div class="ms-auto d-flex gap-2">
          <button type="button" class="btn btn-sm btn-warning" id="btn-mover-bulk">
            <i class="bx bx-transfer me-1"></i> Mover
          </button>
          <a href="#" id="btn-etiquetas-bulk" target="_blank" class="btn btn-sm btn-label-primary">
            <i class="bx bx-printer me-1"></i> Etiquetas
          </a>
          <button type="button" class="btn btn-sm btn-label-secondary" id="btn-deselect-all">
            Deseleccionar todo
          </button>
        </div>
      </div>

      {{-- ══ Escaneo con lector láser ═══════════════════════════════════════════ --}}
      <div class="row mb-3">
        <div class="col-md-6 col-lg-5">
          <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-barcode-reader"></i></span>
            <input type="text" class="form-control" id="scan-input" autocomplete="off"
              placeholder="Escanea un código de barras para seleccionar…">
            <button class="btn btn-outline-primary" type="button" id="scan-clear" title="Limpiar">
              <i class="bx bx-x"></i>
            </button>
          </div>
          <small class="text-muted">Selecciona automáticamente el activo al escanear su etiqueta.</small>
        </div>
      </div>

      {{-- ══ Leyenda de situaciones ════════════════════════════════════════════ --}}
      {{-- <div class="d-flex flex-wrap align-items-center gap-3 mb-3 pb-1">
        <span class="fw-semibold text-muted small text-uppercase me-1">
          <i class="bx bx-palette me-1"></i>Situación:
        </span>
        @php
          $leyenda = [
              'table-success' => 'Operativo',
              'table-warning' => 'En mantenimiento',
              'table-info' => 'En préstamo',
              'table-primary' => 'Asignado',
              'table-secondary' => 'En almacén',
              'table-danger' => 'Dado de baja',
          ];
        @endphp
        @foreach ($leyenda as $clase => $texto)
          <span class="d-inline-flex align-items-center gap-1 small">
            <span class="{{ $clase }} border rounded"
              style="display:inline-block;width:16px;height:16px;background:var(--bs-table-bg);"></span>
            {{ $texto }}
          </span>
        @endforeach
      </div> --}}

      {{-- ══ Tabla ═══════════════════════════════════════════════════════════════ --}}
      <table class="table table-hover small" id="miTablaActivos">
        <thead>
          <tr>
            <th class="text-center"><input type="checkbox" class="form-check-input" id="check-all"></th>
            <th class="fw-bold">Código</th>
            <th class="fw-bold">Modelo</th>
            {{-- <th class="fw-bold">N° Serie</th> --}}
            {{-- <th class="fw-bold">Sede</th> --}}
            <th class="fw-bold">Ubicación</th>
            <th class="fw-bold">Responsable</th>
            <th class="fw-bold">Condición</th>
            <th class="fw-bold">Situación</th>
            <th class="fw-bold">Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0"></tbody>
      </table>
    </div>
  </div>


  {{-- ════════════════════════════════════════════════════════════════════════════
       MODAL — MOVER ACTIVO(S)
  ════════════════════════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalMover" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-transfer me-2"></i> Registrar Movimiento
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formMover">
          @csrf
          <input type="hidden" id="mover-activo-ids" name="activo_ids_json">
          <div class="modal-body py-5 px-5">

            {{-- Lista de activos a mover --}}
            <div class="mb-4">
              <p class="fw-semibold mb-2 text-muted small text-uppercase">Activos seleccionados:</p>
              <div id="mover-lista-activos" class="d-flex flex-wrap gap-2"></div>
            </div>

            <div class="row g-4">

              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <select class="form-select" id="mover-tipo" name="tipo">
                    <option value="">Seleccionar tipo...</option>
                    <option value="PRESTAMO">Préstamo</option>
                    <option value="TRANSFERENCIA">Transferencia</option>
                    <option value="REGULARIZACION">Regularización</option>
                  </select>
                  <label>Tipo de Movimiento <span class="text-danger">*</span></label>
                  <div class="invalid-feedback"></div>
                </div>
                <small class="text-muted" id="mover-tipo-ayuda"></small>
              </div>

              <div class="col-md-6 d-none" id="mover-colaborador-wrap">
                <div class="form-floating form-floating-outline">
                  <select class="form-select" id="mover-colaborador" name="id_colaborador_destino">
                    <option value="">Seleccionar colaborador...</option>
                    @foreach ($colaboradores as $col)
                      <option value="{{ $col->id_colaborador }}">
                        {{ $col->per_apepat }} {{ $col->per_apemat }}, {{ $col->per_nombre }}
                        @if ($col->cargo)
                          — {{ $col->cargo }}
                        @endif
                      </option>
                    @endforeach
                  </select>
                  <label>Colaborador destino <span class="text-danger">*</span></label>
                  <div class="invalid-feedback"></div>
                </div>
              </div>

              <div class="col-md-6 d-none" id="mover-ubicacion-wrap">
                <div class="form-floating form-floating-outline">
                  <select class="form-select" id="mover-ubicacion" name="id_ubicacion_destino">
                    <option value="">Sin cambio de ubicación</option>
                    @foreach ($ubicaciones->groupBy(fn($u) => $u->sede?->nombre_sede ?? 'Sin sede') as $sedeNombre => $items)
                      <optgroup label="{{ $sedeNombre }}">
                        @foreach ($items as $u)
                          <option value="{{ $u->id_ubicacion }}">
                            {{ $u->nombre }} ({{ $u->tipo }}){{ $u->codigo ? ' — ' . $u->codigo : '' }}
                          </option>
                        @endforeach
                      </optgroup>
                    @endforeach
                  </select>
                  <label>Ubicación destino</label>
                  <div class="invalid-feedback"></div>
                </div>
              </div>

              <div class="col-md-6 d-none" id="mover-devolucion-wrap">
                <div class="form-floating form-floating-outline">
                  <input type="date" class="form-control" id="mover-devolucion" name="fecha_devolucion_estimada">
                  <label>Fecha estimada de devolución <span class="text-danger">*</span></label>
                  <div class="invalid-feedback"></div>
                </div>
              </div>

              {{-- Solo REGULARIZACION: corrección de condición / situación --}}
              <div class="col-md-6 d-none" id="mover-condicion-wrap">
                <div class="form-floating form-floating-outline">
                  <select class="form-select" id="mover-condicion" name="condicion_actual">
                    <option value="">Sin cambio de condición</option>
                    @foreach (\App\Models\Activo::CONDICION_LABELS as $code => $label)
                      <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <label>Condición (regularizar)</label>
                  <div class="invalid-feedback"></div>
                </div>
              </div>

              <div class="col-md-6 d-none" id="mover-situacion-wrap">
                <div class="form-floating form-floating-outline">
                  <select class="form-select" id="mover-situacion" name="situacion_actual">
                    <option value="">Sin cambio de situación</option>
                    @foreach (\App\Models\Activo::SITUACION_LABELS as $code => $label)
                      <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <label>Situación (regularizar)</label>
                  <div class="invalid-feedback"></div>
                </div>
              </div>

              <div class="col-md-12">
                <div class="form-floating form-floating-outline">
                  <textarea class="form-control" id="mover-motivo" name="motivo" placeholder="Motivo u observaciones"
                    style="height:80px"></textarea>
                  <label>Motivo / Observaciones</label>
                  <div class="invalid-feedback"></div>
                </div>
              </div>

            </div>
          </div>

          <div class="modal-footer border-top py-5">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning" id="btnConfirmarMover">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              <i class="bx bx-transfer me-1"></i> Confirmar Movimiento
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>


  {{-- ════════════════════════════════════════════════════════════════════════════
       MODAL — UBICACIÓN FÍSICA COMPLETA
  ════════════════════════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalUbicacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-5">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-map me-2 text-primary"></i> Ubicación Física Actual
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body py-5">
          <div id="ubic-sin" class="alert alert-warning mb-0 d-none">
            <i class="bx bx-info-circle me-1"></i> Este activo no tiene una ubicación física asignada.
          </div>

          <div id="ubic-detalle" class="row g-3">
            <div class="col-12">
              <p class="text-muted small mb-1 text-uppercase fw-semibold">Activo</p>
              <p class="fw-semibold mb-0" id="ubic-activo">—</p>
            </div>

            <div class="col-12">
              <hr class="my-1">
            </div>

            <div class="col-12">
              <p class="text-muted small mb-1">Ruta completa</p>
              <p class="fw-semibold mb-0" id="ubic-ruta">—</p>
            </div>

            <div class="col-md-6">
              <p class="text-muted small mb-1">Sede / Campus</p>
              <p class="fw-semibold mb-0" id="ubic-sede">—</p>
            </div>
            <div class="col-md-6">
              <p class="text-muted small mb-1">Dirección de la sede</p>
              <p class="fw-semibold mb-0" id="ubic-direccion">—</p>
            </div>

            <div class="col-12">
              <hr class="my-1">
            </div>

            <div class="col-md-6">
              <p class="text-muted small mb-1">Ubicación</p>
              <p class="fw-semibold mb-0" id="ubic-nombre">—</p>
            </div>
            <div class="col-md-3">
              <p class="text-muted small mb-1">Tipo</p>
              <p class="mb-0" id="ubic-tipo">—</p>
            </div>
            <div class="col-md-3">
              <p class="text-muted small mb-1">Código</p>
              <p class="mb-0" id="ubic-codigo">—</p>
            </div>

            <div class="col-12">
              <p class="text-muted small mb-1">Descripción</p>
              <p class="mb-0" id="ubic-descripcion">—</p>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top py-5">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  {{-- ════════════════════════════════════════════════════════════════════════════
       MODAL — VER FICHA RAPIDA
  ════════════════════════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalFichaRapida" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">

        <div class="modal-header border-bottom py-5 d-block">
          <div class="d-flex align-items-center">
            <i class="bx bx-laptop me-1"></i>
            <h5 class="modal-title fw-bold">Ficha rápida del activo</h5>
          </div>
          <small class="text-muted fw-light">
            Resumen operativo, patrimonial y técnico del activo.
          </small>

          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="row">

            <!-- Resumen del activo -->
            <div class="col-md-4 mb-4">
              <div class="border rounded-4 p-6 text-center">
                <div class="bg-label-primary mx-auto mb-3 rounded-5 d-inline-flex p-3" style="font-size: 2rem;">
                  💻
                </div>

                <h5 class="mb-1" style="line-height: 1.1;">Laptop Lenovo ThinkPad E14</h5>
                <p class="text-muted mb-2">
                  Categoría: Laptop</p>

                <div class="d-flex justify-content-center gap-2 mb-3">
                  <span class="badge bg-label-success">Bueno</span>
                  <span class="badge bg-label-info">En uso</span>
                </div>

                <button class="btn btn-primary btn-sm w-100">
                  <i class="bx bx-detail me-1"></i>
                  Ver ficha completa
                </button>
              </div>
            </div>

            <!-- Datos principales -->
            <div class="col-md-8">

              <div class="row g-3">

                <div class="col-md-6">
                  <label class="text-muted small">Código patrimonial</label>
                  <div class="fw-bold">740899500012</div>
                </div>

                <div class="col-md-6">
                  <label class="text-muted small">Código interno</label>
                  <div class="fw-bold">LAP-OTI-000124</div>
                </div>

                <div class="col-md-6">
                  <label class="text-muted small">N.º de serie</label>
                  <div class="fw-bold">PF3X92LA</div>
                </div>

                <div class="col-md-6">
                  <label class="text-muted small">Marca / Modelo</label>
                  <div class="fw-bold">Lenovo ThinkPad E14</div>
                </div>

                <div class="col-md-6">
                  <label class="text-muted small">Responsable</label>
                  <div class="fw-bold">María Torres</div>
                  <small class="text-muted">Dirección Académica</small>
                </div>

                <div class="col-md-6">
                  <label class="text-muted small">Ubicación actual</label>
                  <div class="fw-bold">Oficina 204</div>
                  <small class="text-muted">Sede Central › Pabellón A › Piso 2</small>
                </div>

                <div class="col-md-6">
                  <label class="text-muted small">Garantía</label>
                  <div>
                    <span class="badge bg-label-success">Vigente hasta 15/11/2026</span>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="text-muted small">OCS Inventory</label>
                  <div>
                    <span class="badge bg-label-success">
                      <i class="bx bx-check-circle me-1"></i>
                      Sincronizado
                    </span>
                  </div>
                </div>

              </div>

            </div>

          </div>

          <hr class="my-4" />

          <!-- Datos técnicos breves -->
          <div class="row g-3">

            <div class="col-md-3 col-6">
              <div class="border rounded-4 p-5">
                <i class="bx bx-chip text-primary"></i>
                <span class="d-block text-muted small">Procesador</span>
                <strong>Intel Core i5</strong>
              </div>
            </div>

            <div class="col-md-3 col-6">
              <div class="border rounded-4 p-5">
                <i class="bx bx-memory-card text-info"></i>
                <span class="d-block text-muted small">RAM</span>
                <strong>16 GB</strong>
              </div>
            </div>

            <div class="col-md-3 col-6">
              <div class="border rounded-4 p-5">
                <i class="bx bx-hdd text-warning"></i>
                <span class="d-block text-muted small">Disco</span>
                <strong>512 GB SSD</strong>
              </div>
            </div>

            <div class="col-md-3 col-6">
              <div class="border rounded-4 p-5">
                <i class="bx bx-desktop text-success"></i>
                <span class="d-block text-muted small">Sistema</span>
                <strong>Windows 11</strong>
              </div>
            </div>

          </div>

        </div>

        <div class="modal-footer border-top py-5">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cerrar
          </button>

          <a href="ficha-activo.html" class="btn btn-primary">
            Abrir ficha completa
          </a>
        </div>

      </div>
    </div>
  </div>

  <!-- Modal: etiqueta -->
  <div class="modal fade" id="modalEtiqueta" tabindex="-1" aria-hidden="true">
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
                <div class="me-2" id="eti-qr">
                  <i class="bx bx-qr"></i>
                </div>

                <div class="d-flex flex-column align-items-start lh-2">
                  <strong><small id="eti_cod_interno"></small></strong>
                  <small>Patrimonial: <small id="eti_cod_patrimonial"></small></small>
                  <small>Serie: <small id="eti_num_serie"></small></small>
                  <small id="eti_marca"></small>
                </div>

              </div>

              <div class="text-center pt-3">
                <svg id="eti-barcode"></svg><br>
                <span id="eti_barcode_text"></span>
              </div>

            </div>
          </div>

        </div>

        <div class="modal-footer border-top py-4">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cerrar
          </button>

          {{-- <a href="{{ route('activos.etiquetas', ['ids' => $activo->id_activo]) }}" target="_blank"
            class="btn btn-primary">
            <i class="bx bx-printer me-1"></i>
            Imprimir
          </a> --}}
        </div>

      </div>
    </div>
  </div>
  <!-- / Modal: etiqueta -->

  {{-- ════════════════════════════════════════════════════════════════════════════
       OFFCANVAS — MÁS INFO
  ════════════════════════════════════════════════════════════════════════════ --}}
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasMasInfo" style="width:420px">
    <div class="offcanvas-header border-bottom py-5">
      <h5 class="offcanvas-title fw-bold d-flex align-items-center">
        <i class="bx bx-info-circle me-2 text-primary"></i>
        <span id="info-titulo">Detalle del Activo</span>
      </h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body py-4">

      <div class="row g-3" id="info-body">

        <div class="col-12 text-center mb-1">
          <img id="info-imagen" src="" alt="Imagen del activo" class="rounded shadow-sm d-none"
            style="max-width:100%;max-height:200px;object-fit:contain;background:#f1f1f4;">
        </div>

        <div class="col-6">
          <p class="text-muted small mb-0">Código Interno</p>
          <p class="fw-semibold mb-0" id="info-codigo-interno">—</p>
        </div>
        <div class="col-6">
          <p class="text-muted small mb-0">Código Patrimonial</p>
          <p class="fw-semibold mb-0" id="info-codigo-patrimonial">—</p>
        </div>

        <div class="col-12">
          <hr class="my-1">
        </div>

        <div class="col-6">
          <p class="text-muted small mb-0">Marca</p>
          <p class="fw-semibold mb-0" id="info-marca">—</p>
        </div>
        <div class="col-6">
          <p class="text-muted small mb-0">Modelo</p>
          <p class="fw-semibold mb-0" id="info-modelo">—</p>
        </div>
        <div class="col-6">
          <p class="text-muted small mb-0">Categoría</p>
          <p class="fw-semibold mb-0" id="info-categoria">—</p>
        </div>
        <div class="col-6">
          <p class="text-muted small mb-0">N° Serie</p>
          <p class="fw-semibold mb-0" id="info-serie">—</p>
        </div>

        <div class="col-12">
          <hr class="my-1">
        </div>

        <div class="col-6">
          <p class="text-muted small mb-0">Condición</p>
          <p class="mb-0" id="info-condicion">—</p>
        </div>
        <div class="col-6">
          <p class="text-muted small mb-0">Situación</p>
          <p class="mb-0" id="info-situacion">—</p>
        </div>
        <div class="col-12">
          <p class="text-muted small mb-0">Responsable Actual</p>
          <p class="fw-semibold mb-0" id="info-responsable">—</p>
        </div>

        <div class="col-12">
          <hr class="my-1">
        </div>

        <div class="col-6">
          <p class="text-muted small mb-0">Fecha Adquisición</p>
          <p class="fw-semibold mb-0" id="info-fecha-adq">—</p>
        </div>
        <div class="col-6">
          <p class="text-muted small mb-0">Valor de Compra</p>
          <p class="fw-semibold mb-0" id="info-valor">—</p>
        </div>
        <div class="col-12">
          <p class="text-muted small mb-0">Proveedor</p>
          <p class="fw-semibold mb-0" id="info-proveedor">—</p>
        </div>
        <div class="col-6">
          <p class="text-muted small mb-0">Garantía Inicio</p>
          <p class="fw-semibold mb-0" id="info-garantia-ini">—</p>
        </div>
        <div class="col-6">
          <p class="text-muted small mb-0">Garantía Fin</p>
          <p class="fw-semibold mb-0" id="info-garantia-fin">—</p>
        </div>

        <div class="col-12">
          <hr class="my-1">
        </div>

        <div class="col-12">
          <p class="text-muted small mb-0">Descripción</p>
          <p class="mb-0" id="info-descripcion">—</p>
        </div>
        <div class="col-12">
          <p class="text-muted small mb-0">Observaciones</p>
          <p class="mb-0" id="info-observaciones">—</p>
        </div>

        {{-- Ficha Técnica TI (la rellena activos-acciones.js si la categoría la requiere) --}}
        <div class="col-12 p-0">
          <div class="row g-3 d-none" id="info-ficha-tecnica"></div>
        </div>

        <div class="col-12">
          <hr class="my-1">
        </div>

        {{-- Códigos de identificación (QR + barras) --}}
        <div class="col-12">
          <p class="text-muted small mb-2 text-uppercase fw-semibold">
            <i class="bx bx-qr-scan me-1"></i> Códigos de identificación
          </p>
          <div class="d-flex align-items-center gap-3">
            <div id="info-qr" style="width:110px;height:110px;flex:0 0 110px;"></div>
            <div class="flex-grow-1 text-center">
              <svg id="info-barcode" style="width:100%;max-width:180px;height:60px;"></svg>
              <p class="text-muted mb-0" style="font-size:.72rem;">Escanea el QR para abrir la ficha</p>
            </div>
          </div>
          <a href="#" id="info-btn-etiqueta" target="_blank" class="btn btn-label-primary btn-sm w-100 mt-3">
            <i class="bx bx-printer me-1"></i> Imprimir etiqueta
          </a>
        </div>

      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    window.activos = @json($activos);
    window.colaboradores = @json($colaboradores);
    window.routes = {
      edit: '/activos/{id}/editar',
      destroy: '/activos/{id}',
      mover: '{{ route('movimientos.store') }}',
      etiquetas: '{{ route('activos.etiquetas') }}',
      ver: '/activos/{id}/ver',
    };
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/activos/activos-table.js', 'resources/js/pages/activos/activos-acciones.js'])
@endsection
