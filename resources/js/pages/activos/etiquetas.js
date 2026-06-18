import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';

// Renderiza, para cada activo, su QR (URL a la ficha) y su código de barras
// Code128 (código patrimonial) dentro de la etiqueta imprimible.
document.addEventListener('DOMContentLoaded', () => {
  const labels = window.etiquetas || [];

  labels.forEach((item, i) => {
    const qrEl = document.querySelector(`#qr-${i}`);
    if (qrEl && item.qr_url) {
      QRCode.toString(item.qr_url, { type: 'svg', margin: 0 }, (err, svg) => {
        if (!err) qrEl.innerHTML = svg;
      });
    }

    const barEl = document.querySelector(`#bar-${i}`);
    if (barEl && item.codigo_patrimonial) {
      try {
        JsBarcode(barEl, item.codigo_patrimonial, {
          format: 'CODE128',
          displayValue: true,
          fontSize: 13,
          height: 40,
          margin: 0,
          width: 1.6,
        });
      } catch (e) {
        /* El valor no se pudo codificar como código de barras */
      }
    }
  });
});
