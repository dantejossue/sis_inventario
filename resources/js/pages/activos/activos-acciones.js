import $ from 'jquery';
import Swal from 'sweetalert2';
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';

$(function () {
  const condicionBadge = {
    NUEVO: 'bg-primary',
    BUENO: 'bg-success',
    REGULAR: 'bg-warning',
    MALO: 'bg-danger'
  };

  const situacionBadge = {
    DISPONIBLE: 'bg-success',
    EN_USO: 'bg-primary',
    EN_PRESTAMO: 'bg-info',
    EN_MANTENIMIENTO: 'bg-warning',
    EN_PROVEEDOR: 'bg-warning',
    OBSERVADO: 'bg-danger',
    DADO_DE_BAJA: 'bg-secondary'
  };

  const tipoUbicacionBadge = {
    EDIFICIO: 'bg-label-primary',
    PABELLON: 'bg-label-primary',
    PISO: 'bg-label-info',
    OFICINA: 'bg-label-secondary',
    AULA: 'bg-label-secondary',
    LABORATORIO: 'bg-label-warning',
    ALMACEN: 'bg-label-warning',
    OTRO: 'bg-label-dark'
  };

  // ═══════════════════════════════════════════
  // UBICACIÓN FÍSICA — Modal
  // ═══════════════════════════════════════════
  $(document).on('click', '.btn-ver-ubicacion', function () {
    const id = parseInt($(this).data('id'));
    const a = window.activos.find(x => x.id_activo === id);
    if (!a) return;

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUbicacion'));

    const ruta = a.ubicacion_ruta ?? '';
    const partes = ruta.split(' › ');
    const ultimo = partes.pop();

    if (!a.id_ubicacion) {
      $('#ubic-detalle').addClass('d-none');
      $('#ubic-sin').removeClass('d-none');
      modal.show();
      return;
    }

    $('#ubic-sin').addClass('d-none');
    $('#ubic-detalle').removeClass('d-none');

    $('#ubic-activo').text(`${a.codigo_interno} — ${a.modelo_nombre}`);
    $('#ubic-ruta').html(
      partes.length ? `${partes.join(' › ')} › <strong>${ultimo}</strong>` : `<strong>${ultimo}</strong>`
    );
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

    const condCls = condicionBadge[activo.condicion_actual] ?? 'bg-secondary';
    $('#info-condicion').html(`<span class="badge ${condCls}">${activo.condicion_nombre}</span>`);

    const sitCls = situacionBadge[activo.situacion_actual] ?? 'bg-secondary';
    $('#info-situacion').html(`<span class="badge ${sitCls}">${activo.situacion_nombre}</span>`);

    $('#info-fecha-adq').text(activo.fecha_adquisicion ?? '—');
    $('#info-valor').text(activo.valor_compra ? `S/. ${parseFloat(activo.valor_compra).toFixed(2)}` : '—');
    $('#info-proveedor').text(activo.proveedor ?? '—');
    $('#info-garantia-ini').text(activo.garantia_inicio ?? '—');
    $('#info-garantia-fin').text(activo.garantia_fin ?? '—');
    $('#info-descripcion').text(activo.descripcion ?? '—');
    $('#info-observaciones').text(activo.observaciones ?? '—');

    // ── Ficha Técnica TI (solo categorías que la requieren) ───────────────────
    const $ficha = $('#info-ficha-tecnica');
    const t = activo.tecnico;
    if (activo.requiere_ficha && t) {
      const fila = (label, val) =>
        val
          ? `<div class="col-6"><p class="text-muted small mb-0">${label}</p><p class="fw-semibold mb-0">${val}</p></div>`
          : '';
      const eo = t.estado_operativo ? t.estado_operativo.replace(/_/g, ' ') : null;
      const disco = [t.almacenamiento, t.tipo_almacenamiento].filter(Boolean).join(' ');
      const cuerpo =
        fila('Procesador', t.procesador) +
        fila('RAM', t.memoria_ram) +
        fila('Almacenamiento', disco) +
        fila('Sistema Operativo', t.sistema_operativo) +
        fila('Estado Operativo', eo) +
        fila('Nombre de Equipo', t.nombre_equipo) +
        fila('IP', t.direccion_ip) +
        fila('MAC', t.direccion_mac) +
        fila('Dominio', t.dominio) +
        fila('Licencia Office', t.licencia_office) +
        fila('Antivirus', t.antivirus) +
        fila('Accesorios', t.accesorios) +
        (t.observaciones_tecnicas
          ? `<div class="col-12"><p class="text-muted small mb-0">Obs. técnicas</p><p class="mb-0">${t.observaciones_tecnicas}</p></div>`
          : '');
      $ficha
        .html(
          '<hr class="my-1"><div class="col-12"><p class="text-muted small mb-2 text-uppercase fw-semibold">' +
            '<i class="bx bx-chip me-1"></i> Ficha Técnica TI</p></div>' +
            (cuerpo ||
              '<div class="col-12"><p class="text-muted small mb-0 fst-italic">Sin datos técnicos cargados.</p></div>')
        )
        .removeClass('d-none');
    } else {
      $ficha.addClass('d-none').empty();
    }

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
        format: 'CODE128',
        displayValue: true,
        fontSize: 13,
        height: 40,
        margin: 0,
        width: 1.4
      });
    } catch (e) {
      barcodeEl.innerHTML = '';
    }

    $('#info-btn-etiqueta').attr('href', `${window.routes.etiquetas}?ids=${activo.id_activo}`);

    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('offcanvasMasInfo'));
    offcanvas.show();
  }

  // ═══════════════════════════════════════════
  // ELIMINAR
  // ═══════════════════════════════════════════
  $(document).on('click', '.btn-eliminar-activo', function () {
    const id = parseInt($(this).data('id'));
    const codigo = $(this).data('codigo');

    Swal.fire({
      icon: 'warning',
      title: '¿Eliminar activo?',
      html: `Se eliminará el activo <strong>${codigo}</strong>. Esta acción no se puede deshacer.`,
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#d33'
    }).then(result => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: window.routes.destroy.replace('{id}', id),
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
            showConfirmButton: false
          });
        },

        error: function () {
          Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar el activo.' });
        }
      });
    });
  });

  // ═══════════════════════════════════════════
  // FICHA — Modal
  // ═══════════════════════════════════════════
  const modalFicha = $('#modalFichaRapida');

  function abrirModalFicha(ids) {
    modalFicha.modal('show');
  }

  // ═══════════════════════════════════════════
  // ETIQUETA — Modal
  // ═══════════════════════════════════════════
  const modalEtiqueta = $('#modalEtiqueta');

  function abrirModalEtiqueta(id) {
    const activo = window.activos.find(a => Number(a.id_activo) === Number(id));

    if (!activo) {
      Swal.fire({
        icon: 'error',
        title: 'Activo no encontrado',
        text: 'No se pudo cargar la información de la etiqueta.'
      });

      return;
    }

    const codigoInterno = activo.codigo_interno || 'SIN CÓDIGO';
    const codigoPatrimonial = activo.codigo_patrimonial || '';
    const numeroSerie = activo.numero_serie || 'Sin serie';
    const marca = activo.marca_nombre || 'Sin marca';
    const modelo = activo.modelo_nombre || '';

    // Llenar información textual
    $('#eti_cod_interno').text(codigoInterno);
    $('#eti_cod_patrimonial').text(codigoPatrimonial || 'Sin código');
    $('#eti_num_serie').text(numeroSerie);
    $('#eti_marca').text(`${marca} ${modelo}`.trim());

    // El código que utilizará el código de barras
    const valorBarcode = codigoPatrimonial || codigoInterno;

    $('#eti_barcode_text').text(valorBarcode);

    generarQrEtiqueta(activo);
    generarBarcodeEtiqueta(valorBarcode);

    modalEtiqueta.modal('show');
  }

  function generarQrEtiqueta(activo) {
    const qrBox = document.getElementById('eti-qr');

    if (!qrBox) return;

    qrBox.innerHTML = '';

    const urlQr = activo.qr_url || `${window.location.origin}${window.location.pathname}?ver=${activo.id_activo}`;

    QRCode.toString(
      urlQr,
      {
        type: 'svg',
        width: 90,
        margin: 0,
        errorCorrectionLevel: 'H'
      },
      function (error, svg) {
        if (error) {
          console.error('Error generando QR:', error);

          qrBox.innerHTML = `
          <i class="bx bx-error-circle text-danger fs-1"></i>
        `;

          return;
        }

        qrBox.innerHTML = svg;
      }
    );
  }

  function generarBarcodeEtiqueta(valor) {
    const barcodeEl = document.getElementById('eti-barcode');

    if (!barcodeEl) return;

    barcodeEl.innerHTML = '';

    if (!valor) {
      $('#eti-barcode-text').text('Sin código');
      return;
    }

    try {
      JsBarcode(barcodeEl, String(valor), {
        format: 'CODE128',
        displayValue: false,
        fontSize: 12,
        height: 42,
        margin: 0,
        width: 1.4
      });
    } catch (error) {
      console.error('Error generando código de barras:', error);

      barcodeEl.innerHTML = '';
      $('#eti-barcode-text').text('Código no válido');
    }
  }

  // ═══════════════════════════════════════════
  // MOVER — Modal
  // ═══════════════════════════════════════════
  const modalMover = $('#modalMover');
  const formMover = $('#formMover');
  const btnMover = $('#btnConfirmarMover');
  const spinnerMov = btnMover.find('.spinner-border');

  // Qué campos exige/permite cada tipo de movimiento (espejo de MovimientoController).
  // colaborador / ubicacion: 'req' (obligatorio), 'opt' (opcional) o false (oculto).
  // regulariza: muestra los selects de condición/situación (solo REGULARIZACION).
  const MOV_CONFIG = {
    PRESTAMO: {
      colaborador: 'req',
      ubicacion: false,
      devolucion: true,
      regulariza: false,
      ayuda: 'Préstamo temporal; requiere fecha estimada de devolución. El activo queda EN PRÉSTAMO.'
    },
    TRANSFERENCIA: {
      colaborador: 'req',
      ubicacion: 'opt',
      devolucion: false,
      regulariza: false,
      ayuda: 'Cambia el responsable (y opcionalmente la ubicación). El activo queda EN USO.'
    },
    REGULARIZACION: {
      colaborador: 'opt',
      ubicacion: 'opt',
      devolucion: false,
      regulariza: true,
      ayuda: 'Corrige responsable, ubicación, condición o situación con sustento. Exige motivo.'
    }
  };

  // Espejo de MovimientoController::OPERACIONES['origen']: situación ACTUAL
  // admitida como origen de cada tipo. Deshabilita en el modal lo que no aplique.
  const MOV_DESDE = {
    PRESTAMO: ['DISPONIBLE', 'EN_USO'],
    TRANSFERENCIA: ['DISPONIBLE', 'EN_USO'],
    REGULARIZACION: ['DISPONIBLE', 'EN_USO', 'EN_PRESTAMO', 'EN_MANTENIMIENTO', 'EN_PROVEEDOR', 'OBSERVADO']
  };

  let idsParaMover = [];

  // Habilita solo los tipos válidos para TODOS los activos seleccionados.
  // Devuelve true si queda al menos un movimiento posible.
  function actualizarTiposDisponibles(ids) {
    const situaciones = ids.map(id => window.activos.find(x => x.id_activo === id)?.situacion_actual).filter(Boolean);

    let hayValido = false;
    $('#mover-tipo option').each(function () {
      if (!this.value) return; // placeholder
      if (!this.dataset.label) this.dataset.label = this.textContent;
      const permitidas = MOV_DESDE[this.value] || [];
      const valido = situaciones.length > 0 && situaciones.every(s => permitidas.includes(s));
      this.disabled = !valido;
      this.textContent = valido ? this.dataset.label : `${this.dataset.label} — no aplica`;
      if (valido) hayValido = true;
    });
    return hayValido;
  }

  function ocultarCamposMover() {
    $('#mover-colaborador-wrap, #mover-ubicacion-wrap, #mover-devolucion-wrap, #mover-condicion-wrap, #mover-situacion-wrap').addClass('d-none');
    $('#mover-tipo-ayuda').text('');
  }

  function abrirModalMover(ids) {
    idsParaMover = ids;
    formMover[0].reset();
    limpiarErroresMover();
    ocultarCamposMover();

    // Renderizar chips de activos seleccionados
    const lista = ids
      .map(id => {
        const a = window.activos.find(x => x.id_activo === id);
        return a
          ? `<span class="badge bg-label-primary">${a.codigo_interno}</span>`
          : `<span class="badge bg-label-secondary">#${id}</span>`;
      })
      .join('');
    $('#mover-lista-activos').html(lista);

    // Ajustar los tipos según la situación de los activos seleccionados.
    const hayValido = actualizarTiposDisponibles(ids);
    $('#mover-tipo').val('');

    if (!hayValido) {
      const situaciones = [
        ...new Set(ids.map(id => window.activos.find(x => x.id_activo === id)?.situacion_actual).filter(Boolean))
      ]
        .map(s => s.replace(/_/g, ' '))
        .join(', ');
      Swal.fire({
        icon: 'info',
        title: 'Sin movimientos disponibles',
        html:
          `Por su situación actual (<strong>${situaciones || '—'}</strong>) no hay ningún movimiento ` +
          `aplicable a la selección. Revisa que no estén DADOS DE BAJA o que mezcles situaciones incompatibles.`
      });
      return;
    }

    modalMover.modal('show');
  }

  // Desde dropdown individual
  $(document).on('click', '.btn-ficha-rapida', function () {
    const id = parseInt($(this).data('id'));
    abrirModalFicha([id]);
  });

  $(document).on('click', '.btn-mover-activo', function () {
    const id = parseInt($(this).data('id'));
    abrirModalMover([id]);
  });

  $(document).on('click', '.btn-ver-etiqueta', function () {
    const id = parseInt($(this).data('id'));
    abrirModalEtiqueta([id]);
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
    if (cfg.ubicacion) $('#mover-ubicacion-wrap').removeClass('d-none');
    if (cfg.devolucion) $('#mover-devolucion-wrap').removeClass('d-none');
    if (cfg.regulariza) $('#mover-condicion-wrap, #mover-situacion-wrap').removeClass('d-none');
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

    const tipo = $('#mover-tipo').val();
    const colabDest = $('#mover-colaborador').val();
    const ubicDest = $('#mover-ubicacion').val();
    const fechaDev = $('#mover-devolucion').val();
    const condReg = $('#mover-condicion').val();
    const sitReg = $('#mover-situacion').val();
    const motivo = $('#mover-motivo').val();

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
      marcarError('#mover-devolucion', 'Indica la fecha estimada de devolución.');
      return;
    }
    if (cfg.regulariza) {
      if (!motivo) {
        marcarError('#mover-motivo', 'La regularización exige un motivo.');
        return;
      }
      if (!colabDest && !ubicDest && !condReg && !sitReg) {
        marcarError('#mover-motivo', 'Indica al menos un dato a regularizar (responsable, ubicación, condición o situación).');
        return;
      }
    }

    // Documento de sustento obligatorio.
    const documento = document.getElementById('mover-documento').files[0];
    if (!documento) {
      marcarError('#mover-documento', 'Adjunta el documento de sustento del movimiento.');
      return;
    }

    btnMover.prop('disabled', true);
    spinnerMov.removeClass('d-none');

    const fd = new FormData();
    idsParaMover.forEach(id => fd.append('activo_ids[]', id));
    fd.append('tipo', tipo);
    if (cfg.colaborador && colabDest) fd.append('id_colaborador_destino', colabDest);
    if (cfg.ubicacion && ubicDest) fd.append('id_ubicacion_destino', ubicDest);
    if (cfg.devolucion && fechaDev) fd.append('fecha_devolucion_estimada', fechaDev);
    if (cfg.regulariza && condReg) fd.append('condicion_actual', condReg);
    if (cfg.regulariza && sitReg) fd.append('situacion_actual', sitReg);
    if (motivo) fd.append('motivo', motivo);
    fd.append('tipo_documento', $('#mover-tipo-doc').val() || 'OTRO');
    fd.append('documento', documento);

    $.ajax({
      url: window.routes.mover,
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      data: fd,
      processData: false,
      contentType: false,

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
          showConfirmButton: false
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
          // Errores sin campo visible en el form (p. ej. la regla de situación
          // sobre 'activo_ids'): mostrarlos en un aviso para que no se pierdan.
          if (errors.activo_ids) {
            Swal.fire({ icon: 'warning', title: 'Movimiento no permitido', text: errors.activo_ids[0] });
          }
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
      a => (a.codigo_patrimonial || '').toUpperCase() === valor || (a.codigo_interno || '').toUpperCase() === valor
    );

    if (!activo) {
      $(this).addClass('is-invalid');
      Swal.fire({
        icon: 'warning',
        title: 'No encontrado',
        text: `Ningún activo coincide con el código «${valor}».`,
        timer: 1800,
        showConfirmButton: false
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
