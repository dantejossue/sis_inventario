import $ from 'jquery';
import Swal from 'sweetalert2';
import dtDefaults from '../../plugins/datatables-defaults';

/**
 * Módulo de bajas (F6): tabla, KPIs, filtros y flujo
 * Propuesta → Evaluación → Expediente → Aprobación → Ejecución (+ marca SIGA).
 */

const causalBadge = {
  DANO: 'bg-label-danger',
  OBSOLESCENCIA_TECNICA: 'bg-label-warning',
  RAEE: 'bg-label-danger',
  MANTENIMIENTO_ONEROSO: 'bg-label-warning',
  SIN_REPARACION: 'bg-label-danger',
  SANEAMIENTO_FALTANTE: 'bg-label-secondary',
  SUSTRACCION: 'bg-label-dark',
  GARANTIA: 'bg-label-primary',
  OTRO: 'bg-label-secondary'
};

const causalTexto = {
  DANO: 'Daño',
  OBSOLESCENCIA_TECNICA: 'Obsolescencia',
  RAEE: 'RAEE',
  MANTENIMIENTO_ONEROSO: 'Mant. oneroso',
  SIN_REPARACION: 'Sin reparación',
  SANEAMIENTO_FALTANTE: 'Faltante',
  SUSTRACCION: 'Sustracción',
  GARANTIA: 'Garantía',
  OTRO: 'Otro'
};

const estadoBadge = {
  SOLICITADA: 'bg-label-warning',
  EN_EVALUACION: 'bg-label-info',
  RECOMENDADA: 'bg-label-primary',
  APROBADA: 'bg-label-success',
  EJECUTADA: 'bg-success',
  RECHAZADA: 'bg-label-danger'
};

const estadoTexto = {
  SOLICITADA: 'Solicitada',
  EN_EVALUACION: 'En evaluación',
  RECOMENDADA: 'Recomendada',
  APROBADA: 'Aprobada',
  EJECUTADA: 'Ejecutada',
  RECHAZADA: 'Rechazada'
};

const sigaBadge = {
  NO_APLICA: 'bg-label-secondary',
  PENDIENTE_ACTUALIZACION: 'bg-label-warning',
  REGISTRADO: 'bg-label-success',
  OBSERVADO: 'bg-label-danger'
};

const sigaTexto = {
  NO_APLICA: 'No aplica',
  PENDIENTE_ACTUALIZACION: 'Pendiente',
  REGISTRADO: 'Registrado',
  OBSERVADO: 'Observado'
};

const ABIERTAS = ['SOLICITADA', 'EN_EVALUACION', 'RECOMENDADA', 'APROBADA'];

const dash = '<span class="text-muted">—</span>';
const fmtFecha = f => (f ? f.split('-').reverse().join('/') : null);
const fmtMonto = v =>
  v !== null && v !== undefined && v !== '' ? `S/ ${Number(v).toLocaleString('es-PE', { minimumFractionDigits: 2 })}` : null;
const csrf = () => $('meta[name="csrf-token"]').attr('content');

const buscar = id => window.bajas.find(b => b.id_baja === id);

$(function () {
  // ═══════════════════════════════════════════
  // TABLA
  // ═══════════════════════════════════════════
  window.tablaBajas = $('#miTablaBajas').DataTable({
    ...dtDefaults,
    data: window.bajas,
    order: [[0, 'desc']],
    columns: [
      {
        data: 'codigo',
        render: d => `<span class="fw-semibold">${d}</span>`
      },
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
        data: 'origen',
        render: d => d || dash
      },
      {
        data: 'diagnostico',
        orderable: false,
        render: d =>
          d
            ? '<span class="badge bg-label-success">Registrado</span>'
            : '<span class="badge bg-label-warning">Pendiente</span>'
      },
      {
        data: 'expediente',
        render: d =>
          d
            ? `<span class="badge bg-label-success">${d}</span>`
            : '<span class="badge bg-label-secondary">Sin expediente</span>'
      },
      {
        data: 'estado',
        render: d => `<span class="badge ${estadoBadge[d] ?? 'bg-label-secondary'}">${estadoTexto[d] ?? d}</span>`
      },
      {
        data: 'estado_siga',
        render: (d, t, row) =>
          row.estado === 'EJECUTADA' || d !== 'NO_APLICA'
            ? `<span class="badge ${sigaBadge[d] ?? 'bg-label-secondary'}">${sigaTexto[d] ?? d}</span>`
            : dash
      },
      {
        data: 'fecha_solicitud',
        render: (d, t, row) => {
          if (t === 'sort' || t === 'type') return d ?? '';
          return `<span class="d-block">${fmtFecha(d) ?? '—'}</span>` +
            (row.fecha_baja ? `<small class="text-muted">Baja: ${fmtFecha(row.fecha_baja)}</small>` : '');
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

          if (['SOLICITADA', 'EN_EVALUACION'].includes(row.estado)) {
            items += `
            <li>
              <a class="dropdown-item btn-evaluar-baja d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_baja}">
                <i class="bx bx-search-alt me-1"></i> Evaluación técnica
              </a>
            </li>`;
          }

          if (ABIERTAS.includes(row.estado) && !row.expediente) {
            items += `
            <li>
              <a class="dropdown-item btn-expediente-baja d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_baja}">
                <i class="bx bx-file me-1"></i> Vincular expediente
              </a>
            </li>`;
          }

          if (row.estado === 'RECOMENDADA') {
            items += `
            <li>
              <a class="dropdown-item btn-aprobar-baja d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_baja}">
                <i class="bx bx-check me-1"></i> Aprobar baja
              </a>
            </li>`;
          }

          if (row.estado === 'APROBADA') {
            items += `
            <li>
              <a class="dropdown-item btn-ejecutar-baja d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_baja}">
                <i class="bx bx-check-circle me-1"></i> Ejecutar baja
              </a>
            </li>`;
          }

          if (ABIERTAS.includes(row.estado)) {
            items += `
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger btn-rechazar-baja d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_baja}">
                <i class="bx bx-x-circle me-1"></i> Rechazar
              </a>
            </li>`;
          }

          if (row.estado === 'EJECUTADA' && row.estado_siga === 'PENDIENTE_ACTUALIZACION') {
            items += `
            <li>
              <a class="dropdown-item btn-siga-baja d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_baja}">
                <i class="bx bx-link me-1"></i> Marcar en SIGA
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

  // ═══════════════════════════════════════════
  // KPIs Y ALERTAS
  // ═══════════════════════════════════════════
  function refrescarIndicadores() {
    const data = window.bajas;
    const abiertas = data.filter(b => ABIERTAS.includes(b.estado));
    const ejecutadas = data.filter(b => b.estado === 'EJECUTADA');
    const pendientesSiga = ejecutadas.filter(b => b.estado_siga === 'PENDIENTE_ACTUALIZACION');

    $('#kpi-abiertas').text(abiertas.length);
    $('#kpi-abiertas-detalle').text(
      `${data.filter(b => ['SOLICITADA', 'EN_EVALUACION'].includes(b.estado)).length} por evaluar`
    );

    $('#kpi-raee').text(data.filter(b => b.causal === 'RAEE').length);

    $('#kpi-ejecutadas').text(ejecutadas.length);
    $('#kpi-ejecutadas-detalle').text(`${pendientesSiga.length} pendientes SIGA`);

    const valor = abiertas.reduce((acc, b) => acc + (Number(b.valor_compra) || 0), 0);
    $('#kpi-valor').text(fmtMonto(valor) ?? 'S/ 0.00');

    $('#stat-obsoletos').text(data.filter(b => b.causal === 'OBSOLESCENCIA_TECNICA').length);
    $('#stat-irreparables').text(data.filter(b => ['DANO', 'SIN_REPARACION'].includes(b.causal)).length);
    $('#stat-raee').text(data.filter(b => b.causal === 'RAEE').length);
    $('#stat-faltantes').text(data.filter(b => ['SANEAMIENTO_FALTANTE', 'SUSTRACCION'].includes(b.causal)).length);

    const alertas = [];
    const sinInforme = abiertas.filter(b => !b.diagnostico);
    if (sinInforme.length) {
      alertas.push(['bg-label-danger', 'bx-error', `${sinInforme.length} sin informe técnico`, 'Requieren sustento de OTI para recomendarse.']);
    }
    const sinExpediente = abiertas.filter(b => !b.expediente);
    if (sinExpediente.length) {
      alertas.push(['bg-label-warning', 'bx-file', `${sinExpediente.length} sin expediente`, 'Necesario antes de la aprobación.']);
    }
    const porEjecutar = data.filter(b => b.estado === 'APROBADA');
    if (porEjecutar.length) {
      alertas.push(['bg-label-primary', 'bx-check-shield', `${porEjecutar.length} aprobada(s) por ejecutar`, 'Listas para la baja definitiva.']);
    }
    if (pendientesSiga.length) {
      alertas.push(['bg-label-info', 'bx-link', `${pendientesSiga.length} por actualizar en SIGA`, 'Patrimonio debe registrar la baja oficial.']);
    }

    $('#alertas-bajas').html(
      alertas.length
        ? alertas
            .map(
              ([color, icono, titulo, detalle]) => `
                <div class="disposal-alert-item">
                  <div class="disposal-alert-icon ${color}"><i class="bx ${icono}"></i></div>
                  <div>
                    <h6 class="mb-1">${titulo}</h6>
                    <small class="text-muted">${detalle}</small>
                  </div>
                </div>`
            )
            .join('')
        : '<p class="text-muted mb-0">Sin alertas pendientes.</p>'
    );
  }

  refrescarIndicadores();

  // ═══════════════════════════════════════════
  // FILTROS
  // ═══════════════════════════════════════════
  $.fn.dataTable.ext.search.push((settings, data, dataIndex) => {
    if (settings.nTable.id !== 'miTablaBajas') return true;
    const row = window.tablaBajas.row(dataIndex).data();

    const causal = $('#filtro-causal').val();
    if (causal && row.causal !== causal) return false;

    const estado = $('#filtro-estado').val();
    if (estado === 'ABIERTAS') {
      if (!ABIERTAS.includes(row.estado)) return false;
    } else if (estado && row.estado !== estado) {
      return false;
    }

    const siga = $('#filtro-siga').val();
    if (siga && row.estado_siga !== siga) return false;

    const desde = $('#filtro-fecha').val();
    if (desde && (!row.fecha_solicitud || row.fecha_solicitud < desde)) return false;

    return true;
  });

  $('#filtro-causal, #filtro-estado, #filtro-siga, #filtro-fecha').on('change', () => window.tablaBajas.draw());
  $('#filtro-reset').on('click', () => {
    $('#filtro-causal, #filtro-estado, #filtro-siga').val('');
    $('#filtro-fecha').val('');
    window.tablaBajas.search('').draw();
  });

  // ═══════════════════════════════════════════
  // AJAX + refresco
  // ═══════════════════════════════════════════
  function aplicarRespuesta(res, modalId) {
    const idx = window.bajas.findIndex(b => b.id_baja === res.data.id_baja);
    if (idx >= 0) {
      window.bajas[idx] = res.data;
    } else {
      window.bajas.unshift(res.data);
    }
    window.tablaBajas.clear().rows.add(window.bajas).draw(false);
    refrescarIndicadores();

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

  function enviar(url, metodo, payload, modalId, fallback, onSuccess) {
    $.ajax({
      url,
      type: metodo,
      data: payload,
      headers: { 'X-CSRF-TOKEN': csrf() },
      success: res => {
        aplicarRespuesta(res, modalId);
        if (onSuccess) onSuccess(res);
      },
      error: xhr => manejarError(xhr, fallback)
    });
  }

  // ═══════════════════════════════════════════
  // NUEVA PROPUESTA
  // ═══════════════════════════════════════════
  $('#baja-activo').on('change', function () {
    const opt = $(this).find('option:selected');
    $('#resumen-activo').text(opt.text().split('—')[0].trim() || 'No seleccionado');
    $('#resumen-valor').text(fmtMonto(opt.data('valor')) ?? '—');

    // Mantenimientos que recomendaron baja para este activo
    const mants = (window.mantsBaja ?? {})[$(this).val()] ?? [];
    $('#baja-mantenimiento').html(
      '<option value="">Sin mantenimiento vinculado</option>' +
        mants.map(m => `<option value="${m.id_mantenimiento}">${m.codigo} — ${m.descripcion}</option>`).join('')
    );
    if (mants.length === 1) $('#baja-mantenimiento').val(mants[0].id_mantenimiento);
  });

  $('#baja-causal').on('change', function () {
    $('#resumen-causal').text($(this).find('option:selected').text() || 'No seleccionada');
  });

  $('#form-nueva-baja').on('submit', function (e) {
    e.preventDefault();
    const form = this;
    const usado = $('#baja-activo').val();

    enviar(
      window.routesBajas.store,
      'POST',
      $(form).serialize(),
      'modalNuevaBaja',
      'No se pudo registrar la propuesta de baja.',
      () => {
        if (usado) $(`#baja-activo option[value="${usado}"]`).remove();
        form.reset();
        $('#resumen-activo').text('No seleccionado');
        $('#resumen-causal').text('No seleccionada');
        $('#resumen-valor').text('—');
      }
    );
  });

  // ═══════════════════════════════════════════
  // DETALLE
  // ═══════════════════════════════════════════
  function extensionIcono(ext) {
    if (ext === 'pdf') return ['bxs-file-pdf', 'bg-label-danger'];
    if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) return ['bx-image', 'bg-label-primary'];
    if (['xls', 'xlsx'].includes(ext)) return ['bx-spreadsheet', 'bg-label-success'];
    return ['bx-file', 'bg-label-info'];
  }

  $(document).on('click', '.btn-detalle-baja', function () {
    const b = buscar(parseInt($(this).data('id')));
    if (!b) return;

    $('#det-codigo').text(b.codigo);
    $('#det-causal').attr('class', `badge mb-2 ${causalBadge[b.causal] ?? 'bg-label-secondary'}`).text(causalTexto[b.causal] ?? b.causal);
    $('#det-titulo').text(b.motivo ? b.motivo.split('\n')[0].slice(0, 80) : b.codigo);
    $('#det-subtitulo').text(`Solicitada el ${fmtFecha(b.fecha_solicitud) ?? '—'} · Origen: ${b.origen}`);
    $('#det-estado-texto').text(estadoTexto[b.estado] ?? b.estado);

    $('#det-activo-modelo').text(b.activo_modelo || b.activo_codigo || '—');
    $('#det-activo-codigo').text(`Código interno: ${b.activo_codigo ?? '—'}`);
    $('#det-activo-patrimonial').text(`Código patrimonial: ${b.activo_patrimonial ?? '—'}`);
    $('#det-activo-valor').text(`Valor referencial: ${fmtMonto(b.valor_compra) ?? '—'}`);
    $('#det-activo-url').attr('href', b.activo_url ?? '#');

    $('#det-motivo').text(b.motivo || '—');
    $('#det-diagnostico').text(b.diagnostico || 'Pendiente de informe técnico.');
    $('#det-observaciones').text(b.observaciones || '—');

    $('#det-estado').attr('class', `badge ${estadoBadge[b.estado] ?? 'bg-label-secondary'}`).text(estadoTexto[b.estado] ?? b.estado);
    $('#det-siga').attr('class', `badge ${sigaBadge[b.estado_siga] ?? 'bg-label-secondary'}`).text(sigaTexto[b.estado_siga] ?? b.estado_siga);
    $('#det-origen').text(b.origen);
    $('#det-expediente').text(b.expediente || 'Sin expediente');
    $('#det-solicitante').text(b.solicitado_por || '—');
    $('#det-evaluador').text(b.evaluado_por || 'Pendiente');
    $('#det-aprobador').text(b.aprobado_por || 'Pendiente');

    $('#det-fecha-solicitud').text(fmtFecha(b.fecha_solicitud) ?? '—');
    $('#det-fecha-evaluacion').text(fmtFecha(b.fecha_evaluacion) ?? 'Pendiente');
    $('#det-fecha-aprobacion').text(fmtFecha(b.fecha_aprobacion) ?? 'Pendiente');
    $('#det-fecha-baja').text(fmtFecha(b.fecha_baja) ?? 'Pendiente');

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
        : '<div class="col-12"><p class="text-muted mb-0">Sin documentos de sustento.</p></div>'
    );
    $('#doc-entidad-id').val(b.id_baja);

    // Acciones contextuales
    const acciones = [];
    if (['SOLICITADA', 'EN_EVALUACION'].includes(b.estado)) {
      acciones.push(`<button class="btn btn-primary btn-evaluar-baja" data-id="${b.id_baja}" data-bs-dismiss="modal"><i class="bx bx-search-alt me-1"></i> Evaluación técnica</button>`);
    }
    if (ABIERTAS.includes(b.estado) && !b.expediente) {
      acciones.push(`<button class="btn btn-outline-primary btn-expediente-baja" data-id="${b.id_baja}" data-bs-dismiss="modal"><i class="bx bx-file me-1"></i> Vincular expediente</button>`);
    }
    if (b.estado === 'RECOMENDADA') {
      acciones.push(`<button class="btn btn-outline-success btn-aprobar-baja" data-id="${b.id_baja}" data-bs-dismiss="modal"><i class="bx bx-check me-1"></i> Aprobar baja</button>`);
    }
    if (b.estado === 'APROBADA') {
      acciones.push(`<button class="btn btn-success btn-ejecutar-baja" data-id="${b.id_baja}" data-bs-dismiss="modal"><i class="bx bx-check-circle me-1"></i> Ejecutar baja</button>`);
    }
    if (ABIERTAS.includes(b.estado)) {
      acciones.push(`<button class="btn btn-outline-danger btn-rechazar-baja" data-id="${b.id_baja}" data-bs-dismiss="modal"><i class="bx bx-x-circle me-1"></i> Rechazar</button>`);
    }
    if (b.estado === 'EJECUTADA' && b.estado_siga === 'PENDIENTE_ACTUALIZACION') {
      acciones.push(`<button class="btn btn-outline-primary btn-siga-baja" data-id="${b.id_baja}" data-bs-dismiss="modal"><i class="bx bx-link me-1"></i> Marcar en SIGA</button>`);
    }
    if (!acciones.length) {
      acciones.push('<p class="text-muted mb-0">Proceso finalizado: sin acciones disponibles.</p>');
    }
    $('#det-acciones').html(acciones.join(''));

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleBaja')).show();
  });

  // ═══════════════════════════════════════════
  // EVALUACIÓN
  // ═══════════════════════════════════════════
  let idEvaluar = null;

  $(document).on('click', '.btn-evaluar-baja', function () {
    const b = buscar(parseInt($(this).data('id')));
    if (!b) return;
    idEvaluar = b.id_baja;

    $('#eval-codigo').text(b.codigo);
    $('#eval-resultado').val('RECOMENDADA').trigger('change');
    $('#eval-diagnostico').val(b.diagnostico ?? '');
    $('#eval-observaciones').val('');

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEvaluar')).show();
  });

  $('#eval-resultado').on('change', function () {
    $('#eval-alerta-rechazo').toggleClass('d-none', $(this).val() !== 'RECHAZADA');
  });

  $('#form-evaluar').on('submit', function (e) {
    e.preventDefault();
    enviar(
      window.routesBajas.evaluar.replace('{id}', idEvaluar),
      'PUT',
      $(this).serialize(),
      'modalEvaluar',
      'No se pudo registrar la evaluación.'
    );
  });

  // ═══════════════════════════════════════════
  // EXPEDIENTE
  // ═══════════════════════════════════════════
  let idExpediente = null;

  $(document).on('click', '.btn-expediente-baja', function () {
    const b = buscar(parseInt($(this).data('id')));
    if (!b) return;
    idExpediente = b.id_baja;

    $('#exp-codigo').text(b.codigo);
    $('#exp-numero').val('');
    $('#exp-asunto').val(`Baja de activo ${b.activo_codigo ?? ''} (${b.codigo})`.trim());

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalExpediente')).show();
  });

  $('#form-expediente').on('submit', function (e) {
    e.preventDefault();
    enviar(
      window.routesBajas.expediente.replace('{id}', idExpediente),
      'PUT',
      $(this).serialize(),
      'modalExpediente',
      'No se pudo vincular el expediente.'
    );
  });

  // ═══════════════════════════════════════════
  // APROBAR / EJECUTAR / RECHAZAR / SIGA
  // ═══════════════════════════════════════════
  $(document).on('click', '.btn-aprobar-baja', function () {
    const b = buscar(parseInt($(this).data('id')));
    if (!b) return;

    Swal.fire({
      icon: 'question',
      title: `¿Aprobar ${b.codigo}?`,
      html: `Expediente: <strong>${b.expediente ?? 'SIN EXPEDIENTE'}</strong>.<br>La baja quedará lista para ejecutarse.`,
      input: 'textarea',
      inputLabel: 'Observación de aprobación (opcional)',
      showCancelButton: true,
      confirmButtonText: 'Aprobar',
      cancelButtonText: 'Volver',
      customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(r => {
      if (!r.isConfirmed) return;
      enviar(
        window.routesBajas.aprobar.replace('{id}', b.id_baja),
        'PUT',
        { observaciones: r.value || '' },
        null,
        'No se pudo aprobar la baja.'
      );
    });
  });

  $(document).on('click', '.btn-ejecutar-baja', function () {
    const b = buscar(parseInt($(this).data('id')));
    if (!b) return;

    Swal.fire({
      icon: 'warning',
      title: `¿Ejecutar la baja ${b.codigo}?`,
      html: `El activo <strong>${b.activo_codigo}</strong> pasará a <strong>DADO DE BAJA</strong>:<br>` +
        'sin responsable, sin movimientos ni mantenimientos posibles. Esta acción no se puede deshacer.',
      showCancelButton: true,
      confirmButtonText: 'Sí, ejecutar baja',
      cancelButtonText: 'Volver',
      customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(r => {
      if (!r.isConfirmed) return;
      enviar(window.routesBajas.ejecutar.replace('{id}', b.id_baja), 'PUT', {}, null, 'No se pudo ejecutar la baja.');
    });
  });

  $(document).on('click', '.btn-rechazar-baja', function () {
    const b = buscar(parseInt($(this).data('id')));
    if (!b) return;

    Swal.fire({
      icon: 'warning',
      title: `¿Rechazar ${b.codigo}?`,
      text: 'El activo volverá a su situación operativa (EN USO o EN ALMACÉN).',
      input: 'textarea',
      inputLabel: 'Motivo del rechazo',
      inputValidator: v => (!v || !v.trim() ? 'Indica el motivo del rechazo.' : undefined),
      showCancelButton: true,
      confirmButtonText: 'Rechazar propuesta',
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

  $(document).on('click', '.btn-siga-baja', function () {
    const b = buscar(parseInt($(this).data('id')));
    if (!b) return;

    Swal.fire({
      icon: 'question',
      title: `Marcar ${b.codigo} en SIGA`,
      input: 'select',
      inputOptions: { REGISTRADO: 'Registrado en SIGA', OBSERVADO: 'Observado por Patrimonio' },
      inputValue: 'REGISTRADO',
      showCancelButton: true,
      confirmButtonText: 'Guardar',
      cancelButtonText: 'Volver',
      customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(r => {
      if (!r.isConfirmed) return;
      enviar(
        window.routesBajas.siga.replace('{id}', b.id_baja),
        'PUT',
        { estado_siga: r.value },
        null,
        'No se pudo actualizar el estado SIGA.'
      );
    });
  });
});
