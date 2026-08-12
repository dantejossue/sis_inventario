import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const modal = $('#modalEditarUsuario');
  const form = $('#formEditarUsuario');
  const btn = $('#btnActualizarUsuario');
  const spinner = btn.find('.spinner-border');

  // Abrir modal con datos de la fila
  $('#miTablaUsuarios').on('click', '.btn-editar', function () {
    const b = $(this);

    limpiarErrores();

    $('#edit-nombre-colaborador').text(b.data('nombre-colaborador'));
    $('#edit-nombre-usuario').val(b.data('nombre-usuario'));
    $('#editSelectRol').val(b.data('id-rol')).trigger('change');

    const url = window.routes.update.replace('{id}', b.data('id'));
    form.attr('action', url);
    form.data('row-id', b.data('id'));

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
      data: form.serialize() + '&_method=PUT',

      success: function (res) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        modal.modal('hide');

        // Actualizar fila en DataTable
        const row = encontrarFila(form.data('row-id'));
        if (row) {
          const data = row.data();
          data.nombre_usuario = res.data.nombre_usuario;
          data.id_rol = res.data.id_rol;
          data.rol = res.data.rol;
          row.invalidate().draw(false);

          // row.data(data).draw(false);
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

        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el usuario.' });
      }
    });
  });

  function encontrarFila(id) {
    let found = null;
    window.tablaUsuarios.rows().every(function () {
      if (this.data().id_usuario == id) found = this;
    });
    return found;
  }

  function limpiarErrores() {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
  }
});
