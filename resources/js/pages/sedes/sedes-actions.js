import $ from 'jquery';
import Swal from 'sweetalert2';
import { initTooltips } from '../../plugins/bootstrap-tooltips';

$(function () {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  $('#miTablaSedes').on('click', '.btn-toggle-estado', function () {
    const btn = $(this);
    const estadoActual = btn.data('estado');
    const accion = estadoActual === 'ACTIVO' ? 'desactivar' : 'activar';

    Swal.fire({
      title: '¿Estás seguro?',
      text: `Vas a ${accion} la sede.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: estadoActual === 'ACTIVO' ? '#d33' : '#28a745',
      cancelButtonColor: '#8592a3',
      confirmButtonText: `Sí, ${accion}`,
      cancelButtonText: 'Cancelar'
    }).then(result => {
      if (!result.isConfirmed) return;

      btn.prop('disabled', true);

      $.ajax({
        url: btn.data('url'),
        type: 'POST',

        success(res) {
          if (res.success) {
            const row = window.tablaSedes.row(btn.closest('tr'));
            const d = row.data();
            d.estado = res.nuevo_estado;
            row.invalidate().draw(false);

            // row.data(d).draw(false);
            initTooltips();
            Swal.mixin({
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 2200,
              timerProgressBar: true
            }).fire({ icon: 'success', title: res.message });
          }
        },

        error(xhr) {
          btn.prop('disabled', false);
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: xhr.responseJSON?.message ?? 'Error al cambiar el estado.'
          });
        }
      });
    });
  });
});
