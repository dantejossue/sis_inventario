@extends('layouts/contentNavbarLayout')

@section('title', 'Mantenimientos - OTI')

@section('content')

  <!-- Encabezado -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <div>
      <h4 class="fw-bold mb-0">
        <span class="text-secondary fw-bold">Ciclo de vida /</span>
        Mantenimientos
      </h4>

      <p class="text-muted fw-light mb-1">
        Control de mantenimientos preventivos, correctivos, garantía y revisión técnica.
      </p>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoMantenimiento">
        <i class="bx bx-plus me-1"></i>
        Nuevo mantenimiento
      </button>
    </div>
  </div>

  <!-- KPIs -->
  <div class="row g-4">

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card maintenance-kpi-card rounded-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">En atención</span>
              <h3 class="mb-2" id="kpi-abiertos">0</h3>
              <small class="text-warning fw-semibold d-flex align-items-center">
                <i class="bx bx-time me-1"></i>
                <span id="kpi-abiertos-detalle">0 registrados</span>
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded-4 bg-label-warning">
                <i class="bx bxs-wrench fs-4"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card maintenance-kpi-card rounded-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Preventivos</span>
              <h3 class="mb-2" id="kpi-preventivos">0</h3>
              <small class="text-info fw-semibold">
                <span id="kpi-preventivos-detalle">0 en curso</span>
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded-4 bg-label-info">
                <i class="bx bxs-calendar-check fs-4"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card maintenance-kpi-card rounded-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Correctivos</span>
              <h3 class="mb-2" id="kpi-correctivos">0</h3>
              <small class="text-danger fw-semibold">
                <span id="kpi-correctivos-detalle">0 en curso</span>
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded-4 bg-label-danger">
                <i class="bx bxs-error-circle fs-4"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card maintenance-kpi-card rounded-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="fw-semibold d-block mb-1">Finalizados</span>
              <h3 class="mb-2" id="kpi-finalizados">0</h3>
              <small class="text-success fw-semibold d-flex align-items-center">
                <i class="bx bx-check-circle me-1"></i>
                <span id="kpi-finalizados-detalle">0 este mes</span>
              </small>
            </div>

            <div class="avatar">
              <span class="avatar-initial rounded-4 bg-label-success">
                <i class="bx bxs-check-shield fs-4"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Panel superior -->
  {{-- <div class="row mb-4">

    <!-- Flujo de atención -->
    <div class="col-xl-8 col-lg-7 mb-4 mb-lg-0">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0">Plan de atención técnica</h5>
            <small class="text-muted">
              Flujo de solicitud, diagnóstico, atención y cierre.
            </small>
          </div>

          <span class="badge bg-label-primary">OTI Soporte</span>
        </div>

        <div class="card-body">

          <div class="maintenance-flow">

            <div class="maintenance-flow-step">
              <div class="maintenance-flow-icon bg-label-primary">
                <i class="bx bx-support"></i>
              </div>
              <div>
                <h6>Solicitud</h6>
                <small>Reporte o programación</small>
              </div>
            </div>

            <div class="maintenance-flow-line"></div>

            <div class="maintenance-flow-step">
              <div class="maintenance-flow-icon bg-label-warning">
                <i class="bx bx-search-alt"></i>
              </div>
              <div>
                <h6>Diagnóstico</h6>
                <small>Evaluación técnica</small>
              </div>
            </div>

            <div class="maintenance-flow-line"></div>

            <div class="maintenance-flow-step">
              <div class="maintenance-flow-icon bg-label-info">
                <i class="bx bx-wrench"></i>
              </div>
              <div>
                <h6>Atención</h6>
                <small>Interna o proveedor</small>
              </div>
            </div>

            <div class="maintenance-flow-line"></div>

            <div class="maintenance-flow-step">
              <div class="maintenance-flow-icon bg-label-success">
                <i class="bx bx-check"></i>
              </div>
              <div>
                <h6>Cierre</h6>
                <small>Resultado y evidencia</small>
              </div>
            </div>

          </div>

          <hr class="my-4">

          <div class="row text-center">

            <div class="col-md-3 col-6 mb-3">
              <h4 class="mb-1" id="stat-atendidos">0%</h4>
              <small class="text-muted">Atendidos</small>
            </div>

            <div class="col-md-3 col-6 mb-3">
              <h4 class="mb-1" id="stat-criticos">0</h4>
              <small class="text-muted">Críticos abiertos</small>
            </div>

            <div class="col-md-3 col-6 mb-3">
              <h4 class="mb-1" id="stat-garantia">0</h4>
              <small class="text-muted">Garantía</small>
            </div>

            <div class="col-md-3 col-6 mb-3">
              <h4 class="mb-1" id="stat-baja">0</h4>
              <small class="text-muted">Recom. baja</small>
            </div>

          </div>

        </div>
      </div>
    </div>

    <!-- Alertas -->
    <div class="col-xl-4 col-lg-5">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Alertas técnicas</h5>
          <small class="text-muted">
            Equipos que requieren seguimiento.
          </small>
        </div>

        <div class="card-body" id="alertas-tecnicas">
          <p class="text-muted mb-0">Sin alertas pendientes.</p>
        </div>
      </div>
    </div>

  </div> --}}

  <!-- Filtros -->
  <div class="card mb-4 rounded-3">
    <div class="card-header">
      <h5 class="mb-0 fw-bold">Filtros de búsqueda</h5>
      <small class="text-light">
        Filtra por tipo, estado o fecha de reporte.
      </small>
    </div>

    <div class="card-body">
      <div class="row g-3">

        <div class="col-lg-3 col-md-6">
          <label class="form-label" for="filtro-tipo">Tipo</label>
          <select class="form-select" id="filtro-tipo">
            <option value="">Todos</option>
            <option value="PREVENTIVO">Preventivo</option>
            <option value="CORRECTIVO">Correctivo</option>
            {{-- Garantía ya no es un tipo de mantenimiento (ahora es una modalidad de atención) --}}
            {{-- <option value="GARANTIA">Garantía</option> --}}
            <option value="REVISION_TECNICA">Revisión técnica</option>
          </select>
        </div>

        <div class="col-lg-3 col-md-6">
          <label class="form-label" for="filtro-estado">Estado</label>
          <select class="form-select" id="filtro-estado">
            <option value="">Todos</option>
            <option value="ABIERTOS">— Abiertos —</option>
            <option value="REGISTRADO">Registrado</option>
            <option value="EN_ATENCION">En atención</option>
            <option value="FINALIZADO">Finalizado</option>
            <option value="CANCELADO">Cancelado</option>
          </select>
        </div>

        {{-- Filtro por prioridad retirado: prioridad ya no se usa en el módulo de mantenimientos --}}
        {{-- <div class="col-lg-2 col-md-6">
          <label class="form-label" for="filtro-prioridad">Prioridad</label>
          <select class="form-select" id="filtro-prioridad">
            <option value="">Todas</option>
            <option value="BAJA">Baja</option>
            <option value="MEDIA">Media</option>
            <option value="ALTA">Alta</option>
            <option value="CRITICA">Crítica</option>
          </select>
        </div> --}}

        <div class="col-lg-2 col-md-6">
          <label class="form-label" for="filtro-fecha">Reportados desde</label>
          <input type="date" class="form-control" id="filtro-fecha" />
        </div>

        <div class="col-lg-2 col-md-6 d-flex align-items-end">
          <button class="btn btn-outline-secondary w-100" id="filtro-reset">
            <i class="bx bxs-eraser me-1"></i>
            Limpiar
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card rounded-3">
    <div class="card-header border-bottom">
      <h5 class="mb-0 fw-bold">Listado de mantenimientos</h5>
      <small class="text-light">
        Historial técnico de diagnósticos, reparaciones, garantías y evidencias.
      </small>
    </div>

    <div class="card-body pt-6">
      <table class="table table-hover table-maintenance small" id="miTablaMantenimientos">
        <thead>
          <tr>
            <th class="fw-bold">Código</th>
            <th class="fw-bold">Activo</th>
            <th class="fw-bold">Tipo</th>
            {{-- <th class="fw-bold">Problema / Diagnóstico</th> --}}
            <th class="fw-bold">Técnico / Proveedor</th>
            {{-- Columna prioridad retirada; reemplazada por Modalidad --}}
            {{-- <th class="fw-bold">Prioridad</th> --}}
            <th class="fw-bold">Modalidad</th>
            <th class="fw-bold">Estado</th>
            <th class="fw-bold">Costo</th>
            <th class="fw-bold">Fecha</th>
            <th class="fw-bold text-end">Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0"></tbody>
      </table>
    </div>
  </div>


  <!-- Modal nuevo mantenimiento -->
  {{-- <div class="modal fade" id="modalNuevoMantenimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">

        <div class="modal-header">
          <div>
            <h5 class="modal-title">
              <i class="bx bx-wrench me-1"></i>
              Nuevo mantenimiento
            </h5>
            <small class="text-muted">
              Registra mantenimiento preventivo, correctivo, garantía o revisión técnica.
            </small>
          </div>

          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="form-nuevo-mantenimiento">
          <div class="modal-body">

            <div class="row g-4">

              <!-- Formulario -->
              <div class="col-lg-8">

                <div class="row g-3">

                  <div class="col-md-6">
                    <label class="form-label" for="nuevo-activo">Activo</label>
                    <select class="form-select" id="nuevo-activo" name="id_activo" required>
                      <option value="">Seleccione activo…</option>
                      @foreach ($activos as $a)
                        <option value="{{ $a['id_activo'] }}">
                          {{ $a['codigo_interno'] }} — {{ $a['modelo'] ?: 'Sin modelo' }}
                        </option>
                      @endforeach
                    </select>
                    <small class="text-muted">
                      Solo activos sin mantenimiento en curso y no dados de baja.
                    </small>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label" for="nuevo-tipo">Tipo</label>
                    <select class="form-select" id="nuevo-tipo" name="tipo_mantenimiento" required>
                      <option value="">Seleccione…</option>
                      <option value="PREVENTIVO">Preventivo</option>
                      <option value="CORRECTIVO">Correctivo</option>
                      <option value="GARANTIA">Garantía</option>
                      <option value="REVISION_TECNICA">Revisión técnica</option>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label" for="nuevo-prioridad">Prioridad</label>
                    <select class="form-select" id="nuevo-prioridad" name="prioridad">
                      <option value="BAJA">Baja</option>
                      <option value="MEDIA" selected>Media</option>
                      <option value="ALTA">Alta</option>
                      <option value="CRITICA">Crítica</option>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="nuevo-origen">Origen del reporte</label>
                    <select class="form-select" id="nuevo-origen" name="origen_reporte">
                      <option value="USUARIO" selected>Área usuaria</option>
                      <option value="OTI">OTI</option>
                      <option value="USG">Servicios Generales</option>
                      <option value="INVENTARIO">Inventario</option>
                      <option value="OTRO">Otro</option>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="nuevo-solicitante">Solicitado por (opcional)</label>
                    <select class="form-select" id="nuevo-solicitante" name="solicitado_por">
                      <option value="">Sin solicitante específico</option>
                      @foreach ($colaboradores as $c)
                        <option value="{{ $c->id_colaborador }}">
                          {{ $c->per_apepat }} {{ $c->per_apemat }}, {{ $c->per_nombre }}{{ $c->cargo ? ' — ' . $c->cargo : '' }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="nuevo-tecnico">Técnico responsable (opcional)</label>
                    <select class="form-select" id="nuevo-tecnico" name="tecnico_responsable">
                      <option value="">Por asignar</option>
                      @foreach ($colaboradores as $c)
                        <option value="{{ $c->id_colaborador }}">
                          {{ $c->per_apepat }} {{ $c->per_apemat }}, {{ $c->per_nombre }}{{ $c->cargo ? ' — ' . $c->cargo : '' }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label" for="nuevo-fecha">Fecha de reporte</label>
                    <input type="date" class="form-control" id="nuevo-fecha" name="fecha_reporte"
                      value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required />
                  </div>

                  <div class="col-md-3">
                    <label class="form-label" for="nuevo-costo">Costo estimado (S/)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="nuevo-costo" name="costo"
                      placeholder="0.00" />
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="nuevo-proveedor">Proveedor (opcional)</label>
                    <input type="text" class="form-control" id="nuevo-proveedor" name="proveedor" maxlength="150"
                      placeholder="Ej. Tech Solutions Perú SAC" />
                  </div>

                  <div class="col-md-12">
                    <label class="form-label" for="nuevo-descripcion">Problema reportado / motivo</label>
                    <textarea class="form-control" id="nuevo-descripcion" name="descripcion" rows="3" required
                      maxlength="2000"
                      placeholder="Describe el problema, motivo del mantenimiento o revisión programada..."></textarea>
                  </div>

                  <div class="col-md-12">
                    <label class="form-label" for="nuevo-diagnostico">Diagnóstico inicial (opcional)</label>
                    <textarea class="form-control" id="nuevo-diagnostico" name="diagnostico" rows="2" maxlength="2000"
                      placeholder="Describe el diagnóstico inicial del técnico..."></textarea>
                  </div>

                </div>

              </div>

              <!-- Resumen lateral -->
              <div class="col-lg-4">

                <div class="maintenance-create-summary">
                  <div class="maintenance-create-icon bg-label-warning">
                    <i class="bx bx-wrench"></i>
                  </div>

                  <h5 class="mb-1">Resumen técnico</h5>
                  <p class="text-muted mb-3">
                    El mantenimiento quedará vinculado al activo, sus evidencias y su trazabilidad.
                  </p>

                  <div class="data-list">
                    <div class="data-list-item">
                      <span>Activo</span>
                      <strong id="resumen-activo">No seleccionado</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Tipo</span>
                      <strong id="resumen-tipo">No seleccionado</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Prioridad</span>
                      <strong id="resumen-prioridad">Media</strong>
                    </div>

                    <div class="data-list-item">
                      <span>Estado inicial</span>
                      <span class="badge bg-label-primary">Solicitado</span>
                    </div>
                  </div>

                  <div class="alert alert-warning mt-3 mb-0">
                    <i class="bx bx-info-circle me-1"></i>
                    Al iniciar la atención, el activo pasará a situación
                    <strong>EN MANTENIMIENTO</strong>. Si no es reparable, podrá recomendarse para baja.
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
              Registrar mantenimiento
            </button>
          </div>
        </form>

      </div>
    </div>
  </div> --}}
  <!-- / Modal nuevo mantenimiento -->
  {{-- Modal nuevo mantenimiento --}}
  <div class="modal fade" id="modalNuevoMantenimiento" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

      <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">

        <div class="modal-header py-4 border-bottom">
          <div>
            <h5 class="modal-title d-flex align-items-center fw-bold" id="modalNuevoMantenimientoLabel">
              <i class="bx bx-wrench me-1"></i>
              Nuevo mantenimiento
            </h5>

            <small class="text-light">
              Registra la atención técnica de un activo de OTI.
            </small>
          </div>

          <button type="button" class="btn-close" data-bs-dismiss="modal">
          </button>
        </div>

        <form id="form-nuevo-mantenimiento" class="d-flex flex-column flex-grow-1 overflow-hidden">
          <div class="modal-body overflow-auto py-4">

            <div class="row g-3">

              {{-- Activo --}}
              <div class="col-12">
                <label class="form-label" for="nuevo-activo">
                  Activo
                  <span class="text-danger">*</span>
                </label>

                <select class="form-select" id="nuevo-activo" name="id_activo" required>

                  <option value="">Seleccione un activo...</option>

                  @foreach ($activos as $a)
                    <option value="{{ $a['id_activo'] }}">
                      {{ $a['codigo_interno'] }}
                      @if (!empty($a['codigo_patrimonial']))
                        · {{ $a['codigo_patrimonial'] }}
                      @endif
                      — {{ $a['modelo'] ?: 'Sin modelo registrado' }}
                    </option>
                  @endforeach
                </select>

                <div class="invalid-feedback"></div>

                <small class="text-light fw-light">
                  Solo se muestran activos disponibles para mantenimiento.
                </small>
              </div>

              {{-- Tipo de mantenimiento --}}
              <div class="col-md-6">
                <label class="form-label" for="nuevo-tipo">
                  Tipo de mantenimiento
                  <span class="text-danger">*</span>
                </label>

                <select class="form-select" id="nuevo-tipo" name="tipo_mantenimiento" required>

                  <option value="">Seleccione...</option>
                  <option value="PREVENTIVO">Preventivo</option>
                  <option value="CORRECTIVO">Correctivo</option>
                  <option value="REVISION_TECNICA">Revisión técnica</option>
                </select>

                <div class="invalid-feedback"></div>
              </div>

              {{-- Modalidad de atención: se decide automáticamente en el servidor según la
                   garantía del activo. El input oculto solo transporta el valor calculado por JS;
                   el backend lo vuelve a verificar. --}}
              <input type="hidden" id="nuevo-modalidad" name="modalidad_atencion">

              {{-- Fecha de reporte --}}
              <div class="col-md-6">
                <label class="form-label" for="nuevo-fecha">
                  Fecha de reporte
                  <span class="text-danger">*</span>
                </label>

                <input type="date" class="form-control" id="nuevo-fecha" name="fecha_reporte"
                  value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>

                <div class="invalid-feedback"></div>
              </div>

              <div class="col-12 d-none" id="grupo-estado-garantia">
                <div class="alert mb-0" id="alerta-garantia">
                  <div class="d-flex align-items-start">
                    <i class="bx bx-shield-quarter fs-5 me-1"></i>
                    <div>
                      <strong id="garantia-titulo"></strong>
                      <div class="small mt-1" id="garantia-detalle"></div>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Técnico OTI --}}
              <div class="col-md-12" id="grupo-tecnico">
                <label class="form-label" for="nuevo-tecnico">
                  Técnico responsable
                  <span class="text-danger">*</span>
                </label>

                <select class="form-select" id="nuevo-tecnico" name="tecnico_responsable">

                  <option value="">Seleccione un técnico...</option>

                  @foreach ($tecnicosOti as $c)
                    <option value="{{ $c->id_colaborador }}">
                      {{ $c->per_apepat }}
                      {{ $c->per_apemat }},
                      {{ $c->per_nombre }}

                      @if ($c->cargo)
                        — {{ $c->cargo }}
                      @endif
                    </option>
                  @endforeach
                </select>

                <div class="invalid-feedback"></div>

                <small class="text-muted">
                  Técnico de OTI encargado de la evaluación y atención.
                </small>
              </div>

              {{-- Proveedor --}}
              <div class="col-md-12 d-none" id="grupo-proveedor">
                <label class="form-label" for="nuevo-proveedor">
                  Proveedor responsable
                  <span class="text-danger">*</span>
                </label>

                <input type="text" class="form-control" id="nuevo-proveedor" name="proveedor" maxlength="150"
                  placeholder="Ej. Lenovo Perú, HP, proveedor adjudicado...">

                <div class="invalid-feedback"></div>

                <small class="text-muted">
                  Empresa que atenderá el activo mediante garantía.
                </small>
              </div>



              {{-- Solicitado por --}}
              <div class="col-md-12">
                <label class="form-label" for="nuevo-solicitante">
                  Reportado por
                  <span class="text-muted">(opcional)</span>
                </label>

                <select class="form-select" id="nuevo-solicitante" name="solicitado_por">

                  <option value="">No especificado</option>

                  @foreach ($colaboradores as $c)
                    <option value="{{ $c->id_colaborador }}">
                      {{ $c->per_apepat }}
                      {{ $c->per_apemat }},
                      {{ $c->per_nombre }}

                      @if ($c->cargo)
                        — {{ $c->cargo }}
                      @endif
                    </option>
                  @endforeach
                </select>

                <div class="invalid-feedback"></div>
              </div>

              {{-- Problema reportado --}}
              <div class="col-12">
                <label class="form-label" for="nuevo-descripcion">
                  Problema reportado o motivo
                  <span class="text-danger">*</span>
                </label>

                <textarea class="form-control" id="nuevo-descripcion" name="descripcion" rows="4" maxlength="2000" required
                  placeholder="Describe la falla, el problema presentado o el motivo del mantenimiento preventivo..."></textarea>

                <div class="invalid-feedback"></div>

                <small class="text-light fw-light">
                  El diagnóstico técnico se registrará durante la atención.
                </small>
              </div>

              {{-- Información --}}
              <div class="col-12">
                <div class="alert alert-info mb-0">
                  <div class="d-flex">
                    <i class="bx bx-info-circle fs-5 me-2 mt-0"></i>

                    <div>
                      <strong>Flujo del mantenimiento</strong>

                      <div class="small mt-1">
                        Al registrar, el mantenimiento quedará pendiente de atención.
                        Cuando el técnico inicie el trabajo, el activo pasará a
                        <strong>EN MANTENIMIENTO</strong>.
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>

          </div>

          <div class="modal-footer py-4 border-top">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              Cancelar
            </button>

            <button type="submit" class="btn btn-primary" id="btn-guardar-mantenimiento">

              <span class="spinner-border spinner-border-sm d-none me-1" role="status">
              </span>

              <i class="bx bx-save me-1"></i>
              Registrar mantenimiento
            </button>
          </div>

        </form>

      </div>
    </div>
  </div>

  <!-- Modal detalle mantenimiento -->
  <div class="modal fade" id="modalDetalleMantenimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">

        <div class="modal-header py-4 border-bottom border-sw">
          <div>
            <h5 class="modal-title d-flex align-items-center fw-bold" style="color:#084b8a;">
              <i class="bx bxs-wrench me-1"></i>
              Detalle del mantenimiento: &nbsp; <span id="det-codigo" style="color: brown;"></span>
            </h5>
            <small class="d-block text-muted">
              Diagnóstico, atención técnica, evidencias, costos y trazabilidad.
            </small>
          </div>

          <button type="button" class="btn-close position-absolute top-0 end-0 mt-3 me-3"
            data-bs-dismiss="modal"></button>
        </div>


        <div class="modal-body overflow-auto">

          <div class="row">

            <!-- Principal -->
            <div class="col-lg-8">



              <div class="maintenance-detail-header row g-3 mb-4">

                <div class="col-12 col-md-4">
                  <div class="maintenance-detail-priority">
                    <span>Tipo</span>
                    <strong id="det-tipo"></strong>
                  </div>
                </div>

                <div class="col-12 col-md-4">
                  <div class="maintenance-detail-priority">
                    <span>Modalidad</span>
                    <strong id="det-modalidad"></strong>
                  </div>
                </div>

                <div class="col-12 col-md-4">
                  <div class="maintenance-detail-priority">
                    <span>Reportado el</span>
                    <strong id="det-subtitulo"></strong>
                  </div>
                </div>

              </div>

              <!-- Activo -->
              <div class="card border rounded-4 mb-4">
                <div class="card-header">
                  <div class="d-flex align-items-center">
                    <i class="bx bx-laptop me-1 fw-bold"></i>
                    <h6 class="mb-0 fw-bold">
                      Activo vinculado
                    </h6>
                  </div>
                </div>

                <div class="card-body">
                  <div class="maintenance-linked-asset">
                    <div class="maintenance-linked-icon bg-label-primary">
                      <i id="icon-category"></i>
                    </div>

                    <div class="flex-grow-1">
                      <h6 class="mb-1" id="det-activo-modelo"></h6>
                      <small class="text-muted d-block" id="det-activo-codigo"></small>
                      <small class="text-muted d-block" id="det-activo-patrimonial"></small>
                      <small class="text-muted d-block" id="det-activo-responsable"></small>
                    </div>

                    <a href="#" id="det-activo-url" class="btn btn-sm btn-outline-primary">
                      Ver ficha
                    </a>
                  </div>
                </div>
              </div>

              <!-- Diagnóstico -->
              <div class="card border rounded-4 mb-4">
                <div class="card-header">
                  <div class="d-flex align-items-center">
                    <i class="bx bx-search-alt me-1 fw-bold"></i>
                    <h6 class="mb-0 fw-bold">
                      Diagnóstico técnico
                    </h6>
                  </div>
                </div>

                <div class="card-body">
                  <div class="maintenance-diagnosis-box mb-3">
                    <span>Problema reportado</span>
                    <p id="det-descripcion"></p>
                  </div>

                  <div class="maintenance-diagnosis-box mb-3">
                    <span>Diagnóstico</span>
                    <p id="det-diagnostico"></p>
                  </div>

                  <div class="maintenance-diagnosis-box">
                    <span>Resultado</span>
                    <p id="det-resultado"></p>
                  </div>
                </div>
              </div>

              <!-- Historial de avances -->
              <div class="card border rounded-4 mb-4">
                <div class="card-header">
                  <div class="d-flex align-items-center">
                    <i class="bx bx-history me-1 fw-bold"></i>
                    <h6 class="mb-0 fw-bold">
                      Historial de avances
                    </h6>
                  </div>
                </div>

                <div class="card-body">
                  <div id="det-historial-avances"></div>
                </div>
              </div>

              <!-- Evidencias -->
              <div class="card border rounded-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center">
                    <i class="bx bx-paperclip me-1 fw-bold"></i>
                    <h6 class="mb-0 fw-bold">
                      Evidencias
                    </h6>
                  </div>
                </div>

                <div class="card-body">
                  <div class="row g-3" id="det-evidencias"></div>

                  <hr class="my-3">

                  <form action="{{ route('documentos.store') }}" method="POST" enctype="multipart/form-data"
                    class="row g-2 align-items-end" id="form-evidencia">
                    @csrf
                    <input type="hidden" name="entidad_tipo" value="MANTENIMIENTO">
                    <input type="hidden" name="entidad_id" value="" id="evidencia-entidad-id">
                    <input type="hidden" name="tipo_documento" value="Evidencia de mantenimiento">

                    <div class="col-md-7">
                      <label class="form-label" for="evidencia-archivo">
                        Adjuntar evidencia (foto, informe, cotización · máx. 5 MB)
                      </label>
                      <input type="file" class="form-control" id="evidencia-archivo" name="archivo" required
                        accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" />
                    </div>

                    <div class="col-md-3">
                      <label class="form-label" for="evidencia-descripcion">Descripción</label>
                      <input type="text" class="form-control" id="evidencia-descripcion" name="descripcion"
                        maxlength="255" placeholder="Opcional" />
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

              <div class="maintenance-side-card mb-4">
                <div class="d-flex align-items-center">
                  <i class="bx bx-info-circle me-1 fw-bold"></i>
                  <h6 class="m-0 fw-bold">
                    Estado técnico
                  </h6>
                </div>

                <div class="data-list">
                  <div class="data-list-item">
                    <span>Estado</span>
                    <span class="badge" id="det-estado"></span>
                  </div>

                  <div class="data-list-item">
                    <span>Resultado de atención</span>
                    <span class="badge" id="det-resultado-atencion"></span>
                  </div>

                  {{-- Origen del reporte retirado de la interfaz nueva --}}
                  {{-- <div class="data-list-item">
                    <span>Origen</span>
                    <strong id="det-origen"></strong>
                  </div> --}}

                  <div class="data-list-item">
                    <span>Solicitado por</span>
                    <strong id="det-solicitante"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Técnico</span>
                    <strong id="det-tecnico"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Proveedor</span>
                    <strong id="det-proveedor"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Costo</span>
                    <strong id="det-costo"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Registrado por</span>
                    <strong id="det-registrador"></strong>
                  </div>
                </div>
              </div>

              <div class="maintenance-side-card mb-4">
                <div class="d-flex align-items-center">
                  <i class="bx bx-calendar me-1 fw-bold"></i>
                  <h6 class="m-0 fw-bold">
                    Fechas
                  </h6>
                </div>

                <div class="data-list">
                  <div class="data-list-item">
                    <span>Reporte</span>
                    <strong id="det-fecha-reporte"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Inicio de atención</span>
                    <strong id="det-fecha-inicio"></strong>
                  </div>

                  <div class="data-list-item">
                    <span>Fin de atención</span>
                    <strong id="det-fecha-fin"></strong>
                  </div>
                </div>
              </div>

              <div class="maintenance-side-card">
                <div class="d-flex align-items-center mb-3">
                  <i class="bx bx-cog me-1 fw-bold"></i>
                  <h6 class="m-0 fw-bold">
                    Acciones rápidas
                  </h6>
                </div>

                <div id="det-acciones" class="d-grid gap-2"></div>
              </div>

            </div>

          </div>

        </div>

        <div class="modal-footer border-top py-4">
          <button class="btn btn-label-secondary fw-bold" data-bs-dismiss="modal">
            Cerrar
          </button>
        </div>

      </div>
    </div>
  </div>
  <!-- / Modal detalle mantenimiento -->


  <!-- Modal avance técnico -->
  <div class="modal fade" id="modalAvance" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <div>
            <h5 class="modal-title d-flex align-items-center">
              <i class="bx bx-edit me-1"></i>
              Avance técnico: &nbsp; <span class="text-navy fw-bold" id="avance-codigo"></span>
            </h5>
            <small class="text-light">
              Registra un avance del diagnóstico y la atención. El activo pasará a
              atención (interna o proveedor según la modalidad definida al registrar).
            </small>
          </div>

          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="form-avance" enctype="multipart/form-data">
          <div class="modal-body">

            <div class="row g-3">

              <div class="col-12">
                <label class="form-label" for="avance-diagnostico">Diagnóstico</label>
                <textarea class="form-control" id="avance-diagnostico" name="diagnostico" rows="3" maxlength="2000"
                  placeholder="Diagnóstico técnico del avance (opcional)..."></textarea>
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-12">
                <label class="form-label" for="avance-actividad">
                  Actividad realizada
                  <span class="text-danger">*</span>
                </label>
                <textarea class="form-control" id="avance-actividad" name="actividad_realizada" rows="3" maxlength="2000"
                  required placeholder="Describe la actividad ejecutada en este avance..."></textarea>
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-12">
                <label class="form-label" for="avance-observacion">Observación</label>
                <textarea class="form-control" id="avance-observacion" name="observacion" rows="2" maxlength="2000"
                  placeholder="Observaciones adicionales (opcional)..."></textarea>
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="avance-costo">
                  Costo (S/)
                  <span class="text-muted">(opcional)</span>
                </label>
                <input type="number" step="0.01" min="0" class="form-control" id="avance-costo"
                  name="costo" placeholder="0.00" />
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="avance-evidencia">
                  Evidencia
                  <span class="text-muted">(opcional · máx. 5 MB)</span>
                </label>
                <input type="file" class="form-control" id="avance-evidencia" name="evidencia"
                  accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" />
                <div class="invalid-feedback"></div>
              </div>

            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              Cancelar
            </button>

            <button type="submit" class="btn btn-primary">
              <i class="bx bx-save me-1"></i>
              Guardar avance
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
  <!-- / Modal avance técnico -->


  <!-- Modal finalizar mantenimiento -->
  <div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <div>
            <h5 class="modal-title">
              <i class="bx bx-check-circle me-1"></i>
              Finalizar mantenimiento <span id="fin-codigo"></span>
            </h5>
            <small class="text-muted">
              Exige diagnóstico y resultado. El activo volverá a su situación operativa o quedará pendiente de baja.
            </small>
          </div>

          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="form-finalizar" enctype="multipart/form-data">
          <div class="modal-body">

            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label" for="fin-resultado-atencion">
                  Resultado de la atención
                  <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="fin-resultado-atencion" name="resultado_atencion" required>
                  <option value="OPERATIVO">Equipo operativo</option>
                  <option value="RECOMENDADO_BAJA">Recomendar baja</option>
                </select>
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-md-6" id="fin-condicion-wrap">
                <label class="form-label" for="fin-condicion">
                  Condición física resultante
                  <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="fin-condicion" name="condicion_resultante">
                  <option value="">Selecciona...</option>
                  <option value="NUEVO">Nuevo</option>
                  <option value="BUENO">Bueno</option>
                  <option value="REGULAR">Regular</option>
                  <option value="MALO">Malo</option>
                </select>
                <div class="form-text">Condición que certifica el técnico tras atender o revisar el equipo.</div>
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="fin-costo">
                  Costo final (S/)
                  <span class="text-muted">(opcional)</span>
                </label>
                <input type="number" step="0.01" min="0" class="form-control" id="fin-costo"
                  name="costo" placeholder="0.00" />
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-md-12">
                <label class="form-label" for="fin-diagnostico">
                  Diagnóstico final
                  <span class="text-danger">*</span>
                </label>
                <textarea class="form-control" id="fin-diagnostico" name="diagnostico" rows="3" maxlength="2000" required
                  placeholder="Diagnóstico técnico final..."></textarea>
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-md-12">
                <label class="form-label" for="fin-resultado">
                  Actividad / resultado final
                  <span class="text-danger">*</span>
                </label>
                <textarea class="form-control" id="fin-resultado" name="resultado" rows="3" required maxlength="2000"
                  placeholder="Describe la actividad realizada, repuestos y resultado final..."></textarea>
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-12">
                <label class="form-label" for="fin-evidencia">
                  Evidencia final
                  <span class="text-danger">*</span>
                  <span class="text-muted">(informe, foto, informe del proveedor, cotización, acta u otro sustento · máx.
                    5 MB)</span>
                </label>
                <input type="file" class="form-control" id="fin-evidencia" name="evidencia" required
                  accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" />
                <div class="invalid-feedback"></div>
              </div>

              <div class="col-12">
                <div class="alert alert-warning mb-0 d-none" id="fin-alerta-baja">
                  <i class="bx bx-error-circle me-1"></i>
                  El activo quedará en situación <strong>PENDIENTE DE BAJA</strong> y este mantenimiento
                  servirá de sustento para la propuesta de baja (módulo de bajas).
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
              Finalizar mantenimiento
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
  <!-- / Modal finalizar mantenimiento -->

  {{-- Modal de nueva baja reutilizable: se abre al finalizar con RECOMENDADO_BAJA.
       Se incluye sin $bajasActivos; el activo se precarga desde el mantenimiento. --}}
  @include('content.bajas.partials.modal-nueva-baja')

@endsection

@section('page-script')
  <script>
    window.mantenimientos = @json($mantenimientos);
    window.activosMantenimiento = @json($activos->keyBy('id_activo'));
    window.routesMant = {
      store: @json(route('mantenimientos.store')),
      avanzar: @json(url('/mantenimientos')) + '/{id}/avanzar',
      finalizar: @json(url('/mantenimientos')) + '/{id}/finalizar',
      // cerrar: fuera de uso (FINALIZADO es el estado terminal)
      // cerrar: @json(url('/mantenimientos')) + '/{id}/cerrar',
      cancelar: @json(url('/mantenimientos')) + '/{id}/cancelar'
    };
    // Ruta usada por el modal de baja compartido (integración mantenimiento → baja).
    window.routesBajas = {
      store: @json(route('bajas.store'))
    };
  </script>
  @vite(['resources/js/vendors/index.js', 'resources/js/pages/mantenimientos/mantenimientos.js'])
@endsection
