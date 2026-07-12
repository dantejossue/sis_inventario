@extends('layouts/contentNavbarLayout')

@section('title', 'Bajas de Activos - OTI')

@section('content')

  <!-- Encabezado -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">
        <span class="text-muted fw-light">Ciclo de vida /</span>
        Bajas de activos
      </h4>

      <p class="text-muted mb-0">
        Recomendación técnica de baja OTI: registro, evaluación, recomendación, validación y ejecución.
      </p>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaBaja">
        <i class="bx bx-plus me-1"></i>
        Nueva propuesta de baja
      </button>
    </div>
  </div>

  <!-- KPIs -->
  <div class="row">

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card disposal-kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Propuestas abiertas</span>
              <h3 class="mb-2" id="kpi-abiertas">0</h3>
              <small class="text-warning fw-semibold">
                <i class="bx bx-time"></i>
                <span id="kpi-abiertas-detalle">0 por evaluar</span>
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="bx bx-file"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card disposal-kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">RAEE</span>
              <h3 class="mb-2" id="kpi-raee">0</h3>
              <small class="text-danger fw-semibold">
                Gestión ambiental
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="bx bx-recycle"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card disposal-kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Ejecutadas</span>
              <h3 class="mb-2" id="kpi-ejecutadas">0</h3>
              <small class="text-success fw-semibold">
                <i class="bx bx-check-circle"></i>
                <span id="kpi-ejecutadas-detalle">activos dados de baja</span>
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="bx bx-check-shield"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card disposal-kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Valor referencial</span>
              <h3 class="mb-2" id="kpi-valor">S/ 0</h3>
              <small class="text-muted fw-semibold">
                Activos propuestos
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-money"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Panel superior -->
  <div class="row mb-4">

    <!-- Flujo de baja -->
    <div class="col-xl-8 col-lg-7 mb-4 mb-lg-0">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0">Flujo de baja de activos</h5>
            <small class="text-muted">
              Desde la propuesta técnica hasta la aprobación y ejecución patrimonial.
            </small>
          </div>

          <span class="badge bg-label-primary">OTI / Patrimonio</span>
        </div>

        <div class="card-body">

          <div class="disposal-flow">

            <div class="disposal-flow-step">
              <div class="disposal-flow-icon bg-label-warning">
                <i class="bx bx-error-circle"></i>
              </div>
              <div>
                <h6>Registro</h6>
                <small>Motivo y causal</small>
              </div>
            </div>

            <div class="disposal-flow-line"></div>

            <div class="disposal-flow-step">
              <div class="disposal-flow-icon bg-label-info">
                <i class="bx bx-search-alt"></i>
              </div>
              <div>
                <h6>Evaluación</h6>
                <small>Informe técnico OTI</small>
              </div>
            </div>

            <div class="disposal-flow-line"></div>

            <div class="disposal-flow-step">
              <div class="disposal-flow-icon bg-label-primary">
                <i class="bx bx-check-shield"></i>
              </div>
              <div>
                <h6>Recomendación</h6>
                <small>Clasificación final</small>
              </div>
            </div>

            <div class="disposal-flow-line"></div>

            <div class="disposal-flow-step">
              <div class="disposal-flow-icon bg-label-success">
                <i class="bx bx-check"></i>
              </div>
              <div>
                <h6>Validación y baja</h6>
                <small>Validación OTI + ejecución</small>
              </div>
            </div>

          </div>

          <hr class="my-4">

          <div class="row text-center">

            <div class="col-md-3 col-6 mb-3">
              <h4 class="mb-1" id="stat-obsoletos">0</h4>
              <small class="text-muted">Obsolescencia</small>
            </div>

            <div class="col-md-3 col-6 mb-3">
              <h4 class="mb-1" id="stat-irreparables">0</h4>
              <small class="text-muted">Daño / sin reparación</small>
            </div>

            <div class="col-md-3 col-6 mb-3">
              <h4 class="mb-1" id="stat-raee">0</h4>
              <small class="text-muted">RAEE</small>
            </div>

            <div class="col-md-3 col-6 mb-3">
              <h4 class="mb-1" id="stat-faltantes">0</h4>
              <small class="text-muted">Sustracción</small>
            </div>

          </div>

        </div>
      </div>
    </div>

    <!-- Alertas -->
    <div class="col-xl-4 col-lg-5">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Alertas de baja</h5>
          <small class="text-muted">
            Casos que necesitan atención prioritaria.
          </small>
        </div>

        <div class="card-body" id="alertas-bajas">
          <p class="text-muted mb-0">Sin alertas pendientes.</p>
        </div>
      </div>
    </div>

  </div>

  <!-- Filtros -->
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="mb-0">Filtros de búsqueda</h5>
      <small class="text-muted">
        Filtra por causal, estado del proceso o fecha de registro.
      </small>
    </div>

    <div class="card-body">
      <div class="row g-3">

        <div class="col-lg-4 col-md-6">
          <label class="form-label" for="filtro-causal">Causal</label>
          <select class="form-select" id="filtro-causal">
            <option value="">Todas</option>
            <option value="DANO">Daño</option>
            <option value="OBSOLESCENCIA_TECNICA">Obsolescencia técnica</option>
            <option value="MANTENIMIENTO_ONEROSO">Mantenimiento oneroso</option>
            <option value="SIN_REPARACION">Sin reparación</option>
            <option value="RAEE">RAEE</option>
            <option value="SUSTRACCION">Sustracción</option>
            <option value="OTRO">Otro</option>
          </select>
        </div>

        <div class="col-lg-4 col-md-6">
          <label class="form-label" for="filtro-estado">Estado</label>
          <select class="form-select" id="filtro-estado">
            <option value="">Todos</option>
            <option value="ABIERTAS">— En curso —</option>
            <option value="REGISTRADA">Registrada</option>
            <option value="EN_EVALUACION">En evaluación</option>
            <option value="RECOMENDADA">Recomendada</option>
            <option value="VALIDADA">Validada</option>
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
      <h5 class="mb-0">Listado de bajas</h5>
      <small class="text-muted">
        Registro, evaluación técnica, recomendación, validación y ejecución.
      </small>
    </div>

    <div class="card-body pt-6">
      <table class="table table-hover table-disposal small" id="miTablaBajas">
        <thead>
          <tr>
            <th class="fw-bold">Código</th>
            <th class="fw-bold">Activo</th>
            <th class="fw-bold">Causal</th>
            <th class="fw-bold">Origen</th>
            <th class="fw-bold">Informe técnico</th>
            <th class="fw-bold">Clasificación</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Fecha</th>
            <th class="fw-bold text-end">Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0"></tbody>
      </table>
    </div>
  </div>


  <!-- Modal nueva baja -->
  <div class="modal fade" id="modalNuevaBaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">

        <div class="modal-header">
          <div>
            <h5 class="modal-title">
              <i class="bx bx-down-arrow-circle me-1"></i>
              Nueva propuesta de baja
            </h5>
            <small class="text-muted">
              El activo queda OBSERVADO al recomendarse la baja; solo pasa a DADO DE BAJA al ejecutarse (o se rechaza).
            </small>
          </div>

          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="form-nueva-baja">
          <div class="modal-body">

            <div class="row g-4">

              <div class="col-lg-8">
                <div class="row g-3">

                  <div class="col-md-6">
                    <label class="form-label" for="baja-activo">Activo</label>
                    <select class="form-select" id="baja-activo" name="id_activo" required>
                      <option value="">Seleccione activo…</option>
                      @foreach ($activos as $a)
                        <option value="{{ $a['id_activo'] }}" data-valor="{{ $a['valor_compra'] }}">
                          {{ $a['codigo_interno'] }} — {{ $a['modelo'] ?: 'Sin modelo' }}
                        </option>
                      @endforeach
                    </select>
                    <small class="text-muted">
                      Solo activos sin propuesta de baja en curso.
                    </small>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="baja-causal">Causal de baja</label>
                    <select class="form-select" id="baja-causal" name="causal_baja" required>
                      <option value="">Seleccione…</option>
                      <option value="DANO">Daño</option>
                      <option value="OBSOLESCENCIA_TECNICA">Obsolescencia técnica</option>
                      <option value="MANTENIMIENTO_ONEROSO">Mantenimiento oneroso</option>
                      <option value="SIN_REPARACION">Sin reparación</option>
                      <option value="RAEE">RAEE</option>
                      <option value="SUSTRACCION">Sustracción</option>
                      <option value="OTRO">Otro</option>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="baja-mantenimiento">Mantenimiento de origen (opcional)</label>
                    <select class="form-select" id="baja-mantenimiento" name="id_mantenimiento_origen">
                      <option value="">Sin mantenimiento vinculado</option>
                    </select>
                    <small class="text-muted">
                      Se listan los mantenimientos del activo que recomendaron baja.
                    </small>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="baja-fecha">Fecha de registro</label>
                    <input type="date" class="form-control" id="baja-fecha" name="fecha_registro"
                      value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required />
                  </div>

                  <div class="col-md-12">
                    <label class="form-label" for="baja-motivo">Motivo / sustento de la baja</label>
                    <textarea class="form-control" id="baja-motivo" name="motivo" rows="3" required maxlength="2000"
                      placeholder="Describe el sustento de la propuesta de baja..."></textarea>
                  </div>

                  <div class="col-md-12">
                    <label class="form-label" for="baja-diagnostico">Informe / diagnóstico técnico (opcional)</label>
                    <textarea class="form-control" id="baja-diagnostico" name="diagnostico_tecnico" rows="2"
                      maxlength="2000"
                      placeholder="Diagnóstico técnico que sustenta la baja (obligatorio antes de recomendar)..."></textarea>
                  </div>

                  <div class="col-md-12">
                    <label class="form-label" for="baja-observaciones">Observaciones (opcional)</label>
                    <textarea class="form-control" id="baja-observaciones" name="observaciones" rows="2"
                      maxlength="1000" placeholder="Notas adicionales..."></textarea>
                  </div>

                </div>
              </div>

              <!-- Resumen lateral -->
              <div class="col-lg-4">
                <div class="disposal-create-summary">
                  <div class="disposal-create-icon bg-label-danger">
                    <i class="bx bx-down-arrow-circle"></i>
                  </div>

                  <h5 class="mb-1">Resumen de la propuesta</h5>
                  <p class="text-muted mb-3">
                    La baja quedará vinculada al activo, su historial y sus documentos.
                  </p>

                  <div class="data-list">
                    <div class="data-list-item">
                      <span>Activo</span>
                      <strong id="resumen-activo">No seleccionado</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Causal</span>
                      <strong id="resumen-causal">No seleccionada</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Valor referencial</span>
                      <strong id="resumen-valor">—</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Estado inicial</span>
                      <span class="badge bg-label-warning">Registrada</span>
                    </div>
                  </div>

                  <div class="alert alert-warning mt-3 mb-0">
                    <i class="bx bx-info-circle me-1"></i>
                    Ningún activo pasa a DADO DE BAJA sin evaluación técnica,
                    recomendación y validación interna OTI.
                  </div>
                </div>
              </div>

            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              Cancelar
            </button>

            <button type="submit" class="btn btn-primary">
              <i class="bx bx-check me-1"></i>
              Registrar propuesta
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
  <!-- / Modal nueva baja -->


  <!-- Modal detalle baja -->
  <div class="modal fade" id="modalDetalleBaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">

        <div class="modal-header">
          <div>
            <h5 class="modal-title">
              <i class="bx bx-down-arrow-circle me-1"></i>
              Detalle de la baja <span id="det-codigo"></span>
            </h5>
            <small class="text-muted">
              Sustento, evaluación técnica, recomendación, validación y documentos.
            </small>
          </div>

          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="row">

            <div class="col-lg-8">

              <div class="disposal-detail-header mb-4">
                <div>
                  <span class="badge mb-2" id="det-causal"></span>
                  <h4 class="mb-1" id="det-titulo"></h4>
                  <p class="text-muted mb-0" id="det-subtitulo"></p>
                </div>

                <div class="disposal-detail-status">
                  <span>Estado</span>
                  <strong id="det-estado-texto"></strong>
                </div>
              </div>

              <!-- Activo -->
              <div class="card border mb-4">
                <div class="card-header">
                  <h6 class="mb-0">
                    <i class="bx bx-laptop me-1"></i>
                    Activo vinculado
                  </h6>
                </div>

                <div class="card-body">
                  <div class="disposal-linked-asset">
                    <div class="disposal-linked-icon bg-label-danger">
                      <i class="bx bx-devices"></i>
                    </div>

                    <div class="flex-grow-1">
                      <h6 class="mb-1" id="det-activo-modelo"></h6>
                      <small class="text-muted d-block" id="det-activo-codigo"></small>
                      <small class="text-muted d-block" id="det-activo-patrimonial"></small>
                      <small class="text-muted d-block" id="det-activo-valor"></small>
                    </div>

                    <a href="#" id="det-activo-url" class="btn btn-sm btn-outline-primary">
                      Ver ficha
                    </a>
                  </div>
                </div>
              </div>

              <!-- Sustento -->
              <div class="card border mb-4">
                <div class="card-header">
                  <h6 class="mb-0">
                    <i class="bx bx-search-alt me-1"></i>
                    Sustento y evaluación
                  </h6>
                </div>

                <div class="card-body">
                  <div class="disposal-diagnosis-box mb-3">
                    <span>Motivo de la propuesta</span>
                    <p id="det-motivo"></p>
                  </div>

                  <div class="disposal-diagnosis-box mb-3">
                    <span>Informe / diagnóstico técnico</span>
                    <p id="det-diagnostico"></p>
                  </div>

                  <div class="disposal-diagnosis-box">
                    <span>Observaciones</span>
                    <p id="det-observaciones"></p>
                  </div>
                </div>
              </div>

              <!-- Documentos -->
              <div class="card border">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h6 class="mb-0">
                    <i class="bx bx-paperclip me-1"></i>
                    Documentos de sustento
                  </h6>
                </div>

                <div class="card-body">
                  <div class="row g-3" id="det-documentos"></div>

                  <hr class="my-3">

                  <form action="{{ route('documentos.store') }}" method="POST" enctype="multipart/form-data"
                    class="row g-2 align-items-end">
                    @csrf
                    <input type="hidden" name="entidad_tipo" value="BAJA">
                    <input type="hidden" name="entidad_id" value="" id="doc-entidad-id">
                    <input type="hidden" name="tipo_documento" value="Sustento de baja">

                    <div class="col-md-7">
                      <label class="form-label" for="doc-archivo">
                        Adjuntar sustento (informe, acta, foto · máx. 5 MB)
                      </label>
                      <input type="file" class="form-control" id="doc-archivo" name="archivo" required
                        accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" />
                    </div>

                    <div class="col-md-3">
                      <label class="form-label" for="doc-descripcion">Descripción</label>
                      <input type="text" class="form-control" id="doc-descripcion" name="descripcion" maxlength="255"
                        placeholder="Opcional" />
                    </div>

                    <div class="col-md-2">
                      <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bx bx-upload me-1"></i>
                        Subir
                      </button>
                    </div>
                  </form>
                </div>
              </div>

            </div>

            <!-- Lateral -->
            <div class="col-lg-4">

              <div class="disposal-side-card mb-4">
                <h6 class="mb-3">
                  <i class="bx bx-info-circle me-1"></i>
                  Proceso
                </h6>

                <div class="data-list">
                  <div class="data-list-item">
                    <span>Estado</span>
                    <span class="badge" id="det-estado"></span>
                  </div>

                  <div class="data-list-item">
                    <span>Clasificación</span>
                    <strong id="det-clasificacion"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Origen</span>
                    <strong id="det-origen"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Informe técnico</span>
                    <strong id="det-informe"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Registrado por</span>
                    <strong id="det-solicitante"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Evaluado por</span>
                    <strong id="det-evaluador"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Validado por</span>
                    <strong id="det-aprobador"></strong>
                  </div>
                </div>
              </div>

              <div class="disposal-side-card mb-4">
                <h6 class="mb-3">
                  <i class="bx bx-calendar me-1"></i>
                  Fechas del proceso
                </h6>

                <div class="data-list">
                  <div class="data-list-item">
                    <span>Registro</span>
                    <strong id="det-fecha-solicitud"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Evaluación</span>
                    <strong id="det-fecha-evaluacion"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Validación</span>
                    <strong id="det-fecha-aprobacion"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Baja ejecutada</span>
                    <strong id="det-fecha-baja"></strong>
                  </div>
                </div>
              </div>

              <div class="disposal-side-card">
                <h6 class="mb-3">
                  <i class="bx bx-cog me-1"></i>
                  Acciones rápidas
                </h6>

                <div id="det-acciones" class="d-grid gap-2"></div>
              </div>

            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cerrar
          </button>
        </div>

      </div>
    </div>
  </div>
  <!-- / Modal detalle baja -->


  <!-- Modal evaluación técnica -->
  <div class="modal fade" id="modalEvaluar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <div>
            <h5 class="modal-title">
              <i class="bx bx-search-alt me-1"></i>
              Evaluación técnica <span id="eval-codigo"></span>
            </h5>
            <small class="text-muted">
              Registra el informe técnico y el resultado de la evaluación.
            </small>
          </div>

          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="form-evaluar">
          <div class="modal-body">

            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label" for="eval-resultado">Resultado de la evaluación</label>
                <select class="form-select" id="eval-resultado" name="resultado" required>
                  <option value="EN_EVALUACION">Continuar en evaluación</option>
                  <option value="RECOMENDADA">Recomendar la baja</option>
                  <option value="RECHAZADA">Rechazar la propuesta</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="eval-evaluador">Evaluado por</label>
                <select class="form-select" id="eval-evaluador" name="evaluado_por">
                  <option value="">Sin especificar</option>
                  @foreach ($colaboradores as $c)
                    <option value="{{ $c->id_colaborador }}">
                      {{ $c->per_apepat }} {{ $c->per_apemat }}, {{ $c->per_nombre }}{{ $c->cargo ? ' — ' . $c->cargo : '' }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-12">
                <label class="form-label" for="eval-diagnostico">Informe / diagnóstico técnico</label>
                <textarea class="form-control" id="eval-diagnostico" name="diagnostico_tecnico" rows="4"
                  maxlength="2000" placeholder="Obligatorio para recomendar la baja..."></textarea>
              </div>

              {{-- Datos exigidos al RECOMENDAR --}}
              <div class="col-12 d-none" id="eval-recomendar-fields">
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label" for="eval-clasificacion">Clasificación final</label>
                    <select class="form-select" id="eval-clasificacion" name="clasificacion_final">
                      <option value="">Seleccione…</option>
                      <option value="RAEE">RAEE</option>
                      <option value="CHATARRA">Chatarra</option>
                      <option value="OBSOLETO">Obsoleto</option>
                      <option value="SIN_REPARACION">Sin reparación</option>
                      <option value="NO_DETERMINADO">No determinado</option>
                      <option value="OTRO">Otro</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="eval-informe">N° informe técnico</label>
                    <input type="text" class="form-control" id="eval-informe" name="numero_informe_tecnico"
                      maxlength="100" placeholder="Ej. INF-OTI-2026-014" />
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="eval-valor">Valor referencial (S/)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="eval-valor"
                      name="valor_referencial" placeholder="0.00" />
                  </div>
                </div>
              </div>

              <div class="col-md-12">
                <label class="form-label" for="eval-observaciones">Observaciones</label>
                <textarea class="form-control" id="eval-observaciones" name="observaciones" rows="2" maxlength="1000"
                  placeholder="Opcional..."></textarea>
              </div>

              <div class="col-12">
                <div class="alert alert-danger mb-0 d-none" id="eval-alerta-rechazo">
                  <i class="bx bx-error-circle me-1"></i>
                  Al rechazar, el activo volverá a su situación operativa (EN USO o DISPONIBLE).
                </div>
              </div>

            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              Cancelar
            </button>

            <button type="submit" class="btn btn-primary">
              <i class="bx bx-save me-1"></i>
              Guardar evaluación
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
  <!-- / Modal evaluación técnica -->


@endsection

@section('page-script')
  <script>
    window.bajas = @json($bajas);
    window.mantsBaja = @json($mantsBaja);
    window.routesBajas = {
      store: @json(route('bajas.store')),
      evaluar: @json(url('/bajas')) + '/{id}/evaluar',
      validar: @json(url('/bajas')) + '/{id}/validar',
      ejecutar: @json(url('/bajas')) + '/{id}/ejecutar',
      rechazar: @json(url('/bajas')) + '/{id}/rechazar'
    };
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/bajas/bajas.js'])
@endsection
