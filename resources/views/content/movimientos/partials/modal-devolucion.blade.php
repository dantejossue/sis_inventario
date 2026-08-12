{{-- Modal de devolución de préstamo (reutilizable en lista y detalle) --}}
<div class="modal fade" id="modalDevolucion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title"><i class="bx bx-undo me-1"></i> Devolución de préstamo <span id="dev-codigo"></span></h5>
          <small class="text-muted">Registra el retorno del activo y adjunta el acta de conformidad.</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="formDevolucion" enctype="multipart/form-data">
        <input type="hidden" id="dev-id">
        <div class="modal-body">
          <div class="row g-3">

            <div class="col-12">
              <label class="form-label">
                Condición de retorno de los activos
                <span class="text-danger">*</span>
              </label>

              <div class="table-responsive border rounded">
                <table class="table table-sm align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Activo</th>
                      <th>Condición de salida</th>
                      <th>Condición de retorno</th>
                    </tr>
                  </thead>

                  <tbody id="dev-activos">
                    <tr>
                      <td colspan="3" class="text-center text-muted py-3">
                        Selecciona un préstamo.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="invalid-feedback" id="dev-detalles-error"></div>
            </div>

            {{-- <div class="col-md-6">
              <label class="form-label">Resultado <span class="text-danger">*</span></label>
              <select class="form-select" id="dev-estado" name="estado_devolucion">
                <option value="DEVUELTO">Conforme</option>
                <option value="DEVUELTO_OBSERVADO">Observado</option>
              </select>
              <div class="invalid-feedback"></div>
            </div> --}}

            <div class="col-md-12">
              <label class="form-label">Tipo de documento</label>
              <select class="form-select" id="dev-tipodoc" name="tipo_documento">
                <option value="ACTA_RETORNO">Acta de conformidad de retorno</option>
                <option value="ACTA_DISCONFORMIDAD">Acta de disconformidad</option>
                <option value="OFICIO">Oficio / memorando</option>
                <option value="OTRO">Otro</option>
              </select>
            </div>

            <div class="col-md-12">
              <label class="form-label">Documento de sustento <span class="text-danger">*</span></label>
              <input type="file" class="form-control" id="dev-documento" name="documento"
                accept=".pdf,.jpg,.jpeg,.png,.webp,.xls,.xlsx,.doc,.docx,.zip,.rar">
              <div class="invalid-feedback"></div>
            </div>

            <div class="col-12">
              <label class="form-label">Observación (opcional)</label>
              <textarea class="form-control" id="dev-obs" name="observacion_devolucion" rows="2"></textarea>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success" id="btnDevolucion">
            <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
            <i class="bx bx-check me-1"></i> Registrar devolución
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
