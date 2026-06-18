import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const form    = $('#formNuevoRol');
  const modal   = $('#modalNuevoRol');
  const btn     = $('#btnGuardarRol');
  const spinner = btn.find('.spinner-border');

  modal.on('show.bs.modal', function () {
    limpiarErrores();
    form.trigger('reset');
  });

  form.on('input', 'input', function () {
    $(this).removeClass('is-invalid');
    $(this).closest('.form-floating').find('.invalid-feedback').text('');
  });

  form.on('submit', function (e) {
    e.preventDefault();
    limpiarErrores();

    btn.prop('disabled', true);
    spinner.removeClass('d-none');

    $.ajax({
      url:  form.attr('action'),
      type: 'POST',
      data: form.serialize(),

      success: function (res) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        modal.modal('hide');

        if (res.data) {
          window.tablaRoles.row.add(res.data).draw(false);
        }

        Swal.fire({
          icon: 'success',
          title: 'Rol registrado',
          text: res.message,
          timer: 2000,
          showConfirmButton: false,
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
          });
          return;
        }

        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo registrar el rol.' });
      }
    });
  });

  function limpiarErrores() {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
  }
});
