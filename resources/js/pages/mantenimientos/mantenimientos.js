import $ from 'jquery';
import Swal from 'sweetalert2';
import dtDefaults from '../../plugins/datatables-defaults';
import { initNuevaBajaModal, prefillBajaDesdeMantenimiento } from '../bajas/nueva-baja-modal';

/**
 * Módulo de mantenimientos (F5) — flujo simplificado:
 *   REGISTRADO → EN_ATENCION → FINALIZADO   (ruta alternativa: → CANCELADO)
 * El resultado técnico (OPERATIVO | RECOMENDADO_BAJA) es independiente del estado.
 * Cada avance crea un registro en el historial (mantenimiento_avances).
 */

const tipoBadge = {
  PREVENTIVO: 'bg-label-info',
  CORRECTIVO: 'bg-label-danger',
  // GARANTIA: 'bg-label-primary',
  REVISION_TECNICA: 'bg-label-warning'
};

const tipoTexto = {
  PREVENTIVO: 'Preventivo',
  CORRECTIVO: 'Correctivo',
  // GARANTIA: 'bg-label-primary',
  REVISION_TECNICA: 'Revisión técnica'
};

// ── Estados del proceso (nuevo flujo) ──────────────────────────────
const estadoBadge = {
  REGISTRADO: 'bg-label-primary',
  EN_ATENCION: 'bg-label-warning',
  FINALIZADO: 'bg-success',
  CANCELADO: 'bg-label-dark'
};

const estadoTexto = {
  REGISTRADO: 'Registrado',
  EN_ATENCION: 'En atención',
  FINALIZADO: 'Finalizado',
  CANCELADO: 'Cancelado'
};

// ── Resultado técnico (independiente del estado) ───────────────────
const resultadoBadge = {
  OPERATIVO: 'bg-label-success',
  RECOMENDADO_BAJA: 'bg-label-danger'
};

const resultadoTexto = {
  OPERATIVO: 'Equipo operativo',
  RECOMENDADO_BAJA: 'Recomendado para baja'
};

// ── Modalidad de atención ──────────────────────────────────────────
const modalidadBadge = {
  INTERNA_OTI: 'bg-label-info',
  GARANTIA_PROVEEDOR: 'bg-label-primary'
};

const modalidadTexto = {
  INTERNA_OTI: 'Atención interna OTI',
  GARANTIA_PROVEEDOR: 'Garantía / proveedor'
};

/*
 * ── Constantes del flujo anterior: FUERA DE USO ──
 * Se conservan comentadas como referencia histórica.
 *
 * const estadoBadge_ANTERIOR = { SOLICITADO, EN_REVISION, EN_MANTENIMIENTO, DERIVADO_PROVEEDOR, ATENDIDO, SIN_REPARACION, RECOMENDADO_BAJA, CERRADO, CANCELADO };
 * const estadoTexto_ANTERIOR = { ... };
 * const AVANCES = { SOLICITADO: [...], EN_REVISION: [...], EN_MANTENIMIENTO: [...], DERIVADO_PROVEEDOR: [...] };  // ya no hay transiciones manuales
 * const RESULTADOS = ['ATENDIDO', 'SIN_REPARACION', 'RECOMENDADO_BAJA'];
 * const prioridadBadge = { ... };   // prioridad eliminada
 * const origenTexto = { ... };      // origen eliminado
 */

const ABIERTOS = ['REGISTRADO', 'EN_ATENCION'];
const FINALES = ['FINALIZADO', 'CANCELADO'];

const dash = '<span class="text-muted">—</span>';
const legible = v => (v ?? '').replace(/_/g, ' ');
const fmtFecha = f => (f ? f.split('-').reverse().join('/') : null);
const fmtCosto = c => (c !== null && c !== undefined && c !== '' ? `S/ ${Number(c).toFixed(2)}` : null);
const csrf = () => $('meta[name="csrf-token"]').attr('content');

const buscar = id => window.mantenimientos.find(m => m.id_mantenimiento === id);

function extensionIcono(ext) {
  if (ext === 'pdf') return ['bxs-file-pdf', 'bg-label-danger'];
  if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) return ['bx-image', 'bg-label-primary'];
  if (['xls', 'xlsx'].includes(ext)) return ['bx-spreadsheet', 'bg-label-success'];
  return ['bx-file', 'bg-label-info'];
}

$(function () {
  // ═══════════════════════════════════════════
  // TABLA
  // ═══════════════════════════════════════════
  window.tablaMant = $('#miTablaMantenimientos').DataTable({
    ...dtDefaults,
    data: window.mantenimientos,
    order: [[0, 'desc']],
    columns: [
      {
        data: 'codigo',
        render: d => `<span class="fw-semibold d-block">${d}</span>`
      },
      {
        data: 'activo_patrimonial',
        render: (d, t, row) =>
          `<a href="${row.activo_url ?? '#'}" class="fw-semibold d-block">${d ?? '—'}</a>` +
          `<small class="text-muted">${row.activo_modelo || '—'}</small>`
      },
      {
        data: 'tipo',
        render: d => `<span class="badge ${tipoBadge[d] ?? 'bg-label-secondary'}">${legible(tipoTexto[d])}</span>`
      },
      // {
      //   data: 'descripcion',
      //   orderable: false,
      //   render: (d, t, row) => {
      //     const problema = d ? `<span class="d-block text-truncate" style="max-width: 240px;">${d}</span>` : dash;
      //     const diag = row.diagnostico
      //       ? `<small class="text-muted d-block text-truncate" style="max-width: 240px;">${row.diagnostico}</small>`
      //       : '';
      //     return problema + diag;
      //   }
      // },
      {
        data: 'tecnico',
        render: (d, t, row) => {
          if (!d && !row.proveedor) return '<span class="text-muted">Por asignar</span>';
          const tec = d ? `<span class="d-block">${d}</span>` : '';
          const prov = row.proveedor ? `<small class="text-muted">${row.proveedor}</small>` : '';
          return tec + prov;
        }
      },
      {
        data: 'modalidad',
        render: d =>
          `<span class="badge ${modalidadBadge[d] ?? 'bg-label-secondary'}">${modalidadTexto[d] ?? legible(d)}</span>`
      },
      {
        data: 'estado',
        render: d =>
          `<span class="badge ${estadoBadge[d] ?? 'bg-label-secondary'}">${estadoTexto[d] ?? legible(d)}</span>`
      },
      {
        data: 'costo',
        render: d => fmtCosto(d) ?? '<span class="text-muted">Pendiente</span>'
      },
      {
        data: 'fecha_reporte',
        render: (d, t, row) => {
          if (t === 'sort' || t === 'type') return d ?? '';
          return (
            `<span class="d-block">${fmtFecha(d) ?? '—'}</span>` +
            (row.fecha_fin ? `<small class="text-muted">Fin: ${fmtFecha(row.fecha_fin)}</small>` : '')
          );
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-end',
        render: row => {
          const abierto = ABIERTOS.includes(row.estado);

          let items = `
            <li>
              <a class="dropdown-item btn-detalle-mant d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_mantenimiento}">
                <i class="bx bx-show me-1"></i> Ver detalle
              </a>
            </li>`;

          if (abierto) {
            items += `
            <li>
              <a class="dropdown-item btn-avance-mant d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_mantenimiento}">
                <i class="bx bx-edit me-1"></i> Avance técnico
              </a>
            </li>`;

            // Finalizar solo desde EN_ATENCION (no se puede finalizar un REGISTRADO sin avance).
            if (row.estado === 'EN_ATENCION') {
              items += `
            <li>
              <a class="dropdown-item btn-finalizar-mant d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_mantenimiento}">
                <i class="bx bx-check-circle me-1"></i> Finalizar
              </a>
            </li>`;
            }

            items += `
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger btn-cancelar-mant d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_mantenimiento}">
                <i class="bx bx-x-circle me-1"></i> Cancelar
              </a>
            </li>`;
          }

          return `
            <div class="dropdown">
              <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                      data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">${items}</ul>
            </div>`;
        }
      }
    ],
    drawCallback: function () {
      // Menú con posicionamiento "fixed" para que no lo recorte el scroll de la tabla
      document.querySelectorAll('#miTablaMantenimientos [data-bs-toggle="dropdown"]').forEach(el => {
        bootstrap.Dropdown.getOrCreateInstance(el, { popperConfig: { strategy: 'fixed' } });
      });
    }
  });

  // ═══════════════════════════════════════════
  // KPIs
  // ═══════════════════════════════════════════
  function refrescarIndicadores() {
    const data = window.mantenimientos;
    const enAtencion = data.filter(m => m.estado === 'EN_ATENCION');
    const registrados = data.filter(m => m.estado === 'REGISTRADO');
    const finalizados = data.filter(m => m.estado === 'FINALIZADO');
    const mesActual = new Date().toISOString().slice(0, 7);

    // En atención
    $('#kpi-abiertos').text(enAtencion.length);
    $('#kpi-abiertos-detalle').text(`${registrados.length} registrados`);

    // Preventivos
    const preventivos = data.filter(m => m.tipo === 'PREVENTIVO');
    $('#kpi-preventivos').text(preventivos.length);
    $('#kpi-preventivos-detalle').text(`${preventivos.filter(m => ABIERTOS.includes(m.estado)).length} en curso`);

    // Correctivos
    const correctivos = data.filter(m => m.tipo === 'CORRECTIVO');
    $('#kpi-correctivos').text(correctivos.length);
    $('#kpi-correctivos-detalle').text(`${correctivos.filter(m => ABIERTOS.includes(m.estado)).length} en curso`);

    // Finalizados
    $('#kpi-finalizados').text(finalizados.length);
    $('#kpi-finalizados-detalle').text(
      `${finalizados.filter(m => (m.fecha_fin ?? '').startsWith(mesActual)).length} este mes`
    );

    // Panel de flujo/alertas y stats (#stat-*, #alertas-tecnicas) retirados de la interfaz: sin cálculo.
  }

  refrescarIndicadores();

  // ═══════════════════════════════════════════
  // FILTROS
  // ═══════════════════════════════════════════
  $.fn.dataTable.ext.search.push((settings, data, dataIndex) => {
    if (settings.nTable.id !== 'miTablaMantenimientos') return true;
    const row = window.tablaMant.row(dataIndex).data();

    const tipo = $('#filtro-tipo').val();
    if (tipo && row.tipo !== tipo) return false;

    const estado = $('#filtro-estado').val();
    if (estado === 'ABIERTOS') {
      if (!ABIERTOS.includes(row.estado)) return false;
    } else if (estado && row.estado !== estado) {
      return false;
    }

    const desde = $('#filtro-fecha').val();
    if (desde && (!row.fecha_reporte || row.fecha_reporte < desde)) return false;

    return true;
  });

  $('#filtro-tipo, #filtro-estado, #filtro-fecha').on('change', () => window.tablaMant.draw());
  $('#filtro-reset').on('click', () => {
    $('#filtro-tipo, #filtro-estado').val('');
    $('#filtro-fecha').val('');
    window.tablaMant.search('').draw();
  });

  // ═══════════════════════════════════════════
  // AJAX + refresco de fila
  // ═══════════════════════════════════════════
  function aplicarRespuesta(res, modalId) {
    const idx = window.mantenimientos.findIndex(m => m.id_mantenimiento === res.data.id_mantenimiento);
    if (idx >= 0) {
      window.mantenimientos[idx] = res.data;
    } else {
      window.mantenimientos.unshift(res.data);
    }
    window.tablaMant.clear().rows.add(window.mantenimientos).draw(false);
    refrescarIndicadores();

    if (modalId) bootstrap.Modal.getOrCreateInstance(document.getElementById(modalId)).hide();

    Swal.fire({ icon: 'success', title: 'Listo', text: res.message, timer: 2200, showConfirmButton: false });
  }

  function manejarError(xhr, fallback) {
    if (xhr.status === 422 && xhr.responseJSON?.errors) {
      const msgs = Object.values(xhr.responseJSON.errors).flat().join('<br>');
      Swal.fire({ icon: 'warning', title: 'Revisa los datos', html: msgs });
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: fallback });
    }
  }

  // Envío simple (payload serializado) — usado por cancelar.
  function enviar(url, metodo, payload, modalId, fallback) {
    $.ajax({
      url,
      type: metodo,
      data: payload,
      headers: { 'X-CSRF-TOKEN': csrf() },
      success: res => aplicarRespuesta(res, modalId),
      error: xhr => manejarError(xhr, fallback)
    });
  }

  // Envío con archivos (FormData + method spoofing) — usado por avance y finalizar.
  function enviarFormData(url, formEl, modalId, fallback, onSuccess) {
    const formData = new FormData(formEl);
    formData.append('_method', 'PUT');

    $.ajax({
      url,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: { 'X-CSRF-TOKEN': csrf() },
      success: res => {
        aplicarRespuesta(res, modalId);
        formEl.reset();
        if (onSuccess) onSuccess(res);
      },
      error: xhr => manejarError(xhr, fallback)
    });
  }

  // Modal de baja compartido (integración: al finalizar con RECOMENDADO_BAJA).
  initNuevaBajaModal();

  // ═══════════════════════════════════════════
  // NUEVO MANTENIMIENTO
  // ═══════════════════════════════════════════
  const modalNuevo = $('#modalNuevoMantenimiento');
  const formNuevo = $('#form-nuevo-mantenimiento');
  const btnGuardar = $('#btn-guardar-mantenimiento');
  const spinnerGuardar = btnGuardar.find('.spinner-border');

  const modalidad = $('#nuevo-modalidad');
  const grupoTecnico = $('#grupo-tecnico');
  const grupoProveedor = $('#grupo-proveedor');

  const tecnico = $('#nuevo-tecnico');
  const proveedor = $('#nuevo-proveedor');

  function limpiarErroresNuevo() {
    formNuevo.find('.is-invalid').removeClass('is-invalid');
    formNuevo.find('.invalid-feedback').removeClass('d-block').text('');
    formNuevo.find('.select2-selection').removeClass('border-danger');
  }

  // La modalidad se decide automáticamente por la garantía del activo (el backend la re-verifica).
  function obtenerActivoSeleccionado() {
    const id = $('#nuevo-activo').val();
    if (!id) return null;
    return window.activosMantenimiento?.[id] ?? window.activosMantenimiento?.[String(id)] ?? null;
  }

  function actualizarAtencionPorActivo() {
    const activo = obtenerActivoSeleccionado();

    // Estado inicial: ocultar técnico, proveedor y mensaje de garantía.
    grupoTecnico.addClass('d-none');
    grupoProveedor.addClass('d-none');
    $('#grupo-estado-garantia').addClass('d-none');
    tecnico.prop('required', false).val(null).trigger('change');
    proveedor.prop('required', false).val('');
    modalidad.val('');

    if (!activo) return;

    $('#grupo-estado-garantia').removeClass('d-none');

    if (activo.garantia_vigente) {
      // Garantía vigente → atención por proveedor.
      modalidad.val('GARANTIA_PROVEEDOR');
      grupoProveedor.removeClass('d-none');
      proveedor.prop('required', true).val(activo.proveedor ?? '');

      $('#alerta-garantia').removeClass('alert-secondary alert-warning alert-danger').addClass('alert-success');
      $('#garantia-titulo').text('Activo con garantía vigente');
      $('#garantia-detalle').text(
        activo.garantia_fin
          ? `La garantía vence el ${fmtFecha(activo.garantia_fin)}. La atención corresponde al proveedor.`
          : 'La atención corresponde al proveedor.'
      );
      return;
    }

    // Sin garantía vigente → atención interna por OTI.
    modalidad.val('INTERNA_OTI');
    grupoTecnico.removeClass('d-none');
    tecnico.prop('required', true);

    $('#alerta-garantia').removeClass('alert-success alert-warning alert-danger').addClass('alert-secondary');
    $('#garantia-titulo').text('Activo sin garantía vigente');
    $('#garantia-detalle').text(
      activo.garantia_fin
        ? `La garantía venció el ${fmtFecha(activo.garantia_fin)}. La atención será realizada por OTI.`
        : 'No tiene una garantía registrada. La atención será realizada por OTI.'
    );
  }

  $('#nuevo-activo').on('change', actualizarAtencionPorActivo);

  function inicializarSelect2Nuevo() {
    const opciones = [
      { selector: '#nuevo-activo', placeholder: 'Seleccione un activo...' },
      { selector: '#nuevo-tecnico', placeholder: 'Seleccione un técnico...' },
      { selector: '#nuevo-solicitante', placeholder: 'No especificado' }
    ];

    opciones.forEach(configuracion => {
      const select = $(configuracion.selector);
      if (!select.length || !$.fn.select2 || select.hasClass('select2-hidden-accessible')) return;

      select.select2({
        dropdownParent: modalNuevo,
        width: '100%',
        placeholder: configuracion.placeholder,
        allowClear: true
      });
    });
  }

  modalNuevo.on('shown.bs.modal', function () {
    inicializarSelect2Nuevo();
    actualizarAtencionPorActivo();
  });

  formNuevo.on('submit', function (event) {
    event.preventDefault();
    limpiarErroresNuevo();

    const formulario = this;
    const idActivoUtilizado = $('#nuevo-activo').val();

    btnGuardar.prop('disabled', true);
    spinnerGuardar.removeClass('d-none');

    $.ajax({
      url: window.routesMant.store,
      type: 'POST',
      data: formNuevo.serialize(),
      headers: { 'X-CSRF-TOKEN': csrf() },
      success: response => {
        aplicarRespuesta(response, 'modalNuevoMantenimiento');

        // El activo deja de ser elegible: ahora tiene un mantenimiento abierto.
        if (idActivoUtilizado) {
          $('#nuevo-activo').find(`option[value="${idActivoUtilizado}"]`).remove();
        }

        formulario.reset();
        $('#nuevo-activo').val(null).trigger('change');
        $('#nuevo-tecnico').val(null).trigger('change');
        $('#nuevo-solicitante').val(null).trigger('change');
        $('#nuevo-modalidad').val('');
        $('#nuevo-tipo').val('');
        actualizarAtencionPorActivo();
      },
      error: xhr => {
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          mostrarErroresNuevo(xhr.responseJSON.errors);
          return;
        }
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: xhr.responseJSON?.message || 'No se pudo registrar el mantenimiento.'
        });
      },
      complete: () => {
        btnGuardar.prop('disabled', false);
        spinnerGuardar.addClass('d-none');
      }
    });
  });

  function mostrarErroresNuevo(errors) {
    const mapa = {
      id_activo: '#nuevo-activo',
      tipo_mantenimiento: '#nuevo-tipo',
      tecnico_responsable: '#nuevo-tecnico',
      proveedor: '#nuevo-proveedor',
      fecha_reporte: '#nuevo-fecha',
      solicitado_por: '#nuevo-solicitante',
      descripcion: '#nuevo-descripcion'
    };

    Object.entries(errors).forEach(([campo, mensajes]) => {
      const selector = mapa[campo];
      if (!selector) return;

      const input = $(selector);
      const mensaje = mensajes[0];
      input.addClass('is-invalid');

      if (input.hasClass('select2-hidden-accessible')) {
        input.next('.select2-container').find('.select2-selection').addClass('border-danger');
        input.siblings('.invalid-feedback').addClass('d-block').text(mensaje);
        return;
      }
      input.siblings('.invalid-feedback').text(mensaje);
    });
  }

  modalNuevo.on('hidden.bs.modal', function () {
    const formulario = formNuevo[0];
    formulario?.reset();
    $('#nuevo-activo').val(null).trigger('change');
    $('#nuevo-tecnico').val(null).trigger('change');
    $('#nuevo-solicitante').val(null).trigger('change');
    $('#nuevo-modalidad').val('');
    $('#nuevo-tipo').val('');
    limpiarErroresNuevo();
    actualizarAtencionPorActivo();
    btnGuardar.prop('disabled', false);
    spinnerGuardar.addClass('d-none');
  });

  // ═══════════════════════════════════════════
  // DETALLE
  // ═══════════════════════════════════════════
  function renderAvances(avances) {
    if (!avances || !avances.length) {
      return '<p class="text-muted mb-0">Sin avances registrados.</p>';
    }
    return avances
      .map((av, i) => {
        const docs = (av.documentos ?? [])
          .map(d => {
            const [icono, color] = extensionIcono(d.extension);
            return `<a href="${d.url_descarga}" class="btn btn-sm btn-outline-primary me-1 mb-1"><i class="bx ${icono} me-1"></i>${d.nombre_original ?? 'Evidencia'}</a>`;
          })
          .join('');
        return `
          <div class="border rounded p-3 mb-2">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-start gap-1 mb-1">
              <strong>Avance ${i + 1}</strong>
              <small class="text-muted d-flex flex-column flex-sm-row gap-sm-1">
                <span>${av.fecha ?? ''}</span>
                ${av.registrado_por ? `<span>${av.registrado_por}</span>` : ''}
              </small>
            </div>
            ${av.diagnostico ? `<div class="small mb-1"><span class="text-muted">Diagnóstico:</span> ${av.diagnostico}</div>` : ''}
            <div class="small mb-1"><span class="text-muted">Actividad:</span> ${av.actividad_realizada ?? '—'}</div>
            ${av.observacion ? `<div class="small mb-1"><span class="text-muted">Observación:</span> ${av.observacion}</div>` : ''}
            ${av.costo !== null && av.costo !== undefined && av.costo !== '' ? `<div class="small mb-1"><span class="text-muted">Costo:</span> ${fmtCosto(av.costo)}</div>` : ''}
            ${docs ? `<div class="mt-2">${docs}</div>` : ''}
          </div>`;
      })
      .join('');
  }

  $(document).on('click', '.btn-detalle-mant', function () {
    const m = buscar(parseInt($(this).data('id')));
    if (!m) return;

    $('#det-codigo').text(m.codigo);
    $('#det-tipo')
      // .attr('class', `badge mb-2 fw-bold ${tipoBadge[m.tipo] ?? 'bg-label-secondary'}`)
      .text(legible(tipoTexto[m.tipo]));
    $('#det-titulo').text(m.descripcion ? m.descripcion.split('\n')[0].slice(0, 80) : m.codigo);
    $('#det-subtitulo').text(
      // `Reportado el ${fmtFecha(m.fecha_reporte) ?? '—'} · Modalidad: ${modalidadTexto[m.modalidad] ?? '—'}`
      `${fmtFecha(m.fecha_reporte) ?? '—'}`
    );
    $('#det-modalidad')
      // .attr('class', `badge ${modalidadBadge[m.modalidad] ?? 'bg-label-secondary'}`)
      .text(modalidadTexto[m.modalidad] ?? legible(m.modalidad));

    $('#icon-category').attr('class', `bx ${m.activo_categoria_icono ?? 'bx-package'}`);

    $('#det-activo-modelo').text(
      `${m.activo_categoria ?? 'Sin categoría'} ${m.activo_modelo || m.activo_codigo || '—'}`
    );
    $('#det-activo-codigo').text(`Código interno: ${m.activo_codigo ?? '—'}`);
    $('#det-activo-patrimonial').text(`Código patrimonial: ${m.activo_patrimonial ?? '—'}`);
    $('#det-activo-responsable').text(
      `Responsable: ${m.activo_responsable ?? 'Sin responsable'} · Situación: ${m.activo_situacion ?? '—'}`
    );
    $('#det-activo-url').attr('href', m.activo_url ?? '#');

    $('#det-descripcion').text(m.descripcion || '—');
    $('#det-diagnostico').text(m.diagnostico || 'Pendiente de diagnóstico.');
    $('#det-resultado').text(m.resultado || 'Pendiente de resultado.');

    $('#det-estado')
      .attr('class', `badge ${estadoBadge[m.estado] ?? 'bg-label-secondary'}`)
      .text(estadoTexto[m.estado] ?? legible(m.estado));
    $('#det-resultado-atencion')
      .attr('class', `badge ${resultadoBadge[m.resultado_atencion] ?? 'bg-label-secondary'}`)
      .text(
        m.resultado_atencion ? (resultadoTexto[m.resultado_atencion] ?? legible(m.resultado_atencion)) : 'Pendiente'
      );

    $('#det-solicitante').text(m.solicitado_por || '—');
    $('#det-tecnico').text(m.tecnico || 'Por asignar');
    $('#det-proveedor').text(m.proveedor || '—');
    $('#det-costo').text(fmtCosto(m.costo) ?? 'Pendiente');
    $('#det-registrador').text(m.registrado_por || '—');

    $('#det-fecha-reporte').text(fmtFecha(m.fecha_reporte) ?? '—');
    $('#det-fecha-inicio').text(fmtFecha(m.fecha_inicio) ?? 'No iniciado');
    $('#det-fecha-fin').text(fmtFecha(m.fecha_fin) ?? 'En curso');

    // Historial de avances
    $('#det-historial-avances').html(renderAvances(m.avances));

    // Evidencias generales del mantenimiento
    const docs = m.documentos ?? [];
    $('#det-evidencias').html(
      docs.length
        ? docs
            .map(d => {
              const [icono, color] = extensionIcono(d.extension);
              return `
                <div class="col-md-6">
                  <div class="maintenance-file-card">
                    <div class="maintenance-file-icon ${color}"><i class="bx ${icono}"></i></div>
                    <div class="flex-grow-1">
                      <h6 class="mb-1">${d.nombre_original ?? d.tipo_documento}</h6>
                      <small class="text-muted d-block">${(d.extension ?? '').toUpperCase()}${d.tamano_kb ? ' · ' + d.tamano_kb + ' KB' : ''}</small>
                      <small class="text-muted">${d.fecha ?? ''}${d.subido_por ? ' · ' + d.subido_por : ''}</small>
                    </div>
                    <a href="${d.url_descarga}" class="btn btn-sm btn-icon btn-outline-primary">
                      <i class="bx bx-download"></i>
                    </a>
                  </div>
                </div>`;
            })
            .join('')
        : '<div class="col-12"><p class="text-muted mb-0">Sin evidencias adjuntas.</p></div>'
    );
    $('#evidencia-entidad-id').val(m.id_mantenimiento);

    // Acciones rápidas contextuales
    const acciones = [];
    if (ABIERTOS.includes(m.estado)) {
      acciones.push(
        `<button class="btn btn-primary btn-avance-mant" data-id="${m.id_mantenimiento}" data-bs-dismiss="modal"><i class="bx bx-edit me-1"></i> Avance técnico</button>`
      );
      if (m.estado === 'EN_ATENCION') {
        acciones.push(
          `<button class="btn btn-outline-success btn-finalizar-mant" data-id="${m.id_mantenimiento}" data-bs-dismiss="modal"><i class="bx bx-check me-1"></i> Finalizar</button>`
        );
      }
      acciones.push(
        `<button class="btn btn-outline-danger btn-cancelar-mant" data-id="${m.id_mantenimiento}" data-bs-dismiss="modal"><i class="bx bx-x-circle me-1"></i> Cancelar</button>`
      );
    } else {
      acciones.push('<p class="text-muted mb-0">Proceso finalizado: sin acciones disponibles.</p>');
    }
    $('#det-acciones').html(acciones.join(''));

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleMantenimiento')).show();
  });

  // ═══════════════════════════════════════════
  // AVANCE TÉCNICO
  // ═══════════════════════════════════════════
  let idAvance = null;

  $(document).on('click', '.btn-avance-mant', function () {
    const m = buscar(parseInt($(this).data('id')));
    if (!m) return;
    idAvance = m.id_mantenimiento;

    const form = document.getElementById('form-avance');
    form.reset();

    $('#avance-codigo').text(m.codigo);
    // El diagnóstico principal se prellena como punto de partida; el resto queda en blanco.
    $('#avance-diagnostico').val(m.diagnostico ?? '');

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAvance')).show();
  });

  $('#form-avance').on('submit', function (e) {
    e.preventDefault();
    enviarFormData(
      window.routesMant.avanzar.replace('{id}', idAvance),
      this,
      'modalAvance',
      'No se pudo registrar el avance.'
    );
  });

  // ═══════════════════════════════════════════
  // FINALIZAR
  // ═══════════════════════════════════════════
  let idFinalizar = null;

  $(document).on('click', '.btn-finalizar-mant', function () {
    const m = buscar(parseInt($(this).data('id')));
    if (!m) return;
    idFinalizar = m.id_mantenimiento;

    const form = document.getElementById('form-finalizar');
    form.reset();

    $('#fin-codigo').text(m.codigo);
    $('#fin-resultado-atencion').val('OPERATIVO').trigger('change');
    $('#fin-diagnostico').val(m.diagnostico ?? '');
    $('#fin-resultado').val('');
    $('#fin-costo').val('');

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalFinalizar')).show();
  });

  $('#fin-resultado-atencion').on('change', function () {
    const esBaja = $(this).val() === 'RECOMENDADO_BAJA';
    $('#fin-alerta-baja').toggleClass('d-none', !esBaja);
    // La condición resultante solo aplica si el equipo queda operativo; si se
    // recomienda baja, la condición pasa a MALO automáticamente.
    $('#fin-condicion-wrap').toggleClass('d-none', esBaja);
    $('#fin-condicion').prop('required', !esBaja);
    if (esBaja) $('#fin-condicion').val('');
  });

  $('#form-finalizar').on('submit', function (e) {
    e.preventDefault();
    enviarFormData(
      window.routesMant.finalizar.replace('{id}', idFinalizar),
      this,
      'modalFinalizar',
      'No se pudo finalizar el mantenimiento.',
      res => {
        // Si el resultado fue RECOMENDADO_BAJA, abrir el modal de propuesta de baja
        // precargado. La baja NO se crea automáticamente: el usuario debe confirmar.
        if (res.abrir_modal_baja && res.baja_prefill) {
          prefillBajaDesdeMantenimiento(res.baja_prefill);
        }
      }
    );
  });

  // ═══════════════════════════════════════════
  // CANCELAR
  // ═══════════════════════════════════════════
  // Nota: la acción "Cerrar mantenimiento" fue eliminada (FINALIZADO es terminal).
  $(document).on('click', '.btn-cancelar-mant', function () {
    const m = buscar(parseInt($(this).data('id')));
    if (!m) return;

    Swal.fire({
      icon: 'warning',
      title: `¿Cancelar ${m.codigo}?`,
      input: 'textarea',
      inputLabel: 'Motivo de la cancelación',
      inputPlaceholder: 'Ej. reporte duplicado, el equipo volvió a operar, etc.',
      inputValidator: v => (!v || !v.trim() ? 'Indica el motivo de la cancelación.' : undefined),
      showCancelButton: true,
      confirmButtonText: 'Cancelar mantenimiento',
      cancelButtonText: 'Volver',
      customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(r => {
      if (!r.isConfirmed) return;
      enviar(
        window.routesMant.cancelar.replace('{id}', m.id_mantenimiento),
        'PUT',
        { motivo: r.value },
        null,
        'No se pudo cancelar el mantenimiento.'
      );
    });
  });
});
