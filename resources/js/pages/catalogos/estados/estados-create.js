import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  const form    = $('#formNuevoEstado');
  const modal   = $('#modalNuevoEstado');
  const btn     = $('#btnGuardarEstado');
  const spinner = btn.find('.spinner-border');
  const label   = $('#modalNuevoEstadoLabel');

  // Detectar tipo según qué botón abrió el modal
  $('[data-bs-target="#modalNuevoEstado"]').on('click', function () {
    const tipo = $(this).data('tipo');
    $('#nuevo_tipo_estado').val(tipo);

    if (tipo === 'CONDICION') {
      label.html('<i class="bx bx-health me-2"></i>Nueva Condición');
    } else {
      label.html('<i class="bx bx-map-alt me-2"></i>Nueva Situación');
    }
  });

  modal.on('show.bs.modal', function () {
    limpiarErrores();
    form.find('input[type="text"]').val('');
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
          const tipo = res.data.tipo_estado;
          if (tipo === 'CONDICION') {
            window.tablaCondiciones.row.add(res.data).draw(false);
          } else {
            window.tablaSituaciones.row.add(res.data).draw(false);
          }
        }

        Swal.fire({
          icon: 'success',
          title: 'Estado registrado',
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

        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo registrar el estado.' });
      }
    });
  });

  function limpiarErrores() {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
  }
});
