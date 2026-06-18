import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const form = $('#formNuevoModelo');
  const modal = $('#modalNuevoModelo');
  const btn = $('#btnGuardarModelo');
  const spinner = btn.find('.spinner-border');

  modal.on('show.bs.modal', function () {
    limpiarErrores();
    form.trigger('reset');

    $('#modelo_id_marca').val(null).trigger('change');
    $('#modelo_id_categoria').val(null).trigger('change');
  });

  form.on('input change', 'input, select', function () {
    $(this).removeClass('is-invalid');
    $(this).closest('.form-floating').find('.invalid-feedback').text('');
    $(this).nextAll('.select2-container').find('.select2-selection').removeClass('is-invalid');
  });

  form.on('submit', function (e) {
    e.preventDefault();
    limpiarErrores();

    btn.prop('disabled', true);
    spinner.removeClass('d-none');

    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize(),

      success: function (res) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        modal.modal('hide');

        if (res.data) {
          window.tablaModelos.row.add(res.data).draw(false);
        }

        Swal.fire({
          icon: 'success',
          title: 'Modelo registrado',
          text: res.message,
          timer: 2000,
          showConfirmButton: false
        });
      },

      error: function (xhr) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');

        if (xhr.status === 422) {
          const errors = xhr.responseJSON.errors;
          Object.keys(errors).forEach(campo => {
            const input = form.find(`[name="${campo}"]`);
            input.addClass('is-invalid');
            input.closest('.form-floating').find('.invalid-feedback').text(errors[campo][0]);
            if (campo === 'id_marca' || campo === 'id_categoria') {
              input.nextAll('.select2-container').find('.select2-selection').addClass('is-invalid');
            }
          });
          return;
        }

        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo registrar el modelo.' });
      }
    });
  });

  function limpiarErrores() {
    form.find('.is-invalid').removeClass('is-invalid');
    $('.input-group-invalid').removeClass('input-group-invalid');
    form.find('.invalid-feedback').text('');
  }
});
