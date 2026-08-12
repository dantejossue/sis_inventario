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


    /* Modal de mover: pegado arriba; si el contenido excede la pantalla, hace
                                                                                                                                             scroll el overlay completo (comportamiento estándar; el modal-dialog-scrollable
                                                                                                                                             de este build de Sneat dejaba parte del modal fuera de pantalla sin scroll). */
    #modalMover.modal {
      overflow-y: auto !important;
    }

    #modalMover .modal-dialog {
      margin: 1.5rem auto !important;
      align-items: flex-start !important;
    }
  </style>

  <h4 class="fw-bold mb-1 lh-1">
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

  @php
    $acts = collect($activos);
    $kTotal = $acts->count();
    $kDisponible = $acts->where('situacion_actual', 'DISPONIBLE')->count();
    $kEnUso = $acts->where('situacion_actual', 'EN_USO')->count();
    $kPrestamo = $acts->where('situacion_actual', 'EN_PRESTAMO')->count();
    $kMantenimiento = $acts->where('situacion_actual', 'EN_MANTENIMIENTO')->count();
    $kObservado = $acts->where('situacion_actual', 'OBSERVADO')->count();
    $kBaja = $acts->where('situacion_actual', 'DADO_DE_BAJA')->count();
    $kpiCards = [
        [
            'Total activos',
            $kTotal,
            'white',
            'primary',
            'bxs-devices',
            $kDisponible . ' disponibles',
            'text-primary',
            'todas',
        ],
        ['En uso', $kEnUso, 'white', 'success', 'bxs-user-check', 'Asignados a responsable', 'text-success', 'EN_USO'],
        [
            'En préstamo',
            $kPrestamo,
            'white',
            'warning',
            'bxs-time-five',
            $kMantenimiento . ' en mantenimiento',
            'text-warning',
            'EN_PRESTAMO',
        ],
        [
            'Observados',
            $kObservado,
            'white',
            'danger',
            'bxs-error-circle',
            $kBaja . ' dados de baja',
            'text-danger',
            'OBSERVADO',
        ],
    ];
  @endphp

  <!-- KPIs rápidos (clic para filtrar por situación) -->
  <div class="row g-4">
    @foreach ($kpiCards as [$titulo, $valor, $fondo, $color, $icono, $sub, $subClass, $filtro])
      <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="card maintenance-kpi-card rounded-3 kpi-card kpi-filtro" role="button"
          data-situacion="{{ $filtro }}" title="Filtrar por {{ $titulo }}">
          <div class="card-body bg-{{ $fondo }} bg-gradient rounded-5">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <span class="fw-bold d-block mb-1">{{ $titulo }}</span>
                <h3 class="card-title mb-2 fw-bold">{{ $valor }}</h3>
                <small class="{{ $subClass }} fw-semibold">{{ $sub }}</small>
              </div>
              <div class="rounded-5 bg-label-{{ $color }}  p-2 d-flex align-items-center justify-content-center"
                style="width:55px; height:55px;">
                <i class="bx {{ $icono }} fs-2 bg-{{ $color }}"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <!-- Filtros -->
  <div class="card rounded-3 mb-4">
    <div class="card-header">
      <h5 class="mb-0 fw-bold">Filtros de búsqueda</h5>
      <small class="text-muted">
        Filtra por sede, categoría, estado, responsable o información patrimonial.
      </small>
    </div>

    <div class="card-body">
      @php
        $catsFiltro = $acts->pluck('categoria_nombre')->filter(fn($c) => $c && $c !== '—')->unique()->sort()->values();
        $sedesFiltro = $acts->pluck('sede_nombre')->filter(fn($s) => $s && $s !== '—')->unique()->sort()->values();
        $respsFiltro = $acts
            ->filter(fn($a) => $a['id_responsable_actual'] && $a['responsable_nombre'])
            ->map(fn($a) => ['id' => $a['id_responsable_actual'], 'nombre' => $a['responsable_nombre']])
            ->unique('id')
            ->sortBy('nombre')
            ->values();
      @endphp

      <div class="row g-3">

        <div class="col-lg-3 col-md-6">
          <label class="form-label">Responsable</label>
          <select class="form-select" id="filtro-responsable" data-placeholder="Todos">
            <option value=""></option>
            @foreach ($respsFiltro as $r)
              <option value="{{ $r['id'] }}">{{ $r['nombre'] }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-2 col-md-6">
          <label class="form-label">Categoría</label>
          <select class="form-select" id="filtro-categoria">
            <option value="">Todas</option>
            @foreach ($catsFiltro as $c)
              <option value="{{ $c }}">{{ $c }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-2 col-md-6">
          <label class="form-label">Sede</label>
          <select class="form-select" id="filtro-sede">
            <option value="">Todas</option>
            @foreach ($sedesFiltro as $s)
              <option value="{{ $s }}">{{ $s }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-2 col-md-6">
          <label class="form-label">Situación</label>
          <select class="form-select" id="filtro-situacion">
            <option value="">Todas</option>
            @foreach (\App\Models\Activo::SITUACION_LABELS as $code => $label)
              <option value="{{ $code }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-2 col-md-6">
          <label class="form-label">Condición</label>
          <select class="form-select" id="filtro-condicion">
            <option value="">Todas</option>
            @foreach (\App\Models\Activo::CONDICION_LABELS as $code => $label)
              <option value="{{ $code }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-1 col-md-6 d-flex align-items-end">
          <button class="btn btn-outline-secondary w-100" id="filtro-reset" title="Limpiar filtros">
            <i class="bx bx-reset"></i>
          </button>
        </div>

      </div>
    </div>
  </div>

  <div class="card rounded-3">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0 fw-bold">Inventario de Activos</h5>
      <div class="d-flex gap-2">
        <a href="{{ route('activos.importar.plantilla') }}" class="btn btn-label-secondary">
          <i class="bx bx-download me-1"></i> Descargar plantilla
        </a>
        <button type="button" class="btn btn-label-success" data-bs-toggle="modal" data-bs-target="#modalImportarExcel">
          <i class="bx bx-upload me-1"></i> Importar Excel
        </button>
        <a href="{{ route('activos.create') }}" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i> Nuevo Activo
        </a>
      </div>
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
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-4">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-transfer me-2"></i> Registrar Movimiento
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formMover">
          @csrf
          <input type="hidden" id="mover-activo-ids" name="activo_ids_json">
          <div class="modal-body py-4">

            {{-- Lista de activos a mover --}}
            <div class="mb-3">
              <p class="fw-semibold mb-2 text-muted small text-uppercase">Activos seleccionados:</p>
              <div id="mover-lista-activos" class="d-flex flex-wrap gap-2"></div>
            </div>

            {{-- Formulario vertical (campos apilados) --}}
            <div class="d-flex flex-column gap-3">

              <div>
                <label class="form-label">Tipo de Movimiento <span class="text-danger">*</span></label>
                <select class="form-select" id="mover-tipo" name="tipo">
                  <option value="">Seleccionar tipo...</option>
                  <option value="PRESTAMO">Préstamo</option>
                  <option value="TRANSFERENCIA">Transferencia</option>
                  <option value="REGULARIZACION">Regularización</option>
                </select>
                <div class="invalid-feedback"></div>
                <small class="text-muted" id="mover-tipo-ayuda"></small>
              </div>

              {{-- Colaborador actual (deshabilitado) + destino (Select2) --}}
              <div class="d-none" id="mover-colaborador-wrap">
                <label class="form-label">Colaborador actual</label>
                <input type="text" class="form-control bg-label-secondary mb-3" id="mover-colaborador-actual"
                  disabled placeholder="—">

                <label class="form-label">Colaborador destino <span class="text-danger">*</span></label>
                <select class="form-select" id="mover-colaborador" name="id_colaborador_destino"></select>
                <div class="invalid-feedback"></div>
              </div>

              <div class="d-none" id="mover-ubicacion-wrap">
                <label class="form-label">Ubicación destino</label>
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
                <div class="invalid-feedback"></div>
              </div>

              <div class="d-none" id="mover-devolucion-wrap">
                <label class="form-label">Fecha estimada de devolución <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="mover-devolucion" name="fecha_devolucion_estimada">
                <div class="invalid-feedback"></div>
              </div>

              {{-- Solo REGULARIZACION: corrección de condición / situación --}}
              <div class="d-none" id="mover-condicion-wrap">
                <label class="form-label">Condición (regularizar)</label>
                <select class="form-select" id="mover-condicion" name="condicion_actual">
                  <option value="">Sin cambio de condición</option>
                  @foreach (\App\Models\Activo::CONDICION_LABELS as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                  @endforeach
                </select>
                <div class="invalid-feedback"></div>
              </div>

              <div class="d-none" id="mover-situacion-wrap">
                <label class="form-label">Situación (regularizar)</label>
                <select class="form-select" id="mover-situacion" name="situacion_actual">
                  <option value="">Sin cambio de situación</option>
                  @foreach (\App\Models\Activo::SITUACION_LABELS as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                  @endforeach
                </select>
                <div class="invalid-feedback"></div>
              </div>

              <div>
                <label class="form-label">Motivo / Observaciones</label>
                <textarea class="form-control" id="mover-motivo" name="motivo" rows="2"
                  placeholder="Motivo u observaciones"></textarea>
                <div class="invalid-feedback"></div>
              </div>

              {{-- Documento de sustento OBLIGATORIO --}}
              <div>
                <label class="form-label" for="mover-tipo-doc">Tipo de documento</label>
                <select class="form-select" id="mover-tipo-doc" name="tipo_documento">
                  <option value="ACTA_ENTREGA">Acta de entrega</option>
                  <option value="ACTA_CONFORMIDAD">Acta de conformidad</option>
                  <option value="ACTA_RETORNO">Acta de conformidad de retorno</option>
                  <option value="OFICIO">Oficio / memorando</option>
                  <option value="OTRO">Otro</option>
                </select>
              </div>

              <div>
                <label class="form-label" for="mover-documento">
                  Documento de sustento <span class="text-danger">*</span>
                </label>
                <input type="file" class="form-control" id="mover-documento" name="documento" required
                  accept=".pdf,.jpg,.jpeg,.png,.webp,.xls,.xlsx,.doc,.docx,.zip,.rar">
                <div class="invalid-feedback"></div>
                <small class="text-muted">Obligatorio. Acta de entrega/conformidad que respalda el movimiento.</small>
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
       MODAL — IMPORTAR ACTIVOS DESDE EXCEL
  ════════════════════════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalImportarExcel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header border-bottom py-4">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <i class="bx bx-upload me-2"></i> Importar Activos desde Excel
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="formImportarExcel">
          @csrf
          <div class="modal-body py-4">
            <div class="alert alert-info d-flex align-items-start gap-2">
              <i class="bx bx-info-circle fs-5"></i>
              <div>
                Usa la plantilla oficial para llenar los datos.
                <a href="{{ route('activos.importar.plantilla') }}" class="fw-semibold">Descárgala aquí</a>
                si aún no la tienes. Cada fila se procesa por separado: las filas con errores se
                reportan al final sin bloquear a las demás.
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="importar-archivo">Archivo Excel (.xlsx, .xls) <span
                  class="text-danger">*</span></label>
              <input type="file" class="form-control" id="importar-archivo" name="archivo" required
                accept=".xlsx,.xls">
              <div class="invalid-feedback"></div>
              <small class="text-muted">Tamaño máximo 5 MB.</small>
            </div>

            {{-- Resultado tras procesar el archivo --}}
            <div id="importar-resultado" class="d-none">
              <hr>
              <div id="importar-resumen" class="mb-3"></div>
              <div id="importar-errores"></div>
            </div>
          </div>

          <div class="modal-footer border-top py-4">
            <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-success" id="btnConfirmarImportar">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              <i class="bx bx-upload me-1"></i> Importar
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
      importar: '{{ route('activos.importar') }}',
      exportarExcel: '{{ route('activos.exportar.excel') }}',
    };
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/activos/activos-table.js', 'resources/js/pages/activos/activos-acciones.js', 'resources/js/pages/activos/activos-importar.js'])
@endsection
