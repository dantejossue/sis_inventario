import $ from 'jquery';
import Swal from 'sweetalert2';

/**
 * Lógica compartida del modal "Nueva propuesta de baja".
 * La usan tanto la vista de bajas (registro manual) como la de mantenimientos
 * (precarga desde un mantenimiento que recomendó baja). Mismos nombres de campos,
 * misma ruta (window.routesBajas.store) y misma lógica de envío (FormData).
 */

const csrf = () => $('meta[name="csrf-token"]').attr('content');

function limpiarErrores($form) {
  $form.find('.is-invalid').removeClass('is-invalid');
  $form.find('.invalid-feedback').removeClass('d-block').text('');
}

function mostrarErrores($form, errors) {
  const mapa = {
    id_activo: '#baja-activo',
    id_mantenimiento_origen: '#baja-activo',
    causal_baja: '#baja-causal',
    motivo: '#baja-motivo',
    fecha_registro: '#baja-fecha',
    observaciones: '#baja-observaciones',
    documento: '#baja-documento'
  };
  Object.entries(errors).forEach(([campo, mensajes]) => {
    const sel = mapa[campo];
    if (!sel) return;
    const input = $(sel);
    input.addClass('is-invalid');
    input.siblings('.invalid-feedback').addClass('d-block').text(mensajes[0]);
  });
}

function mostrarInfoOrigen(mant) {
  if (!mant) {
    $('#baja-origen-info').addClass('d-none');
    $('#baja-mantenimiento').val('');
    return;
  }
  $('#baja-mantenimiento').val(mant.id_mantenimiento ?? '');
  $('#baja-origen-codigo').text(mant.codigo ?? '');
  $('#baja-origen-diagnostico').text(mant.diagnostico || '—');
  $('#baja-origen-resultado').text(mant.resultado || '—');
  $('#baja-origen-info').removeClass('d-none');
}

/**
 * Inicializa el modal. `onCreated(res)` se invoca tras registrar la baja
 * (el modal ya se cerró y se mostró el aviso); úsalo para refrescar tablas.
 */
export function initNuevaBajaModal({ onCreated } = {}) {
  const modalEl = document.getElementById('modalNuevaBaja');
  if (!modalEl) return;

  const formEl = document.getElementById('form-nueva-baja');
  const $form = $(formEl);
  const $btn = $('#btn-guardar-baja');
  const $spinner = $btn.find('.spinner-border');

  // Al elegir activo (registro manual): vincular el mantenimiento que recomendó baja, si existe.
  $('#baja-activo').off('change.baja').on('change.baja', function () {
    const mants = (window.mantsBaja ?? {})[$(this).val()] ?? [];
    mostrarInfoOrigen(mants.length ? mants[0] : null);
  });

  $form.off('submit.baja').on('submit.baja', function (e) {
    e.preventDefault();
    limpiarErrores($form);
    $btn.prop('disabled', true);
    $spinner.removeClass('d-none');

    $.ajax({
      url: window.routesBajas.store,
      type: 'POST',
      data: new FormData(formEl),
      processData: false,
      contentType: false,
      headers: { 'X-CSRF-TOKEN': csrf() },
      success: res => {
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        Swal.fire({ icon: 'success', title: 'Listo', text: res.message, timer: 2400, showConfirmButton: false });
        if (onCreated) onCreated(res);
      },
      error: xhr => {
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          mostrarErrores($form, xhr.responseJSON.errors);
          return;
        }
        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'No se pudo registrar la baja.' });
      },
      complete: () => {
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');
      }
    });
  });

  // Al cerrar el modal, limpiar para no arrastrar la precarga de un mantenimiento.
  $(modalEl).off('hidden.bs.modal.baja').on('hidden.bs.modal.baja', function () {
    formEl.reset();
    limpiarErrores($form);
    mostrarInfoOrigen(null);
  });
}

/**
 * Precarga el modal a partir del prefill devuelto por MantenimientoController::finalizar()
 * y lo abre. El activo y el mantenimiento de origen quedan fijados.
 */
export function prefillBajaDesdeMantenimiento(prefill) {
  if (!prefill) return;
  const modalEl = document.getElementById('modalNuevaBaja');
  if (!modalEl) return;

  const formEl = document.getElementById('form-nueva-baja');
  formEl.reset();

  // Activo fijado: se deja únicamente su opción (funcionalmente bloqueado).
  const $activo = $('#baja-activo');
  $activo.html(
    `<option value="${prefill.id_activo}">${prefill.codigo_activo ?? 'Activo ' + prefill.id_activo}</option>`
  );
  $activo.val(String(prefill.id_activo));

  $('#baja-mantenimiento').val(prefill.id_mantenimiento ?? '');
  $('#baja-causal').val(prefill.causal_sugerida ?? 'DANO_IRREPARABLE');

  mostrarInfoOrigen({
    id_mantenimiento: prefill.id_mantenimiento,
    codigo: prefill.codigo_mantenimiento,
    diagnostico: prefill.diagnostico,
    resultado: prefill.resultado
  });

  // Sugerir el motivo con el resultado del mantenimiento (editable por el usuario).
  if (prefill.resultado) $('#baja-motivo').val(prefill.resultado);

  bootstrap.Modal.getOrCreateInstance(modalEl).show();
}
