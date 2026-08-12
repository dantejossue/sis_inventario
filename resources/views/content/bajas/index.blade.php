@extends('layouts/contentNavbarLayout')

@section('title', 'Bajas de Activos - OTI')

@section('content')

  {{--
    Módulo de bajas simplificado. La evaluación técnica ya no vive aquí (la hace
    mantenimientos). Se retiraron KPIs, panel de flujo, panel de alertas y las
    estadísticas de causales.
  --}}

  <!-- Encabezado -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <div class="mb-2">
      <h4 class="fw-bold m-0">
        <span class="text-secondary fw-bold">Ciclo de vida /</span>
        Bajas de activos
      </h4>

      <p class="text-muted mb-0">
        Registra propuestas de baja y controla su ejecución formal.
      </p>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaBaja">
        <i class="bx bx-plus me-1"></i>
        Nueva propuesta de baja
      </button>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="mb-0 fw-bold">Filtros de búsqueda</h5>
      <small class="text-muted">Filtra por causal, estado o fecha de registro.</small>
    </div>

    <div class="card-body">
      <div class="row g-3">

        <div class="col-lg-4 col-md-6">
          <label class="form-label" for="filtro-causal">Causal</label>
          <select class="form-select" id="filtro-causal">
            <option value="">Todas</option>
            <option value="DANO_IRREPARABLE">Daño irreparable</option>
            <option value="OBSOLESCENCIA">Obsolescencia</option>
            <option value="REPARACION_NO_CONVENIENTE">Reparación no conveniente</option>
            <option value="RAEE">RAEE</option>
            <option value="SUSTRACCION">Sustracción</option>
            <option value="OTRO">Otro</option>
          </select>
        </div>

        <div class="col-lg-4 col-md-6">
          <label class="form-label" for="filtro-estado">Estado</label>
          <select class="form-select" id="filtro-estado">
            <option value="">Todos</option>
            <option value="REGISTRADA">Registrada</option>
            <option value="EJECUTADA">Ejecutada</option>
            <option value="RECHAZADA">Rechazada</option>
          </select>
        </div>

        <div class="col-lg-2 col-md-6">
          <label class="form-label" for="filtro-fecha">Registradas desde</label>
          <input type="date" class="form-control" id="filtro-fecha" />
        </div>

        <div class="col-lg-2 col-md-6 d-flex align-items-end">
          <button class="btn btn-outline-secondary w-100" id="filtro-reset">
            <i class="bx bx-reset me-1"></i>
            Limpiar
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="mb-0 fw-bold">Listado de bajas</h5>
      <small class="text-muted">Propuestas de baja, su sustento y su ejecución formal.</small>
    </div>

    <div class="card-body pt-6">
      <table class="table table-hover table-disposal small" id="miTablaBajas">
        <thead>
          <tr>
            <th class="fw-bold">Código</th>
            <th class="fw-bold">Activo</th>
            <th class="fw-bold">Causal</th>
            <th class="fw-bold">Sustento</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Fecha</th>
            <th class="fw-bold text-end">Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0"></tbody>
      </table>
    </div>
  </div>


  {{-- Modal reutilizable de nueva propuesta de baja --}}
  @include('content.bajas.partials.modal-nueva-baja', ['bajasActivos' => $activos])


  <!-- Modal detalle baja -->
  <div class="modal fade" id="modalDetalleBaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">

        <div class="modal-header py-4 border-bottom border-sw">
          <div>
            <h5 class="modal-title d-flex align-items-center fw-bold" style="color:#084b8a;">
              <i class="bx bxs-down-arrow-circle me-1"></i>
              Detalle de la baja:&nbsp; <span id="det-codigo" style="color: brown;"></span>
            </h5>
            <small class="text-muted">Sustento, origen de mantenimiento y documentos.</small>
          </div>

          <button type="button" class="btn-close position-absolute top-0 end-0 mt-3 me-3"
            data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body overflow-auto">

          <div class="row">

            <div class="col-lg-8">

              {{-- <div class="maintenance-detail-header row g-3 mb-4">
                <div>
                  <span class="badge mb-2" id="det-causal"></span>
                  <h4 class="mb-1" id="det-titulo"></h4>
                  <p class="text-muted mb-0" id="det-subtitulo"></p>
                </div>

                <div class="disposal-detail-status">
                  <span>Estado</span>
                  <strong id="det-estado-texto"></strong>
                </div>
                <div class="col-12 col-md-4">
                  <div class="maintenance-detail-priority">
                    <span>Tipo</span>
                    <strong id="det-causal"></strong>
                  </div>
                </div>

                <div class="col-12 col-md-4">
                  <div class="maintenance-detail-priority">
                    <span>Registrado el</span>
                    <strong id="det-subtitulo"></strong>
                  </div>
                </div>

                <div class="col-12 col-md-4">
                  <div class="maintenance-detail-priority">
                    <span>Reportado el</span>
                    <strong id="det-subtitulo"></strong>
                  </div>
                </div>
              </div> --}}

              <!-- Activo -->
              <div class="card border rounded-3 mb-4">
                <div class="card-header">
                  <div class="d-flex align-items-center">
                    <i class="bx bx-laptop me-1 fw-bold"></i>
                    <h6 class="mb-0 fw-bold">
                      Activo vinculado
                    </h6>
                  </div>
                </div>

                <div class="card-body">
                  <div class="disposal-linked-asset">
                    <div class="disposal-linked-icon bg-label-danger">
                      <i id="icon-category"></i>
                    </div>

                    <div class="flex-grow-1">
                      <h6 class="mb-1" id="det-activo-modelo"></h6>
                      <small class="text-muted d-block" id="det-activo-codigo"></small>
                      <small class="text-muted d-block" id="det-activo-patrimonial"></small>
                      <small class="text-muted d-block" id="det-activo-situacion"></small>
                    </div>

                    <a href="#" id="det-activo-url" class="btn btn-sm btn-outline-primary">Ver ficha</a>
                  </div>
                </div>
              </div>

              <!-- Sustento -->
              <div class="card border rounded-3 mb-4">
                <div class="card-header">
                  <div class="d-flex align-items-center">
                    <i class="bx bx-search-alt me-1 fw-bold"></i>
                    <h6 class="mb-0 fw-bold">
                      Sustento de la baja
                    </h6>
                  </div>
                </div>

                <div class="card-body">
                  <div class="maintenance-diagnosis-box mb-3">
                    <span>Motivo de la propuesta</span>
                    <p id="det-motivo"></p>
                  </div>

                  <div class="maintenance-diagnosis-box mb-3">
                    <span>Observaciones</span>
                    <p id="det-observaciones"></p>
                  </div>
                  {{-- <div class="disposal-diagnosis-box mb-3">
                    <span class="">Motivo de la propuesta</span>
                    <p id="det-motivo"></p>
                  </div>

                  <div class="disposal-diagnosis-box mb-3">
                    <span>Observaciones</span>
                    <p id="det-observaciones"></p>
                  </div> --}}

                  {{-- <div class="disposal-diagnosis-box mb-3 d-none" id="det-box-rechazo">
                    <span>Motivo de rechazo</span>
                    <p id="det-motivo-rechazo"></p>
                  </div> --}}
                  <div class="maintenance-diagnosis-box mb-3 d-none" id="det-box-rechazo">
                    <span>Motivo de rechazo</span>
                    <p id="det-motivo-rechazo"></p>
                  </div>
                </div>
              </div>

              <!-- Origen de mantenimiento -->
              <div class="card border rounded-3 mb-4 d-none" id="det-card-mant">
                <div class="card-header">
                  <div class="d-flex align-items-center">
                    <i class="bx bx-wrench me-1 fw-bold"></i>
                    <h6 class="mb-0 fw-bold">
                      Mantenimiento de origen
                    </h6>
                  </div>
                </div>

                <div class="card-body">
                  <div class="maintenance-diagnosis-box mb-3">
                    <span>Diagnóstico del mantenimiento</span>
                    <p id="det-diag-mant"></p>
                  </div>

                  <div class="maintenance-diagnosis-box mb-3">
                    <span>Resultado del mantenimiento</span>
                    <p id="det-result-mant"></p>
                  </div>
                  {{-- <div class="disposal-diagnosis-box mb-3">
                    <span>Diagnóstico del mantenimiento</span>
                    <p id="det-diag-mant"></p>
                  </div>

                  <div class="disposal-diagnosis-box">
                    <span>Resultado del mantenimiento</span>
                    <p id="det-result-mant"></p>
                  </div> --}}
                </div>
              </div>

              <!-- Documentos -->
              <div class="card border rounded-3">
                <div class="card-header">
                  <div class="d-flex align-items-center">
                    <i class="bx bx-paperclip me-1 fw-bold"></i>
                    <h6 class="mb-0 fw-bold">
                      Documentos
                    </h6>
                  </div>
                </div>

                <div class="card-body">
                  <div class="row g-3" id="det-documentos"></div>

                  {{-- Solo se puede adjuntar mientras la baja está REGISTRADA --}}
                  <div id="det-doc-form-wrap">
                    <hr class="my-3">

                    <form action="{{ route('documentos.store') }}" method="POST" enctype="multipart/form-data"
                      class="row g-2 align-items-end">
                      @csrf
                      <input type="hidden" name="entidad_tipo" value="BAJA">
                      <input type="hidden" name="entidad_id" value="" id="doc-entidad-id">
                      <input type="hidden" name="tipo_documento" value="SUSTENTO">

                      <div class="col-md-7">
                        <label class="form-label" for="doc-archivo">Adjuntar sustento (informe, acta, foto · máx. 5
                          MB)</label>
                        <input type="file" class="form-control" id="doc-archivo" name="archivo" required
                          accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" />
                      </div>

                      <div class="col-md-3">
                        <label class="form-label" for="doc-descripcion">Descripción</label>
                        <input type="text" class="form-control" id="doc-descripcion" name="descripcion"
                          maxlength="255" placeholder="Opcional" />
                      </div>

                      <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                          <i class="bx bx-upload me-1"></i> Subir
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

            </div>

            <!-- Lateral -->
            <div class="col-lg-4">

              <div class="disposal-side-card mb-4">
                <div class="d-flex align-items-center">
                  <i class="bx bx-info-circle me-1 fw-bold"></i>
                  <h6 class="mb-0 fw-bold">
                    Proceso
                  </h6>
                </div>

                <div class="data-list">
                  <div class="data-list-item">
                    <span>Causal</span>
                    <span class="badge" id="det-causal"></span>
                  </div>

                  <div class="data-list-item">
                    <span>Estado</span>
                    <span class="badge" id="det-estado"></span>
                  </div>

                  <div class="data-list-item">
                    <span>Origen</span>
                    <strong id="det-origen"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Registrado por</span>
                    <strong id="det-registrado"></strong>
                  </div>

                  <div class="data-list-item">
                    <span id="det-final-label">Ejecutado / rechazado por</span>
                    <strong id="det-final-por"></strong>
                  </div>
                </div>
              </div>

              <div class="disposal-side-card mb-4">
                <div class="d-flex align-items-center">
                  <i class="bx bx-calendar me-1 fw-bold"></i>
                  <h6 class="mb-0 fw-bold">
                    Fechas
                  </h6>
                </div>

                <div class="data-list">
                  <div class="data-list-item">
                    <span>Registro</span>
                    <strong id="det-fecha-registro"></strong>
                  </div>

                  <div class="data-list-item">
                    <span id="det-fecha-final-label">Ejecución / rechazo</span>
                    <strong id="det-fecha-final"></strong>
                  </div>
                </div>
              </div>

              <div class="disposal-side-card">
                <div class="d-flex align-items-center mb-3">
                  <i class="bx bx-cog me-1 fw-bold"></i>
                  <h6 class="mb-0 fw-bold">
                    Acciones rápidas
                  </h6>
                </div>
                <div id="det-acciones" class="d-grid gap-2"></div>
              </div>

            </div>

          </div>

        </div>

        <div class="modal-footer border-top py-4">
          <button class="btn btn-label-secondary fw-bold" data-bs-dismiss="modal">Cerrar</button>
        </div>

      </div>
    </div>
  </div>
  <!-- / Modal detalle baja -->


  <!-- Modal ejecutar baja -->
  <div class="modal fade" id="modalEjecutar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <div>
            <h5 class="modal-title d-flex align-items-center fw-bold" style="color:#084b8a;">
              <i class="bx bxs-check-circle me-1"></i>
              Ejecutar baja:&nbsp; <span id="eje-codigo" style="color: brown;"></span>
            </h5>
            <small class="text-muted">
              Requiere el documento formal de baja. El activo pasará a DADO DE BAJA (acción irreversible).
            </small>
          </div>

          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="form-ejecutar-baja" enctype="multipart/form-data">
          <div class="modal-body">

            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label" for="eje-fecha">
                  Fecha de ejecución
                  <span class="text-danger">*</span>
                </label>
                <input type="date" class="form-control" id="eje-fecha" name="fecha_ejecucion"
                  value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="eje-tipo">
                  Tipo de documento formal
                  <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="eje-tipo" name="tipo_documento" required>
                  <option value="ACTA_BAJA">Acta de baja</option>
                  <option value="RESOLUCION">Resolución</option>
                  <option value="DOCUMENTO_PATRIMONIAL">Documento patrimonial</option>
                  <option value="INFORME_FINAL">Informe final</option>
                  <option value="OTRO">Otro</option>
                </select>
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="eje-numero">
                  N° de documento
                  <span class="text-muted">(opcional)</span>
                </label>
                <input type="text" class="form-control" id="eje-numero" name="numero_documento" maxlength="100"
                  placeholder="Ej. RES-OTI-2026-014">
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="eje-documento">
                  Documento formal
                  <span class="text-danger">*</span>
                  <span class="text-muted">(máx. 5 MB)</span>
                </label>
                <input type="file" class="form-control" id="eje-documento" name="documento" required
                  accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx">
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-12">
                <label class="form-label" for="eje-observaciones">
                  Observaciones
                  <span class="text-muted">(opcional)</span>
                </label>
                <textarea class="form-control" id="eje-observaciones" name="observaciones" rows="2" maxlength="1000"
                  placeholder="Notas de la ejecución..."></textarea>
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-12">
                <div class="alert alert-danger mb-0 rounded-3">
                  <div class="d-flex align-items-center">
                    <i class="bx bxs-error-circle fs-3 me-2"></i>
                    <p class="m-0 p-0">
                      El activo quedará <strong>DADO DE BAJA</strong>, sin responsable y sin movimientos ni
                      mantenimientos posibles. Esta acción no se puede deshacer.
                    </p>
                  </div>
                </div>
              </div>

            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary fw-bold" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger fw-bold" id="btn-ejecutar-baja">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              <i class="bx bx-check me-1 fw-bold"></i>
              Ejecutar baja
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
  <!-- / Modal ejecutar baja -->

@endsection

@section('page-script')
  <script>
    window.bajas = @json($bajas);
    window.mantsBaja = @json($mantsBaja);
    window.routesBajas = {
      store: @json(route('bajas.store')),
      ejecutar: @json(url('/bajas')) + '/{id}/ejecutar',
      rechazar: @json(url('/bajas')) + '/{id}/rechazar'
    };
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/bajas/bajas.js'])
@endsection
