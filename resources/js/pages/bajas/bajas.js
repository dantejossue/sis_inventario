import $ from 'jquery';
import Swal from 'sweetalert2';
import dtDefaults from '../../plugins/datatables-defaults';
import { initNuevaBajaModal } from './nueva-baja-modal';

/**
 * Bajas de activos (flujo simplificado): REGISTRADA → EJECUTADA / RECHAZADA.
 * La evaluación técnica ya no vive aquí (la realiza mantenimientos).
 */

const causalBadge = {
  DANO_IRREPARABLE: 'bg-label-danger',
  OBSOLESCENCIA: 'bg-label-warning',
  REPARACION_NO_CONVENIENTE: 'bg-label-warning',
  RAEE: 'bg-label-dark',
  SUSTRACCION: 'bg-label-dark',
  OTRO: 'bg-label-secondary'
};

const causalTexto = {
  DANO_IRREPARABLE: 'Daño irreparable',
  OBSOLESCENCIA: 'Obsolescencia',
  REPARACION_NO_CONVENIENTE: 'Reparación no conveniente',
  RAEE: 'RAEE',
  SUSTRACCION: 'Sustracción',
  OTRO: 'Otro'
};

const estadoBadge = {
  REGISTRADA: 'bg-label-warning',
  EJECUTADA: 'bg-success',
  RECHAZADA: 'bg-label-danger'
};

const estadoTexto = {
  REGISTRADA: 'Registrada',
  EJECUTADA: 'Ejecutada',
  RECHAZADA: 'Rechazada'
};

/*
 * Referencias del flujo anterior FUERA DE USO (conservadas como comentario):
 * estados EN_EVALUACION, RECOMENDADA, VALIDADA; causales viejas (DANO, SIN_REPARACION,
 * OBSOLESCENCIA_TECNICA, MANTENIMIENTO_ONEROSO); clasifTexto; #eval-resultado,
 * #eval-clasificacion, .btn-evaluar-baja, .btn-validar-baja.
 */

const dash = '<span class="text-muted">—</span>';
const fmtFecha = f => (f ? f.split('-').reverse().join('/') : null);
const csrf = () => $('meta[name="csrf-token"]').attr('content');
const buscar = id => window.bajas.find(b => b.id_baja === id);

function extensionIcono(ext) {
  if (ext === 'pdf') return ['bxs-file-pdf', 'bg-label-danger'];
  if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) return ['bx-image', 'bg-label-primary'];
  if (['xls', 'xlsx'].includes(ext)) return ['bx-spreadsheet', 'bg-label-success'];
  return ['bx-file', 'bg-label-info'];
}

$(function () {
  // ═══════════════════════════════════════════ TABLA
  window.tablaBajas = $('#miTablaBajas').DataTable({
    ...dtDefaults,
    data: window.bajas,
    order: [[0, 'desc']],
    columns: [
      { data: 'codigo', render: d => `<span class="fw-semibold">${d}</span>` },
      {
        data: 'activo_codigo',
        render: (d, t, row) =>
          `<a href="${row.activo_url ?? '#'}" class="fw-semibold d-block">${d ?? '—'}</a>` +
          `<small class="text-muted d-block">${row.activo_modelo || '—'}</small>` +
          (row.activo_patrimonial ? `<small class="text-muted">Patrim.: ${row.activo_patrimonial}</small>` : '')
      },
      {
        data: 'causal',
        render: d => `<span class="badge ${causalBadge[d] ?? 'bg-label-secondary'}">${causalTexto[d] ?? d}</span>`
      },
      {
        data: null,
        orderable: false,
        render: row => {
          const origen = row.mantenimiento_origen
            ? `<span class="d-block">Mantenimiento ${row.mantenimiento_origen}</span>`
            : '<span class="d-block text-muted">Registro directo</span>';
          const docs = row.documentos_count
            ? `<small class="text-muted">${row.documentos_count} documento(s)</small>`
            : '<small class="text-muted">Sin documentos</small>';
          return origen + docs;
        }
      },
      {
        data: 'estado',
        render: d => `<span class="badge ${estadoBadge[d] ?? 'bg-label-secondary'}">${estadoTexto[d] ?? d}</span>`
      },
      {
        data: 'fecha_registro',
        render: (d, t, row) => {
          if (t === 'sort' || t === 'type') return d ?? '';
          const final = row.fecha_ejecucion || row.fecha_rechazo;
          return (
            `<span class="d-block">${fmtFecha(d) ?? '—'}</span>` +
            (final ? `<small class="text-muted">Fin: ${fmtFecha(final)}</small>` : '')
          );
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-end',
        render: row => {
          let items = `
            <li>
              <a class="dropdown-item btn-detalle-baja d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_baja}">
                <i class="bx bx-show me-1"></i> Ver detalle
              </a>
            </li>`;

          if (row.estado === 'REGISTRADA') {
            items += `
            <li>
              <a class="dropdown-item btn-ejecutar-baja d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_baja}">
                <i class="bx bx-check-circle me-1"></i> Ejecutar baja
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger btn-rechazar-baja d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_baja}">
                <i class="bx bx-x-circle me-1"></i> Rechazar
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
      document.querySelectorAll('#miTablaBajas [data-bs-toggle="dropdown"]').forEach(el => {
        bootstrap.Dropdown.getOrCreateInstance(el, { popperConfig: { strategy: 'fixed' } });
      });
    }
  });

  // ═══════════════════════════════════════════ FILTROS
  $.fn.dataTable.ext.search.push((settings, data, dataIndex) => {
    if (settings.nTable.id !== 'miTablaBajas') return true;
    const row = window.tablaBajas.row(dataIndex).data();

    const causal = $('#filtro-causal').val();
    if (causal && row.causal !== causal) return false;

    const estado = $('#filtro-estado').val();
    if (estado && row.estado !== estado) return false;

    const desde = $('#filtro-fecha').val();
    if (desde && (!row.fecha_registro || row.fecha_registro < desde)) return false;

    return true;
  });

  $('#filtro-causal, #filtro-estado, #filtro-fecha').on('change', () => window.tablaBajas.draw());
  $('#filtro-reset').on('click', () => {
    $('#filtro-causal, #filtro-estado').val('');
    $('#filtro-fecha').val('');
    window.tablaBajas.search('').draw();
  });

  // ═══════════════════════════════════════════ AJAX + refresco
  function refrescarTabla(res) {
    const idx = window.bajas.findIndex(b => b.id_baja === res.data.id_baja);
    if (idx >= 0) window.bajas[idx] = res.data;
    else window.bajas.unshift(res.data);

    window.tablaBajas.clear().rows.add(window.bajas).draw(false);
  }

  function aplicarRespuesta(res, modalId) {
    refrescarTabla(res);
    if (modalId) bootstrap.Modal.getOrCreateInstance(document.getElementById(modalId)).hide();
    Swal.fire({ icon: 'success', title: 'Listo', text: res.message, timer: 2400, showConfirmButton: false });
  }

  function manejarError(xhr, fallback) {
    if (xhr.status === 422 && xhr.responseJSON?.errors) {
      const msgs = Object.values(xhr.responseJSON.errors).flat().join('<br>');
      Swal.fire({ icon: 'warning', title: 'Revisa los datos', html: msgs });
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: fallback });
    }
  }

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

  // ═══════════════════════════════════════════ NUEVA BAJA (modal compartido)
  initNuevaBajaModal({
    onCreated: res => {
      refrescarTabla(res);
      // El activo deja de ser elegible: quitar su opción del selector.
      if (res.data?.id_activo) {
        $(`#baja-activo option[value="${res.data.id_activo}"]`).remove();
      }
    }
  });

  // ═══════════════════════════════════════════ DETALLE
  $(document).on('click', '.btn-detalle-baja', function () {
    const b = buscar(parseInt($(this).data('id')));
    if (!b) return;

    $('#det-codigo').text(b.codigo);
    $('#det-causal')
      .attr('class', `badge mb-2 ${causalBadge[b.causal] ?? 'bg-label-secondary'} fw-bold`)
      .text(causalTexto[b.causal] ?? b.causal);
    $('#det-titulo').text(b.motivo ? b.motivo.split('\n')[0].slice(0, 80) : b.codigo);
    $('#det-subtitulo').text(`Registrada el ${fmtFecha(b.fecha_registro) ?? '—'} · Origen: ${b.origen}`);
    $('#det-estado-texto').text(estadoTexto[b.estado] ?? b.estado);

    $('#det-activo-modelo').text(
      `${b.activo_categoria ?? 'Sin categoría'} ${b.activo_modelo || b.activo_codigo || '—'}`
    );
    $('#det-activo-codigo').text(`Código interno: ${b.activo_codigo ?? '—'}`);
    $('#icon-category').attr('class', `bx ${b.activo_categoria_icono ?? 'bx-package'}`);
    $('#det-activo-patrimonial').text(`Código patrimonial: ${b.activo_patrimonial ?? '—'}`);
    $('#det-activo-situacion').text(`Situación: ${b.activo_situacion ?? '—'}`);
    $('#det-activo-url').attr('href', b.activo_url ?? '#');

    $('#det-motivo').text(b.motivo || '—');
    $('#det-observaciones').text(b.observaciones || '—');

    // Motivo de rechazo (solo si aplica)
    $('#det-box-rechazo').toggleClass('d-none', !b.motivo_rechazo);
    $('#det-motivo-rechazo').text(b.motivo_rechazo || '—');

    // Origen de mantenimiento (solo si aplica)
    const tieneMant = !!b.mantenimiento_origen;
    $('#det-card-mant').toggleClass('d-none', !tieneMant);
    $('#det-diag-mant').text(b.diagnostico_mantenimiento || '—');
    $('#det-result-mant').text(b.resultado_mantenimiento || '—');

    $('#det-estado')
      .attr('class', `badge ${estadoBadge[b.estado] ?? 'bg-label-secondary'}`)
      .text(estadoTexto[b.estado] ?? b.estado);
    $('#det-origen').text(b.origen);
    $('#det-registrado').text(b.registrado_por || '—');
    $('#det-fecha-registro').text(fmtFecha(b.fecha_registro) ?? '—');

    // Ejecutado / rechazado por + fecha final
    if (b.estado === 'EJECUTADA') {
      $('#det-final-label').text('Ejecutado por');
      $('#det-final-por').text(b.ejecutado_por || '—');
      $('#det-fecha-final-label').text('Ejecución');
      $('#det-fecha-final').text(fmtFecha(b.fecha_ejecucion) ?? '—');
    } else if (b.estado === 'RECHAZADA') {
      $('#det-final-label').text('Rechazado por');
      $('#det-final-por').text(b.rechazado_por || '—');
      $('#det-fecha-final-label').text('Rechazo');
      $('#det-fecha-final').text(fmtFecha(b.fecha_rechazo) ?? '—');
    } else {
      $('#det-final-label').text('Ejecutado / rechazado por');
      $('#det-final-por').text('Pendiente');
      $('#det-fecha-final-label').text('Ejecución / rechazo');
      $('#det-fecha-final').text('Pendiente');
    }

    // Documentos (siempre descargables)
    const docs = b.documentos ?? [];
    $('#det-documentos').html(
      docs.length
        ? docs
            .map(d => {
              const [icono, color] = extensionIcono(d.extension);
              return `
                <div class="col-md-6">
                  <div class="disposal-file-card">
                    <div class="disposal-file-icon ${color}"><i class="bx ${icono}"></i></div>
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
        : '<div class="col-12"><p class="text-muted mb-0">Sin documentos.</p></div>'
    );
    $('#doc-entidad-id').val(b.id_baja);
    // Solo se puede adjuntar mientras esté REGISTRADA.
    $('#det-doc-form-wrap').toggleClass('d-none', b.estado !== 'REGISTRADA');

    // Acciones rápidas
    const acciones = [];
    if (b.estado === 'REGISTRADA') {
      acciones.push(
        `<button class="btn btn-danger btn-ejecutar-baja" data-id="${b.id_baja}" data-bs-dismiss="modal"><i class="bx bx-check-circle me-1"></i> Ejecutar baja</button>`
      );
      acciones.push(
        `<button class="btn btn-outline-danger btn-rechazar-baja" data-id="${b.id_baja}" data-bs-dismiss="modal"><i class="bx bx-x-circle me-1"></i> Rechazar</button>`
      );
    } else {
      acciones.push('<p class="text-muted mb-0">Proceso finalizado: sin acciones disponibles.</p>');
    }
    $('#det-acciones').html(acciones.join(''));

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleBaja')).show();
  });

  // ═══════════════════════════════════════════ EJECUTAR
  let idEjecutar = null;

  $(document).on('click', '.btn-ejecutar-baja', function () {
    const b = buscar(parseInt($(this).data('id')));
    if (!b) return;
    idEjecutar = b.id_baja;

    const form = document.getElementById('form-ejecutar-baja');
    form.reset();
    $('#eje-codigo').text(b.codigo);

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEjecutar')).show();
  });

  $('#form-ejecutar-baja').on('submit', function (e) {
    e.preventDefault();
    const form = this;
    const $btn = $('#btn-ejecutar-baja');
    const $spinner = $btn.find('.spinner-border');

    const formData = new FormData(form);
    formData.append('_method', 'PUT');

    $btn.prop('disabled', true);
    $spinner.removeClass('d-none');

    $.ajax({
      url: window.routesBajas.ejecutar.replace('{id}', idEjecutar),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: { 'X-CSRF-TOKEN': csrf() },
      success: res => {
        aplicarRespuesta(res, 'modalEjecutar');
        form.reset();
      },
      error: xhr => manejarError(xhr, 'No se pudo ejecutar la baja.'),
      complete: () => {
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');
      }
    });
  });

  // ═══════════════════════════════════════════ RECHAZAR
  $(document).on('click', '.btn-rechazar-baja', function () {
    const b = buscar(parseInt($(this).data('id')));
    if (!b) return;

    Swal.fire({
      icon: 'warning',
      title: `¿Rechazar ${b.codigo}?`,
      text: 'La propuesta se rechaza. El activo permanece OBSERVADO (no vuelve a estar operativo).',
      input: 'textarea',
      inputLabel: 'Motivo del rechazo',
      inputValidator: v => (!v || !v.trim() ? 'Indica el motivo del rechazo.' : undefined),
      showCancelButton: true,
      confirmButtonText: 'Rechazar baja',
      cancelButtonText: 'Volver',
      customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(r => {
      if (!r.isConfirmed) return;
      enviar(
        window.routesBajas.rechazar.replace('{id}', b.id_baja),
        'PUT',
        { motivo: r.value },
        null,
        'No se pudo rechazar la baja.'
      );
    });
  });
});
