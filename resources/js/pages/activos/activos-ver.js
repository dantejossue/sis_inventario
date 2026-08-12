import $ from 'jquery';
import Swal from 'sweetalert2';
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';
import languageES from '../../plugins/datatables-language-es';

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
      // Las tablas nacen en paneles ocultos: recalcular anchos al mostrarse.
      if ($.fn.dataTable) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
      }
    });
  });

  // ═══════════════════════════════════════════
  // Tablas de la ficha: buscador + paginación (5 por página, sin ordenamiento
  // para respetar el orden cronológico que ya trae el servidor).
  // ═══════════════════════════════════════════
  const dtFichaOpts = {
    searching: true,
    paging: true,
    info: true,
    ordering: false,
    pageLength: 5,
    lengthMenu: [
      [5, 10, 25],
      [5, 10, 25]
    ],
    language: languageES,
    autoWidth: false,
    dom:
      "<'row align-items-center mb-3'<'col-sm-6'l><'col-sm-6 d-flex justify-content-sm-end'f>>" +
      "<'row'<'col-12'tr>>" +
      "<'row align-items-center mt-3'<'col-sm-5'i><'col-sm-7 d-flex justify-content-sm-end'p>>"
  };

  ['#tabla-movimientos', '#tabla-mantenimientos', '#tabla-condicion'].forEach(sel => {
    if (document.querySelector(sel) && !$.fn.dataTable.isDataTable(sel)) {
      $(sel).DataTable(dtFichaOpts);
    }
  });

  // Si la ficha abrió directo en una pestaña con tabla (vía hash), ajustar anchos.
  if ($.fn.dataTable) {
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
  }

  // ═══════════════════════════════════════════
  // Listas no tabulares (documentos en tarjetas y trazabilidad en timeline):
  // buscador + filtro por tipo + límite de 5 con "ver más". Con búsqueda o
  // filtro activos se muestran todas las coincidencias (sin límite).
  // ═══════════════════════════════════════════
  function initListaFiltrable(cfg) {
    const items = cfg.items || [];
    if (!items.length) return;
    const limite = cfg.limite || 5;
    let expandido = false;

    function aplicar() {
      const q = (cfg.buscarInput?.value || '').trim().toLowerCase();
      const tipo = cfg.tipoSelect?.value || '';
      const filtroActivo = !!q || !!tipo;

      const coinciden = items.filter(it => {
        const okTexto = !q || (it.dataset.search || '').includes(q);
        const okTipo = !tipo || it.dataset.tipo === tipo;
        return okTexto && okTipo;
      });

      items.forEach(it => it.classList.add('d-none'));
      const visibles = filtroActivo || expandido ? coinciden : coinciden.slice(0, limite);
      visibles.forEach(it => it.classList.remove('d-none'));

      cfg.sinResultados?.classList.toggle('d-none', coinciden.length > 0);

      const ocultos = coinciden.length - visibles.length;
      if (cfg.verMasWrap) {
        cfg.verMasWrap.classList.toggle('d-none', !(ocultos > 0 && !filtroActivo));
        if (cfg.restantesEl) cfg.restantesEl.textContent = ocultos;
      }
    }

    cfg.buscarInput?.addEventListener('input', aplicar);
    cfg.tipoSelect?.addEventListener('change', aplicar);
    cfg.verMasBtn?.addEventListener('click', () => {
      expandido = true;
      aplicar();
    });
    aplicar();
  }

  // Documentos (tarjetas)
  initListaFiltrable({
    items: Array.from(document.querySelectorAll('#docs-grid .doc-item')),
    buscarInput: document.getElementById('docs-buscar'),
    sinResultados: document.getElementById('docs-sin-resultados'),
    verMasWrap: document.getElementById('docs-vermas-wrap'),
    verMasBtn: document.getElementById('docs-vermas'),
    restantesEl: document.getElementById('docs-restantes')
  });

  // Trazabilidad (timeline)
  initListaFiltrable({
    items: Array.from(document.querySelectorAll('.traza-item')),
    buscarInput: document.getElementById('traza-buscar'),
    tipoSelect: document.getElementById('traza-tipo'),
    sinResultados: document.getElementById('traza-sin-resultados'),
    verMasWrap: document.getElementById('traza-vermas-wrap'),
    verMasBtn: document.getElementById('traza-vermas'),
    restantesEl: document.getElementById('traza-restantes')
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
