import $ from 'jquery';
import Swal from 'sweetalert2';
import { initTooltips } from '../../plugins/bootstrap-tooltips';

$(function () {
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  $('#miTablaRoles').on('click', '.btn-toggle-estado', function () {
    const btn         = $(this);
    const id          = btn.data('id');
    const estadoActual = btn.data('estado');
    const url         = btn.data('url');

    const accion = estadoActual === 'ACTIVO' ? 'desactivar' : 'activar';
    const color  = estadoActual === 'ACTIVO' ? '#d33' : '#28a745';

    Swal.fire({
      title: '¿Estás seguro?',
      text: `Vas a ${accion} el rol. Los usuarios con este rol podrían verse afectados.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: color,
      cancelButtonColor: '#8592a3',
      confirmButtonText: `Sí, ${accion}`,
      cancelButtonText: 'Cancelar',
    }).then(result => {
      if (!result.isConfirmed) return;

      btn.prop('disabled', true);

      $.ajax({
        url:  url,
        type: 'POST',

        success: function (res) {
          if (res.success) {
            const row  = window.tablaRoles.row(btn.closest('tr'));
            const data = row.data();
            data.estado = res.nuevo_estado;
            row.data(data).draw(false);

            initTooltips();

            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2200, timerProgressBar: true })
              .fire({ icon: 'success', title: res.message });
          }
        },

        error: function (xhr) {
          btn.prop('disabled', false);
          const msg = xhr.responseJSON?.message ?? 'Hubo un error al cambiar el estado.';
          Swal.fire({ icon: 'error', title: 'Oops...', text: msg });
        }
      });
    });
  });
});
