{{--
  Modal reutilizable "Nueva propuesta de baja".
  Se incluye tanto en la vista de bajas como en la de mantenimientos.
  - En bajas: se le pasa $bajasActivos (activos elegibles) para poblar el <select>.
  - En mantenimientos: se incluye sin $bajasActivos; el activo y el mantenimiento
    de origen se precargan y bloquean por JavaScript (prefill).
  Requiere window.routesBajas.store en la página que lo incluya.
--}}
<div class="modal fade" id="modalNuevaBaja" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">

      <div class="modal-header py-3 border-bottom">
        <div>
          <h5 class="modal-title m-0 d-flex align-items-center fw-bold" id="modalNuevaBajaLabel" style="color:#084b8a;">
            <i class="bx bxs-down-arrow-circle me-1"></i>
            Nueva propuesta de baja
          </h5>
          <small class="text-muted">
            La propuesta queda REGISTRADA y el activo OBSERVADO. Solo la ejecución formal lo da de baja.
          </small>
        </div>

        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="form-nueva-baja" enctype="multipart/form-data" class="d-flex flex-column flex-grow-1 overflow-hidden">
        <div class="modal-body py-6 overflow-auto">

          <div class="row g-3">

            {{-- Info: proviene de un mantenimiento --}}
            <div class="col-12 d-none" id="baja-origen-info">
              <div class="alert alert-info mb-0">
                <div class="d-flex">
                  <i class="bx bx-link-alt fs-5 me-2 mt-1"></i>
                  <div>
                    <strong>Propuesta originada por el mantenimiento <span id="baja-origen-codigo"></span></strong>
                    <div class="small mt-1">
                      <span class="text-muted">Diagnóstico:</span>
                      <span id="baja-origen-diagnostico">—</span>
                    </div>
                    <div class="small">
                      <span class="text-muted">Resultado:</span>
                      <span id="baja-origen-resultado">—</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Activo --}}
            <div class="col-md-6 mt-0">
              <label class="form-label" for="baja-activo">
                Activo
                <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="baja-activo" name="id_activo" required>
                <option value="">Seleccione un activo…</option>
                @isset($bajasActivos)
                  @foreach ($bajasActivos as $a)
                    <option value="{{ $a['id_activo'] }}">
                      {{ $a['codigo_interno'] }} — {{ $a['modelo'] ?: 'Sin modelo' }}
                    </option>
                  @endforeach
                @endisset
              </select>
              <div class="invalid-feedback"></div>
              <small class="text-muted">Solo activos sin propuesta de baja en curso.</small>
            </div>

            {{-- Causal --}}
            <div class="col-md-6 mt-0">
              <label class="form-label" for="baja-causal">
                Causal de baja
                <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="baja-causal" name="causal_baja" required>
                <option value="">Seleccione…</option>
                <option value="DANO_IRREPARABLE">Daño irreparable</option>
                <option value="OBSOLESCENCIA">Obsolescencia</option>
                <option value="REPARACION_NO_CONVENIENTE">Reparación no conveniente</option>
                <option value="RAEE">RAEE</option>
                <option value="SUSTRACCION">Sustracción</option>
                <option value="OTRO">Otro</option>
              </select>
              <div class="invalid-feedback"></div>
            </div>

            {{-- Mantenimiento de origen (lo fija el JS cuando corresponde) --}}
            <input type="hidden" id="baja-mantenimiento" name="id_mantenimiento_origen">

            {{-- Fecha de registro --}}
            <div class="col-md-6">
              <label class="form-label" for="baja-fecha">
                Fecha de registro
                <span class="text-danger">*</span>
              </label>
              <input type="date" class="form-control" id="baja-fecha" name="fecha_registro"
                value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
              <div class="invalid-feedback"></div>
            </div>

            {{-- Documento inicial (opcional) --}}
            <div class="col-md-6">
              <label class="form-label" for="baja-documento">
                Documento inicial
                <span class="text-muted">(opcional · máx. 5 MB)</span>
              </label>
              <input type="file" class="form-control" id="baja-documento" name="documento"
                accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx">
              <div class="invalid-feedback"></div>
            </div>

            {{-- Motivo / sustento --}}
            <div class="col-12">
              <label class="form-label" for="baja-motivo">
                Motivo / sustento de la baja
                <span class="text-danger">*</span>
              </label>
              <textarea class="form-control" id="baja-motivo" name="motivo" rows="3" maxlength="2000" required
                placeholder="Describe el sustento de la propuesta de baja..."></textarea>
              <div class="invalid-feedback"></div>
            </div>

            {{-- Observaciones --}}
            <div class="col-12">
              <label class="form-label" for="baja-observaciones">
                Observaciones
                <span class="text-muted">(opcional)</span>
              </label>
              <textarea class="form-control" id="baja-observaciones" name="observaciones" rows="2" maxlength="1000"
                placeholder="Notas adicionales..."></textarea>
              <div class="invalid-feedback"></div>
            </div>

          </div>

        </div>

        <div class="modal-footer py-4 border-top">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="btn-guardar-baja">
            <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
            <i class="bx bx-check me-1"></i>
            Registrar propuesta
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
