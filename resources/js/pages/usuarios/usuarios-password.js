import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const modal = $('#modalCambiarPassword');
  const form = $('#formCambiarPassword');
  const btn = $('#btnCambiarPassword');
  const spinner = btn.find('.spinner-border');

  // Abrir modal con datos del usuario
  $('#miTablaUsuarios').on('click', '.btn-cambiar-pwd', function () {
    const b = $(this);

    limpiarErrores();
    form.find('input[type="password"]').val('');

    $('#pwd-nombre-usuario').text(b.data('nombre-usuario'));

    const url = window.routes.changePassword.replace('{id}', b.data('id'));
    form.attr('action', url);

    modal.modal('show');
  });

  modal.on('hidden.bs.modal', limpiarErrores);

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

        Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 2200,
          timerProgressBar: true
        }).fire({ icon: 'success', title: res.message });
      },

      error: function (xhr) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');

        if (xhr.status === 422) {
          const errors = xhr.responseJSON.errors;

          if (errors.nueva_contrasena) {
            $('.group').addClass('input-group-invalid');
            $('#error-nueva-pwd').text(errors.nueva_contrasena[0]);
          }
          if (errors.nueva_contrasena_confirmation) {
            $('#nueva_contrasena_confirmation').addClass('is-invalid');
            $('#error-confirm-pwd').text(errors.nueva_contrasena_confirmation[0]);
          }
          return;
        }

        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cambiar la contraseña.' });
      }
    });
  });

  function limpiarErrores() {
    $('#nueva_contrasena, #nueva_contrasena_confirmation').removeClass('is-invalid');
    $('.group').removeClass('input-group-invalid');
    $('#error-nueva-pwd, #error-confirm-pwd').text('');
  }
});
