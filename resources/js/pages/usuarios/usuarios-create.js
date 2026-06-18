import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const form = $('#formNuevoUsuario');
  const modal = $('#modalNuevoUsuario');
  const btn = $('#btnGuardarUsuario');

  function limpiarErrores() {
    $('.is-invalid').removeClass('is-invalid');
    $('.input-group-invalid').removeClass('input-group-invalid');
    $('.invalid-feedback').text('');
  }
  modal.on('show.bs.modal', function () {
    limpiarErrores();
    form.trigger('reset');

    $('#selectColaborador').val(null).trigger('change');
    $('#selectRol').val(null).trigger('change');
  });

  function limpiarErrorCampo(input) {
    input.removeClass('is-invalid');

    input.closest('.form-floating').find('.invalid-feedback').text('');

    if (input.attr('name') === 'contrasena') {
      input.closest('.input-group').removeClass('input-group-invalid');

      input.closest('.form-password-toggle').find('.invalid-feedback').text('');
    }

    if (input.attr('name') === 'id_colaborador' || input.attr('name') === 'id_rol') {
      input.nextAll('.select2-container').find('.select2-selection').removeClass('is-invalid');
    }
  }

  form.on('input', 'input', function () {
    limpiarErrorCampo($(this));
  });

  // Selects normales y Select2
  form.on('change', 'select', function () {
    limpiarErrorCampo($(this));
  });

  form.on('submit', function (e) {
    e.preventDefault();

    limpiarErrores();

    btn.prop('disabled', true);

    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize(),

      success: function (response) {
        btn.prop('disabled', false);

        modal.modal('hide');

        form.trigger('reset');

        $('#selectColaborador').val(null).trigger('change');
        $('#selectRol').val(null).trigger('change');
        $('.select2-selection').removeClass('is-invalid');

        // Inputs de texto y password

        // Añadimos la nueva fila al DataTable en cliente
        if (response.data) {
          window.tablaUsuarios.row.add(response.data).draw(false);
        } else {
          // Fallback: si no viene data, recargar la página para garantizar consistencia
          window.location.reload();
        }

        Swal.fire({
          icon: 'success',
          title: 'Usuario registrado',
          text: response.message,
          timer: 2000,
          showConfirmButton: false
        });
      },

      error: function (xhr) {
        btn.prop('disabled', false);

        if (xhr.status === 422) {
          btn.prop('disabled', false);

          const errors = xhr.responseJSON.errors;

          Object.keys(errors).forEach(campo => {
            const input = $(`[name="${campo}"]`);

            input.addClass('is-invalid');

            if (campo === 'id_colaborador' || campo === 'id_rol') {
              input.nextAll('.select2-container').find('.select2-selection').addClass('is-invalid');
            }

            if (campo === 'contrasena') {
              input.closest('.input-group').addClass('input-group-invalid');

              input.closest('.form-password-toggle').find('.invalid-feedback').text(errors[campo][0]);

              return;
            }

            input.closest('.form-floating').find('.invalid-feedback').text(errors[campo][0]);
          });

          return;
        }

        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Ocurrió un problema al registrar el usuario'
        });
      }
    });
  });
});
