import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const modal      = $('#modalPermisos');
  const loading    = $('#permisos-loading');
  const body       = $('#permisos-body');
  const btn        = $('#btnGuardarPermisos');
  const spinner    = btn.find('.spinner-border');

  let rolActualId = null;

  $('#miTablaRoles').on('click', '.btn-permisos', function () {
    const b = $(this);
    rolActualId = b.data('id');

    $('#permisos-rol-nombre').text(b.data('nombre'));
    loading.removeClass('d-none');
    body.addClass('d-none');

    modal.modal('show');

    $.ajax({
      url:  window.routes.permisos.replace('{id}', rolActualId),
      type: 'GET',

      success: function (res) {
        // Limpiar y marcar los permisos asignados
        $('.permiso-check').prop('checked', false);
        res.asignados.forEach(id => {
          $(`#perm_${id}`).prop('checked', true);
        });

        loading.addClass('d-none');
        body.removeClass('d-none');
      },

      error: function () {
        modal.modal('hide');
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los permisos.' });
      }
    });
  });

  modal.on('hidden.bs.modal', function () {
    rolActualId = null;
    loading.removeClass('d-none');
    body.addClass('d-none');
  });

  btn.on('click', function () {
    if (!rolActualId) return;

    const seleccionados = $('.permiso-check:checked').map(function () {
      return $(this).val();
    }).get();

    btn.prop('disabled', true);
    spinner.removeClass('d-none');

    $.ajax({
      url:  window.routes.permisos.replace('{id}', rolActualId),
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        _method: 'POST',
        permisos: seleccionados,
      },

      success: function (res) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        modal.modal('hide');

        // Actualizar contador en la tabla
        const row = encontrarFila(rolActualId);
        if (row) {
          const data = row.data();
          data.permisos_count = res.permisos_count;
          row.data(data).draw(false);
        }

        Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2200, timerProgressBar: true })
          .fire({ icon: 'success', title: res.message });
      },

      error: function () {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron guardar los permisos.' });
      }
    });
  });

  function encontrarFila(id) {
    let found = null;
    window.tablaRoles.rows().every(function () {
      if (this.data().id_rol == id) found = this;
    });
    return found;
  }
});
