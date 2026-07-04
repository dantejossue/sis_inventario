import $ from 'jquery';
import Swal from 'sweetalert2';
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';

/**
 * Ficha del activo (ver.blade.php): pestañas persistentes vía hash,
 * etiqueta con QR/código de barras reales y gestión de documentos adjuntos.
 */
$(function () {
  // ═══════════════════════════════════════════
  // Pestañas: activar desde el hash y actualizarlo al cambiar
  // ═══════════════════════════════════════════
  const hash = window.location.hash;
  if (hash) {
    const trigger = document.querySelector(`[data-bs-target="${hash}"]`);
    if (trigger) bootstrap.Tab.getOrCreateInstance(trigger).show();
  }

  document.querySelectorAll('[data-bs-toggle="tab"]').forEach(el => {
    el.addEventListener('shown.bs.tab', e => {
      const target = e.target.getAttribute('data-bs-target');
      if (target) history.replaceState(null, '', target);
    });
  });

  // ═══════════════════════════════════════════
  // Etiqueta: QR y código de barras reales
  // ═══════════════════════════════════════════
  const cfg = window.routesFicha || {};

  if (cfg.qrUrl) {
    const qrBox = document.getElementById('etiqueta-qr');
    if (qrBox) {
      QRCode.toCanvas(cfg.qrUrl, { width: 90, margin: 1 }, (err, canvas) => {
        if (!err && canvas) {
          qrBox.innerHTML = '';
          qrBox.appendChild(canvas);
        }
      });
    }
  }

  const barcodeSvg = document.getElementById('etiqueta-barcode');
  if (barcodeSvg && cfg.codigoPatrimonial) {
    try {
      JsBarcode(barcodeSvg, cfg.codigoPatrimonial, {
        format: 'CODE128',
        displayValue: false,
        height: 34,
        width: 1.4,
        margin: 0
      });
    } catch (e) {
      // Código no representable: se mantiene el texto plano de la etiqueta.
    }
  }

  // ═══════════════════════════════════════════
  // Documentos adjuntos
  // ═══════════════════════════════════════════

  // Si el POST de subida devolvió errores de validación, reabrir el modal.
  if (window.reabrirModalDocumento) {
    bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#tab-documentos"]')).show();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSubirDocumento')).show();
  }

  $(document).on('click', '.btn-eliminar-doc', function () {
    const id = $(this).data('id');
    const nombre = $(this).data('nombre');

    Swal.fire({
      icon: 'warning',
      title: '¿Eliminar documento?',
      html: `Se eliminará <strong>${nombre}</strong> y su archivo adjunto. Esta acción no se puede deshacer.`,
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(result => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: cfg.documentosDestroy.replace('{id}', id),
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function () {
          window.location.hash = '#tab-documentos';
          window.location.reload();
        },
        error: function () {
          Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar el documento.' });
        }
      });
    });
  });
});
