import $ from 'jquery';
import Swal from 'sweetalert2';
import { initTooltips } from '../../plugins/bootstrap-tooltips';

$(function () {
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  $('#tablaColaboradores').on('click', '.btn-toggle-colab', function () {
    const btn         = $(this);
    const id          = btn.data('id');
    const estadoActual = btn.data('estado');
    const url         = btn.data('url');
    const accion      = estadoActual === 'ACTIVO' ? 'desactivar' : 'activar';
    const colorBtn    = estadoActual === 'ACTIVO' ? '#d33' : '#28a745';

    Swal.fire({
      title: '¿Estás seguro?',
      text: `Vas a ${accion} a este colaborador.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: colorBtn,
      cancelButtonColor: '#8592a3',
      confirmButtonText: `Sí, ${accion}`,
      cancelButtonText: 'Cancelar',
    }).then(result => {
      if (!result.isConfirmed) return;

      btn.prop('disabled', true);

      $.ajax({
        url,
        type: 'POST',
        success: function (res) {
          if (!res.success) return;

          const row     = window.tablaColaboradores.row(btn.closest('tr'));
          const rowData = row.data();
          rowData.estado = res.nuevo_estado;
          row.data(rowData).draw(false);

          initTooltips();

          Swal.mixin({
            toast: true, position: 'top-end',
            showConfirmButton: false, timer: 2200, timerProgressBar: true,
          }).fire({ icon: 'success', title: res.message });
        },
        error: function () {
          btn.prop('disabled', false);
          Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cambiar el estado.' });
        }
      });
    });
  });

  window.tablaColaboradores.on('draw.dt', initTooltips);
  initTooltips();
});
