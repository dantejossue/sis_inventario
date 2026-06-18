import $ from 'jquery';
import Swal from 'sweetalert2';
import { filtrarPadresPorSede, upsertOpcionPadre } from './ubicaciones-padre-filter';

$(function () {
  const modal   = $('#modalEditarUbicacion');
  const form    = $('#formEditarUbicacion');
  const btn     = $('#btnActualizarUbicacion');
  const spinner = btn.find('.spinner-border');
  const sedeSel = $('#edit-id_sede');
  const padreSel = $('#edit-id_ubicacion_padre');

  let editId = null;

  $('#miTablaUbicaciones').on('click', '.btn-editar', function () {
    const b = $(this);
    limpiar();
    editId = b.data('id');

    sedeSel.val(b.data('sede'));
    $('#edit-nombre').val(b.data('nombre'));
    $('#edit-tipo').val(b.data('tipo'));
    $('#edit-codigo').val(b.data('codigo'));
    $('#edit-descripcion').val(b.data('descripcion'));

    // Filtra padres de la misma sede, excluyéndose a sí misma, y fija el actual.
    filtrarPadresPorSede(sedeSel, padreSel, editId);
    padreSel.val(b.data('padre') ? String(b.data('padre')) : '');

    form.attr('action', window.routes.update.replace('{id}', editId));
    modal.modal('show');
  });

  // Al cambiar la sede en edición, recalcula los padres válidos.
  sedeSel.on('change', () => filtrarPadresPorSede(sedeSel, padreSel, editId));

  modal.on('hidden.bs.modal', limpiar);

  form.on('input', 'input', function () {
    $(this).removeClass('is-invalid');
    $(this).closest('.form-floating').find('.invalid-feedback').text('');
  });

  form.on('submit', function (e) {
    e.preventDefault();
    limpiar();
    btn.prop('disabled', true); spinner.removeClass('d-none');

    $.ajax({
      url: form.attr('action'), type: 'POST',
      data: form.serialize() + '&_method=PUT',

      success(res) {
        btn.prop('disabled', false); spinner.addClass('d-none');
        modal.modal('hide');
        const row = encontrarFila(editId);
        if (row && res.data) {
          row.data(res.data).draw(false);
          upsertOpcionPadre(res.data);
        }
        Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2200, timerProgressBar: true })
          .fire({ icon: 'success', title: res.message });
      },

      error(xhr) {
        btn.prop('disabled', false); spinner.addClass('d-none');
        if (xhr.status === 422) {
          const errors = xhr.responseJSON.errors;
          Object.keys(errors).forEach(c => {
            const inp = form.find(`[name="${c}"]`);
            inp.addClass('is-invalid');
            inp.closest('.form-floating').find('.invalid-feedback').text(errors[c][0]);
          });
          return;
        }
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar la ubicación.' });
      }
    });
  });

  function encontrarFila(id) {
    let found = null;
    window.tablaUbicaciones.rows().every(function () {
      if (this.data().id_ubicacion == id) found = this;
    });
    return found;
  }

  function limpiar() {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
  }
});
