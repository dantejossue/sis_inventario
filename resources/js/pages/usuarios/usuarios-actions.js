import $ from 'jquery';
import Swal from 'sweetalert2';

import { initTooltips } from '../../plugins/bootstrap-tooltips';

$(function () {
  // Aseguramos que el token CSRF se envíe en todas las peticiones AJAX
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // function mostrarToastSuccess(mensaje) {
  //   const toastElement = document.getElementById('toastSuccess');
  //   const toastMessage = document.getElementById('toastSuccessMessage');

  //   if (!toastElement || !toastMessage) return;

  //   toastMessage.textContent = mensaje;

  //   const toast = bootstrap.Toast.getOrCreateInstance(toastElement, {
  //     delay: 2500,
  //     autohide: true
  //   });

  //   toast.show();
  // }

  // Delegación de eventos: Escuchamos el clic en cualquier botón de toggle-estado,
  // incluso si la tabla cambia de página
  $('#miTablaUsuarios').on('click', '.btn-toggle-estado', function () {
    const btn = $(this);
    const id = btn.data('id');
    const estadoActual = btn.data('estado');
    const url = btn.data('url');

    // Textos dinámicos para SweetAlert
    const accion = estadoActual === 'ACTIVO' ? 'desactivar' : 'activar';
    const colorConfirmacion = estadoActual === 'ACTIVO' ? '#d33' : '#28a745';

    Swal.fire({
      title: `¿Estás seguro?`,
      text: `Vas a ${accion} el acceso de este usuario al sistema.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: colorConfirmacion,
      cancelButtonColor: '#8592a3',
      confirmButtonText: `Sí, ${accion}`,
      cancelButtonText: 'Cancelar'
    }).then(result => {
      if (result.isConfirmed) {
        // Ponemos el botón en estado de carga (opcional)
        btn.prop('disabled', true);

        $.ajax({
          url: url,
          type: 'POST', // Usamos POST porque estamos modificando la base de datos
          success: function (response) {
            if (response.success) {
              // Actualizamos la variable global de DataTables
              // Encontramos la fila exacta donde hicimos clic
              const row = window.tablaUsuarios.row(btn.closest('tr'));
              const rowData = row.data();

              // Actualizamos el estado en la data de esa fila
              rowData.estado = response.nuevo_estado;

              // Redibujamos solo esa fila
              row.data(rowData).draw(false);

              // Refrescamos los tooltips de Bootstrap
              initTooltips();
              Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true,
                didOpen: toast => {
                  toast.onmouseenter = Swal.stopTimer;
                  toast.onmouseleave = Swal.resumeTimer;
                }
              }).fire({
                icon: 'success',
                title: response.message
              });
            }
          },
          error: function () {
            btn.prop('disabled', false);
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: 'Hubo un error al intentar cambiar el estado.'
            });
          }
        });
      }
    });
  });

  window.tablaUsuarios.on('draw.dt', initTooltips);

  initTooltips();
});
