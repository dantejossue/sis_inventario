import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const modal   = $('#modalEditarEstado');
  const form    = $('#formEditarEstado');
  const btn     = $('#btnActualizarEstado');
  const spinner = btn.find('.spinner-border');

  // Delegación en ambas tablas
  $(document).on('click', '.btn-editar-estado', function () {
    const b = $(this);

    limpiarErrores();
    $('#edit-nombre-estado').val(b.data('nombre'));
    $('#edit-descripcion-estado').val(b.data('descripcion'));
    $('#edit-tipo-estado').val(b.data('tipo'));

    const url = window.routes.update.replace('{id}', b.data('id'));
    form.attr('action', url);
    form.data('row-id', b.data('id'));
    form.data('tabla', b.data('tabla'));

    modal.modal('show');
  });

  modal.on('hidden.bs.modal', limpiarErrores);

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
      data: form.serialize() + '&_method=PUT',

      success: function (res) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        modal.modal('hide');

        const tabla = form.data('tabla');
        const tablaRef = tabla === 'condiciones' ? window.tablaCondiciones : window.tablaSituaciones;

        const row = encontrarFila(tablaRef, form.data('row-id'));
        if (row) {
          const data          = row.data();
          data.nombre         = res.data.nombre;
          data.descripcion    = res.data.descripcion;
          data.tipo           = res.data.tipo;
          row.data(data).draw(false);
        }

        Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2200, timerProgressBar: true })
          .fire({ icon: 'success', title: res.message });
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

        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el estado.' });
      }
    });
  });

  function encontrarFila(tabla, id) {
    let found = null;
    tabla.rows().every(function () {
      if (this.data().id_estado_activo == id) found = this;
    });
    return found;
  }

  function limpiarErrores() {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
  }
});
