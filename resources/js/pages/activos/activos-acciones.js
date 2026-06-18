import $ from 'jquery';
import Swal from 'sweetalert2';
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';

$(function () {
  const condicionBadge = {
    BUENO:    'bg-success',
    REGULAR:  'bg-warning',
    MALO:     'bg-danger',
    OBSOLETO: 'bg-secondary',
  };

  const situacionBadge = {
    DISPONIBLE:       'bg-success',
    OPERATIVO:        'bg-success',
    EN_MANTENIMIENTO: 'bg-warning',
    EN_PRESTAMO:      'bg-info',
    ASIGNADO:         'bg-primary',
    EN_ALMACEN:       'bg-secondary',
    DADO_DE_BAJA:     'bg-danger',
  };

  const tipoUbicacionBadge = {
    EDIFICIO:    'bg-label-primary',
    PABELLON:    'bg-label-primary',
    PISO:        'bg-label-info',
    OFICINA:     'bg-label-secondary',
    AULA:        'bg-label-secondary',
    LABORATORIO: 'bg-label-warning',
    ALMACEN:     'bg-label-warning',
    OTRO:        'bg-label-dark',
  };

  // ═══════════════════════════════════════════
  // UBICACIÓN FÍSICA — Modal
  // ═══════════════════════════════════════════
  $(document).on('click', '.btn-ver-ubicacion', function () {
    const id = parseInt($(this).data('id'));
    const a  = window.activos.find(x => x.id_activo === id);
    if (!a) return;

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUbicacion'));

    if (!a.id_ubicacion) {
      $('#ubic-detalle').addClass('d-none');
      $('#ubic-sin').removeClass('d-none');
      modal.show();
      return;
    }

    $('#ubic-sin').addClass('d-none');
    $('#ubic-detalle').removeClass('d-none');

    $('#ubic-activo').text(`${a.codigo_interno} — ${a.modelo_nombre}`);
    $('#ubic-ruta').text(a.ubicacion_ruta || a.ubicacion_nombre || '—');
    $('#ubic-sede').text(a.sede_nombre && a.sede_nombre !== '—' ? a.sede_nombre : '—');
    $('#ubic-direccion').text(a.sede_direccion || '—');
    $('#ubic-nombre').text(a.ubicacion_nombre && a.ubicacion_nombre !== '—' ? a.ubicacion_nombre : '—');

    const tcls = tipoUbicacionBadge[a.ubicacion_tipo] ?? 'bg-label-secondary';
    $('#ubic-tipo').html(a.ubicacion_tipo ? `<span class="badge ${tcls}">${a.ubicacion_tipo}</span>` : '—');
    $('#ubic-codigo').html(a.ubicacion_codigo ? `<span class="badge bg-label-dark">${a.ubicacion_codigo}</span>` : '—');
    $('#ubic-descripcion').text(a.ubicacion_descripcion || '—');

    modal.show();
  });

  // ═══════════════════════════════════════════
  // MÁS INFO — Offcanvas
  // ═══════════════════════════════════════════
  $(document).on('click', '.btn-mas-info', function () {
    mostrarDetalle(parseInt($(this).data('id')));
  });

  function mostrarDetalle(id) {
    const activo = window.activos.find(a => a.id_activo === id);
    if (!activo) return;

    const $imagen = $('#info-imagen');
    if (activo.imagen_url) {
      $imagen.attr('src', activo.imagen_url).removeClass('d-none');
    } else {
      $imagen.attr('src', '').addClass('d-none');
    }

    $('#info-titulo').text(activo.codigo_interno);
    $('#info-codigo-interno').text(activo.codigo_interno);
    $('#info-codigo-patrimonial').text(activo.codigo_patrimonial);
    $('#info-marca').text(activo.marca_nombre);
    $('#info-modelo').text(activo.modelo_nombre);
    $('#info-categoria').text(activo.categoria_nombre !== '—' ? activo.categoria_nombre : '—');
    $('#info-serie').text(activo.numero_serie ?? '—');
    $('#info-responsable').text(activo.responsable_nombre ?? 'Sin asignar');

    const condCls = condicionBadge[activo.condicion_nombre] ?? 'bg-secondary';
    $('#info-condicion').html(`<span class="badge ${condCls}">${activo.condicion_nombre}</span>`);

    const sitCls = situacionBadge[activo.situacion_nombre] ?? 'bg-secondary';
    $('#info-situacion').html(`<span class="badge ${sitCls}">${activo.situacion_nombre.replace(/_/g, ' ')}</span>`);

    $('#info-fecha-adq').text(activo.fecha_adquisicion ?? '—');
    $('#info-valor').text(activo.valor_compra ? `S/. ${parseFloat(activo.valor_compra).toFixed(2)}` : '—');
    $('#info-proveedor').text(activo.proveedor ?? '—');
    $('#info-garantia-ini').text(activo.garantia_inicio ?? '—');
    $('#info-garantia-fin').text(activo.garantia_fin ?? '—');
    $('#info-descripcion').text(activo.descripcion ?? '—');
    $('#info-observaciones').text(activo.observaciones ?? '—');

    // ── Códigos de identificación: QR (URL a la ficha) + barras (cód. patrimonial)
    const qrBox = document.getElementById('info-qr');
    qrBox.innerHTML = '';
    if (activo.qr_url) {
      QRCode.toString(activo.qr_url, { type: 'svg', margin: 0 }, (err, svg) => {
        if (!err) qrBox.innerHTML = svg;
      });
    }

    const barcodeEl = document.getElementById('info-barcode');
    try {
      JsBarcode(barcodeEl, activo.codigo_patrimonial, {
        format: 'CODE128', displayValue: true, fontSize: 13, height: 40, margin: 0, width: 1.4,
      });
    } catch (e) {
      barcodeEl.innerHTML = '';
    }

    $('#info-btn-etiqueta').attr(
      'href',
      `${window.routes.etiquetas}?ids=${activo.id_activo}`
    );

    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('offcanvasMasInfo'));
    offcanvas.show();
  }


  // ═══════════════════════════════════════════
  // ELIMINAR
  // ═══════════════════════════════════════════
  $(document).on('click', '.btn-eliminar-activo', function () {
    const id     = parseInt($(this).data('id'));
    const codigo = $(this).data('codigo');

    Swal.fire({
      icon: 'warning',
      title: '¿Eliminar activo?',
      html: `Se eliminará el activo <strong>${codigo}</strong>. Esta acción no se puede deshacer.`,
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#d33',
    }).then(result => {
      if (!result.isConfirmed) return;

      $.ajax({
        url:  window.routes.destroy.replace('{id}', id),
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },

        success: function (res) {
          // Eliminar del array local
          window.activos = window.activos.filter(a => a.id_activo !== id);
          window.eliminarFilaActivo(id);

          // Limpiar de la selección si estaba marcado
          window.activosSeleccionados.delete(id);
          window.actualizarBulkBar();

          Swal.fire({
            icon: 'success',
            title: 'Eliminado',
            text: res.message,
            timer: 2000,
            showConfirmButton: false,
          });
        },

        error: function () {
          Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar el activo.' });
        }
      });
    });
  });


  // ═══════════════════════════════════════════
  // MOVER — Modal
  // ═══════════════════════════════════════════
  const modalMover  = $('#modalMover');
  const formMover   = $('#formMover');
  const btnMover    = $('#btnConfirmarMover');
  const spinnerMov  = btnMover.find('.spinner-border');

  // Qué campos exige/permite cada tipo de movimiento (espejo de MovimientoController).
  // colaborador / ubicacion: 'req' (obligatorio), 'opt' (opcional) o false (oculto).
  const MOV_CONFIG = {
    ASIGNAR:       { colaborador: 'req', ubicacion: 'opt', devolucion: false, ayuda: 'Asigna el activo a un colaborador (queda ASIGNADO).' },
    TRANSFERENCIA: { colaborador: 'req', ubicacion: 'opt', devolucion: false, ayuda: 'Cambia el colaborador responsable (y opcionalmente la ubicación).' },
    PRESTAMO:      { colaborador: 'req', ubicacion: 'opt', devolucion: true,  ayuda: 'Préstamo temporal; requiere fecha de devolución programada.' },
    DEVOLUCION:    { colaborador: false, ubicacion: 'opt', devolucion: false, ayuda: 'Devuelve el activo: queda DISPONIBLE y sin colaborador.' },
    REUBICACION:   { colaborador: false, ubicacion: 'req', devolucion: false, ayuda: 'Solo cambia la ubicación física del activo.' },
    BAJA:          { colaborador: false, ubicacion: false, devolucion: false, ayuda: 'Da de baja el activo (situación DADO_DE_BAJA).' }
  };

  let idsParaMover = [];

  function ocultarCamposMover() {
    $('#mover-colaborador-wrap, #mover-ubicacion-wrap, #mover-devolucion-wrap').addClass('d-none');
    $('#mover-tipo-ayuda').text('');
  }

  function abrirModalMover(ids) {
    idsParaMover = ids;
    formMover[0].reset();
    limpiarErroresMover();
    ocultarCamposMover();

    // Renderizar chips de activos seleccionados
    const lista = ids.map(id => {
      const a = window.activos.find(x => x.id_activo === id);
      return a
        ? `<span class="badge bg-label-primary">${a.codigo_interno}</span>`
        : `<span class="badge bg-label-secondary">#${id}</span>`;
    }).join('');
    $('#mover-lista-activos').html(lista);

    modalMover.modal('show');
  }

  // Desde dropdown individual
  $(document).on('click', '.btn-mover-activo', function () {
    const id = parseInt($(this).data('id'));
    abrirModalMover([id]);
  });

  // Desde barra bulk
  $(document).on('click', '#btn-mover-bulk', function () {
    if (window.activosSeleccionados.size === 0) return;
    abrirModalMover([...window.activosSeleccionados]);
  });

  // Mostrar/ocultar campos según el tipo de movimiento
  $(document).on('change', '#mover-tipo', function () {
    limpiarErroresMover();
    ocultarCamposMover();

    const cfg = MOV_CONFIG[$(this).val()];
    if (!cfg) return;

    $('#mover-tipo-ayuda').text(cfg.ayuda);
    if (cfg.colaborador) $('#mover-colaborador-wrap').removeClass('d-none');
    if (cfg.ubicacion)   $('#mover-ubicacion-wrap').removeClass('d-none');
    if (cfg.devolucion)  $('#mover-devolucion-wrap').removeClass('d-none');
  });

  function marcarError(sel, msg) {
    const el = $(sel);
    el.addClass('is-invalid');
    el.closest('.form-floating').find('.invalid-feedback').text(msg);
  }

  // Submit mover
  formMover.on('submit', function (e) {
    e.preventDefault();
    limpiarErroresMover();

    const tipo       = $('#mover-tipo').val();
    const colabDest  = $('#mover-colaborador').val();
    const ubicDest   = $('#mover-ubicacion').val();
    const fechaDev   = $('#mover-devolucion').val();
    const motivo     = $('#mover-motivo').val();

    if (!tipo) {
      marcarError('#mover-tipo', 'Selecciona un tipo de movimiento.');
      return;
    }

    const cfg = MOV_CONFIG[tipo];
    if (cfg.colaborador === 'req' && !colabDest) {
      marcarError('#mover-colaborador', 'Debes seleccionar el colaborador destino.');
      return;
    }
    if (cfg.ubicacion === 'req' && !ubicDest) {
      marcarError('#mover-ubicacion', 'Debes seleccionar la ubicación destino.');
      return;
    }
    if (cfg.devolucion && !fechaDev) {
      marcarError('#mover-devolucion', 'Indica la fecha de devolución programada.');
      return;
    }

    btnMover.prop('disabled', true);
    spinnerMov.removeClass('d-none');

    $.ajax({
      url:  window.routes.mover,
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      data: {
        activo_ids:                  idsParaMover,
        tipo:                        tipo,
        id_colaborador_destino:      (cfg.colaborador && colabDest) ? colabDest : null,
        id_ubicacion_destino:        (cfg.ubicacion && ubicDest) ? ubicDest : null,
        fecha_devolucion_programada: cfg.devolucion ? fechaDev : null,
        motivo:                      motivo || null,
      },

      success: function (res) {
        btnMover.prop('disabled', false);
        spinnerMov.addClass('d-none');
        modalMover.modal('hide');

        // Actualizar filas en tabla y array local
        if (res.data && res.data.length) {
          res.data.forEach(updated => {
            const idx = window.activos.findIndex(a => a.id_activo === updated.id_activo);
            if (idx !== -1) window.activos[idx] = updated;
            window.actualizarFilaActivo(updated);
          });
        }

        // Limpiar selección
        idsParaMover.forEach(id => window.activosSeleccionados.delete(id));
        $('.row-check, #check-all').prop('checked', false);
        window.actualizarBulkBar();

        Swal.fire({
          icon: 'success',
          title: 'Movimiento registrado',
          text: res.message,
          timer: 2500,
          showConfirmButton: false,
        });
      },

      error: function (xhr) {
        btnMover.prop('disabled', false);
        spinnerMov.addClass('d-none');

        if (xhr.status === 422) {
          const errors = xhr.responseJSON?.errors ?? {};
          Object.keys(errors).forEach(campo => {
            const input = formMover.find(`[name="${campo}"]`);
            input.addClass('is-invalid');
            input.closest('.form-floating').find('.invalid-feedback').text(errors[campo][0]);
          });
          return;
        }

        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo registrar el movimiento.' });
      }
    });
  });

  function limpiarErroresMover() {
    formMover.find('.is-invalid').removeClass('is-invalid');
    formMover.find('.invalid-feedback').text('');
  }


  // ═══════════════════════════════════════════
  // ETIQUETAS — Imprimir seleccionados
  // ═══════════════════════════════════════════
  $(document).on('click', '#btn-etiquetas-bulk', function (e) {
    e.preventDefault();
    if (window.activosSeleccionados.size === 0) return;
    const ids = [...window.activosSeleccionados].join(',');
    window.open(`${window.routes.etiquetas}?ids=${ids}`, '_blank');
  });


  // ═══════════════════════════════════════════
  // ESCANEO LÁSER — Seleccionar activo por código
  // ═══════════════════════════════════════════
  // El lector láser "teclea" el código y un Enter. Buscamos el activo por su
  // código patrimonial o interno y marcamos su fila para acciones (ej. Mover).
  $(document).on('keydown', '#scan-input', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();

    const valor = $(this).val().trim().toUpperCase();
    if (!valor) return;

    const activo = window.activos.find(
      a => (a.codigo_patrimonial || '').toUpperCase() === valor
        || (a.codigo_interno || '').toUpperCase() === valor
    );

    if (!activo) {
      $(this).addClass('is-invalid');
      Swal.fire({
        icon: 'warning', title: 'No encontrado',
        text: `Ningún activo coincide con el código «${valor}».`,
        timer: 1800, showConfirmButton: false,
      });
      $(this).val('').focus();
      return;
    }

    $(this).removeClass('is-invalid');
    window.activosSeleccionados.add(activo.id_activo);

    // Reflejar el check en la fila visible y resaltarla brevemente
    window.tablaActivos.rows().every(function () {
      if (this.data().id_activo === activo.id_activo) {
        const $node = $(this.node());
        $node.find('.row-check').prop('checked', true);
        $node.addClass('flash-scan');
        setTimeout(() => $node.removeClass('flash-scan'), 1200);
      }
    });

    window.actualizarBulkBar();
    $(this).val('').focus();
  });

  $(document).on('click', '#scan-clear', function () {
    $('#scan-input').val('').removeClass('is-invalid').focus();
  });


  // ═══════════════════════════════════════════
  // Apertura automática de ficha tras escanear un QR (?ver=ID)
  // ═══════════════════════════════════════════
  const verId = new URLSearchParams(window.location.search).get('ver');
  if (verId) {
    // Espera a que la tabla y los datos estén listos antes de abrir la ficha
    setTimeout(() => mostrarDetalle(parseInt(verId)), 300);
  }
});
