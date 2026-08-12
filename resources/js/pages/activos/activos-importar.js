import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const modalEl = document.getElementById('modalImportarExcel');
  if (!modalEl) return;

  const $modal = $(modalEl);
  const $form = $('#formImportarExcel');
  const $btn = $('#btnConfirmarImportar');
  const $spinner = $btn.find('.spinner-border');
  const $archivo = $('#importar-archivo');
  const $resultado = $('#importar-resultado');
  const $resumen = $('#importar-resumen');
  const $errores = $('#importar-errores');

  let huboImportacion = false;

  function limpiarErrorArchivo() {
    $archivo.removeClass('is-invalid');
    $archivo.closest('.mb-3').find('.invalid-feedback').text('');
  }

  function marcarErrorArchivo(msg) {
    $archivo.addClass('is-invalid');
    $archivo.closest('.mb-3').find('.invalid-feedback').text(msg);
  }

  // Estado limpio cada vez que se abre el modal (deja los resultados de la
  // importación anterior visibles solo mientras el modal permanece abierto).
  $modal.on('show.bs.modal', function () {
    $form[0].reset();
    limpiarErrorArchivo();
    $resultado.addClass('d-none');
    $resumen.empty();
    $errores.empty();
    huboImportacion = false;
  });

  // La tabla se recarga recién al cerrar el modal (no de inmediato) para que
  // el usuario pueda leer el detalle de errores antes de perder la vista.
  $modal.on('hidden.bs.modal', function () {
    if (huboImportacion) window.location.reload();
  });

  function renderResultado(res) {
    const r = res.resumen || {};
    const creados = r.creados || 0;
    const conErrores = r.con_errores || 0;

    const clase = creados > 0 ? (conErrores > 0 ? 'alert-warning' : 'alert-success') : 'alert-danger';
    const icono = creados > 0 ? (conErrores > 0 ? 'bx-error' : 'bx-check-circle') : 'bx-x-circle';

    $resumen.html(`
      <div class="alert ${clase} d-flex align-items-center mb-3">
        <i class="bx ${icono} fs-4 me-2"></i>
        <div>
          <strong>${creados}</strong> activo(s) importado(s) correctamente de <strong>${r.total || 0}</strong> fila(s) procesada(s).
          ${conErrores ? ` <strong>${conErrores}</strong> fila(s) con errores (detalle abajo).` : ''}
        </div>
      </div>
    `);

    if (res.errores && res.errores.length) {
      const filas = res.errores
        .map(e => `<tr><td class="text-nowrap">Fila ${e.fila}</td><td>${e.motivo}</td></tr>`)
        .join('');
      $errores.html(`
        <div class="table-responsive" style="max-height:260px;overflow-y:auto;">
          <table class="table table-sm table-striped mb-0">
            <thead><tr><th style="width:80px;">Fila</th><th>Motivo</th></tr></thead>
            <tbody>${filas}</tbody>
          </table>
        </div>
      `);
    } else {
      $errores.empty();
    }

    $resultado.removeClass('d-none');
  }

  $form.on('submit', function (e) {
    e.preventDefault();
    limpiarErrorArchivo();

    const archivo = $archivo[0].files[0];
    if (!archivo) {
      marcarErrorArchivo('Selecciona un archivo Excel.');
      return;
    }

    const fd = new FormData();
    fd.append('archivo', archivo);

    $btn.prop('disabled', true);
    $spinner.removeClass('d-none');
    $resultado.addClass('d-none');

    $.ajax({
      url: window.routes.importar,
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      data: fd,
      processData: false,
      contentType: false,

      success: function (res) {
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');

        const creoAlgo = (res.resumen?.creados || 0) > 0;
        huboImportacion = huboImportacion || creoAlgo;
        renderResultado(res);

        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: creoAlgo ? 'success' : 'warning',
          title: creoAlgo ? 'Importación procesada' : 'No se importó ningún activo',
          timer: 3000,
          showConfirmButton: false
        });
      },

      error: function (xhr) {
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');

        const errores = xhr.responseJSON?.errors;
        const mensaje = errores?.archivo?.[0] || xhr.responseJSON?.message || 'No se pudo procesar el archivo.';
        marcarErrorArchivo(mensaje);
      }
    });
  });
});
