import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const modal = $('#modalEditarModelo');
  const form = $('#formEditarModelo');
  const btn = $('#btnActualizarModelo');
  const spinner = btn.find('.spinner-border');

  $('#miTablaModelos').on('click', '.btn-editar-modelo', function () {
    const b = $(this);

    limpiarErrores();
    $('#edit-modelo-id-marca').val(b.data('id-marca')).trigger('change');
    $('#edit-modelo-id-categoria').val(b.data('id-categoria')).trigger('change');
    $('#edit-modelo-nombre').val(b.data('nombre'));
    $('#edit-modelo-descripcion').val(b.data('descripcion'));

    const url = window.routes.updateModelo.replace('{id}', b.data('id'));
    form.attr('action', url);
    form.data('row-id', b.data('id'));

    modal.modal('show');
  });

  modal.on('hidden.bs.modal', limpiarErrores);

  form.on('input change', 'input, select', function () {
    $(this).removeClass('is-invalid');
    $(this).closest('.form-floating').find('.invalid-feedback').text('');
  });

  form.on('submit', function (e) {
    e.preventDefault();
    limpiarErrores();

    btn.prop('disabled', true);
    spinner.removeClass('d-none');

    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize() + '&_method=PUT',

      success: function (res) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        modal.modal('hide');

        const row = encontrarFila(form.data('row-id'));
        if (row) {
          const data = row.data();
          data.id_marca = res.data.id_marca;
          data.id_categoria = res.data.id_categoria;
          data.nombre = res.data.nombre;
          data.descripcion = res.data.descripcion;
          data.marca_nombre = res.data.marca_nombre;
          data.categoria_nombre = res.data.categoria_nombre;
          row.data(data).draw(false);
        }

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
          Object.keys(errors).forEach(campo => {
            const input = form.find(`[name="${campo}"]`);
            input.addClass('is-invalid');
            input.closest('.form-floating').find('.invalid-feedback').text(errors[campo][0]);
          });
          return;
        }

        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el modelo.' });
      }
    });
  });

  function encontrarFila(id) {
    let found = null;
    window.tablaModelos.rows().every(function () {
      if (this.data().id_modelo == id) found = this;
    });
    return found;
  }

  function limpiarErrores() {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
  }
});
