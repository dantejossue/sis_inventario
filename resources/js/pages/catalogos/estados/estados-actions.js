import $ from 'jquery';
import Swal from 'sweetalert2';
import { initTooltips } from '../../../plugins/bootstrap-tooltips';

$(function () {
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  // Delegación en ambas tablas
  $(document).on('click', '.btn-toggle-estado', function () {
    const btn          = $(this);
    const estadoActual = btn.data('estado');
    const tablaKey     = btn.data('tabla');
    const accion       = estadoActual === 'ACTIVO' ? 'desactivar' : 'activar';
    const color        = estadoActual === 'ACTIVO' ? '#d33' : '#28a745';

    Swal.fire({
      title: '¿Estás seguro?',
      text: `Vas a ${accion} este estado.`,
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
        url:  btn.data('url'),
        type: 'POST',

        success(res) {
          if (res.success) {
            const tablaRef = tablaKey === 'condiciones' ? window.tablaCondiciones : window.tablaSituaciones;
            const row      = tablaRef.row(btn.closest('tr'));
            const d        = row.data();
            d.estado       = res.nuevo_estado;
            row.data(d).draw(false);
            initTooltips();
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2200, timerProgressBar: true })
              .fire({ icon: 'success', title: res.message });
          }
        },

        error(xhr) {
          btn.prop('disabled', false);
          Swal.fire({ icon: 'error', title: 'Oops...', text: xhr.responseJSON?.message ?? 'Error al cambiar el estado.' });
        }
      });
    });
  });
});
