import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const modal = $('#modalEditarSede');
  const form = $('#formEditarSede');
  const btn = $('#btnActualizarSede');
  const spinner = btn.find('.spinner-border');

  $('#miTablaSedes').on('click', '.btn-editar', function () {
    const b = $(this);
    limpiar();
    $('#edit-nombre-sede').val(b.data('nombre'));
    $('#edit-ubicacion').val(b.data('ubicacion'));
    form.attr('action', window.routes.update.replace('{id}', b.data('id')));
    form.data('row-id', b.data('id'));
    modal.modal('show');
  });

  modal.on('hidden.bs.modal', limpiar);

  form.on('submit', function (e) {
    e.preventDefault();
    limpiar();
    btn.prop('disabled', true);
    spinner.removeClass('d-none');

    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize() + '&_method=PUT',

      success(res) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        modal.modal('hide');
        const row = encontrarFila(form.data('row-id'));
        if (row) {
          const d = row.data();
          d.nombre_sede = res.data.nombre_sede;
          d.ubicacion = res.data.ubicacion;
          // row.data(d).draw(false);
          row.invalidate().draw(false);
        }
        Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 2200,
          timerProgressBar: true
        }).fire({ icon: 'success', title: res.message });
      },

      error(xhr) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        if (xhr.status === 422) {
          const errors = xhr.responseJSON.errors;
          Object.keys(errors).forEach(c => {
            const inp = form.find(`[name="${c}"]`);
            inp.addClass('is-invalid');
            inp.closest('.form-floating').find('.invalid-feedback').text(errors[c][0]);
          });
          return;
        }
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar la sede.' });
      }
    });
  });

  function encontrarFila(id) {
    let found = null;
    window.tablaSedes.rows().every(function () {
      if (this.data().id_sede == id) found = this;
    });
    return found;
  }

  function limpiar() {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
  }
});
