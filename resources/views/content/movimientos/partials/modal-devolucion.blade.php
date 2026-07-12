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

            <div class="col-md-6">
              <label class="form-label">Condición de retorno <span class="text-danger">*</span></label>
              <select class="form-select" id="dev-condicion" name="condicion_retorno">
                <option value="BUENO">Bueno</option>
                <option value="NUEVO">Nuevo</option>
                <option value="REGULAR">Regular</option>
                <option value="MALO">Malo</option>
              </select>
              <div class="invalid-feedback"></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Resultado <span class="text-danger">*</span></label>
              <select class="form-select" id="dev-estado" name="estado_devolucion">
                <option value="DEVUELTO">Conforme</option>
                <option value="DEVUELTO_OBSERVADO">Observado</option>
              </select>
              <div class="invalid-feedback"></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Tipo de documento</label>
              <select class="form-select" id="dev-tipodoc" name="tipo_documento">
                <option value="ACTA_RETORNO">Acta de conformidad de retorno</option>
                <option value="ACTA_ENTREGA">Acta de entrega</option>
                <option value="OFICIO">Oficio / memorando</option>
                <option value="OTRO">Otro</option>
              </select>
            </div>

            <div class="col-md-6">
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
