import $ from 'jquery';
import Swal from 'sweetalert2';
import { filtrarPadresPorSede, upsertOpcionPadre } from './ubicaciones-padre-filter';

$(function () {
  const form    = $('#formNuevaUbicacion');
  const modal   = $('#modalNuevaUbicacion');
  const btn     = $('#btnGuardarUbicacion');
  const spinner = btn.find('.spinner-border');
  const sedeSel = $('#id_sede');
  const padreSel = $('#id_ubicacion_padre');

  modal.on('show.bs.modal', () => {
    limpiar();
    form.trigger('reset');
    filtrarPadresPorSede(sedeSel, padreSel);
  });

  // Al cambiar la sede, solo se ofrecen ubicaciones superiores de esa sede.
  sedeSel.on('change', () => filtrarPadresPorSede(sedeSel, padreSel));

  form.on('input', 'input', function () {
    $(this).removeClass('is-invalid');
    $(this).closest('.form-floating').find('.invalid-feedback').text('');
  });

  form.on('submit', function (e) {
    e.preventDefault();
    limpiar();
    btn.prop('disabled', true);
    spinner.removeClass('d-none');

    $.ajax({
      url: form.attr('action'), type: 'POST', data: form.serialize(),

      success(res) {
        btn.prop('disabled', false); spinner.addClass('d-none');
        modal.modal('hide');
        if (res.data) {
          window.tablaUbicaciones.row.add(res.data).draw(false);
          upsertOpcionPadre(res.data);
        }
        Swal.fire({ icon: 'success', title: 'Ubicación registrada', text: res.message, timer: 2000, showConfirmButton: false });
      },

      error(xhr) {
        btn.prop('disabled', false); spinner.addClass('d-none');
        if (xhr.status === 422) {
          const errors = xhr.responseJSON.errors;
          Object.keys(errors).forEach(c => {
            const inp = form.find(`[name="${c}"]`);
            inp.addClass('is-invalid');
            inp.closest('.form-floating').find('.invalid-feedback').text(errors[c][0]);
          });
          return;
        }
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo registrar la ubicación.' });
      }
    });
  });

  function limpiar() {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
  }
});
