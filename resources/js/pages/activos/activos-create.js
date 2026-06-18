import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  const form    = $('#formNuevoActivo');
  const modal   = $('#modalNuevoActivo');
  const btn     = $('#btnGuardarActivo');
  const spinner = btn.find('.spinner-border');

  modal.on('show.bs.modal', function () {
    limpiarErrores();
    form.trigger('reset');
    // Volver al tab de básicos
    modal.find('#tabNuevoActivo button:first-child').tab('show');
  });

  form.on('input change', 'input, select, textarea', function () {
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
          window.tablaActivos.row.add(res.data).draw(false);
        }

        Swal.fire({
          icon: 'success',
          title: 'Activo registrado',
          text: res.message,
          timer: 2200,
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

            // Abrir tab donde está el campo con error
            const tabPane = input.closest('.tab-pane');
            if (tabPane.length && !tabPane.hasClass('active')) {
              const targetId = '#' + tabPane.attr('id');
              modal.find(`[data-bs-target="${targetId}"]`).tab('show');
            }
          });
          return;
        }

        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo registrar el activo.' });
      }
    });
  });

  function limpiarErrores() {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
  }
});
