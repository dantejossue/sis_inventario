import $ from 'jquery';
import Swal from 'sweetalert2';
import dtDefaults from '../../plugins/datatables-defaults';

/**
 * Módulo de mantenimientos (F5): tabla, KPIs, filtros y ciclo
 * Solicitud → Diagnóstico → Atención → Resultado → Cierre.
 */

const tipoBadge = {
  PREVENTIVO: 'bg-label-info',
  CORRECTIVO: 'bg-label-danger',
  GARANTIA: 'bg-label-primary',
  REVISION_TECNICA: 'bg-label-warning'
};

const estadoBadge = {
  SOLICITADO: 'bg-label-primary',
  EN_REVISION: 'bg-label-info',
  EN_MANTENIMIENTO: 'bg-label-warning',
  DERIVADO_PROVEEDOR: 'bg-label-warning',
  ATENDIDO: 'bg-label-success',
  SIN_REPARACION: 'bg-label-danger',
  RECOMENDADO_BAJA: 'bg-label-danger',
  CERRADO: 'bg-success',
  CANCELADO: 'bg-label-dark'
};

const prioridadBadge = {
  BAJA: 'bg-label-secondary',
  MEDIA: 'bg-label-warning',
  ALTA: 'bg-label-danger',
  CRITICA: 'bg-danger'
};

const estadoTexto = {
  SOLICITADO: 'Solicitado',
  EN_REVISION: 'En diagnóstico',
  EN_MANTENIMIENTO: 'En atención',
  DERIVADO_PROVEEDOR: 'Derivado a proveedor',
  ATENDIDO: 'Atendido',
  SIN_REPARACION: 'Sin reparación',
  RECOMENDADO_BAJA: 'Recomendado baja',
  CERRADO: 'Cerrado',
  CANCELADO: 'Cancelado'
};

const origenTexto = {
  USUARIO: 'Área usuaria',
  OTI: 'OTI',
  USG: 'Servicios Generales',
  INVENTARIO: 'Inventario',
  OTRO: 'Otro'
};

// Debe reflejar MantenimientoController::AVANCES
const AVANCES = {
  SOLICITADO: ['EN_REVISION', 'EN_MANTENIMIENTO', 'DERIVADO_PROVEEDOR'],
  EN_REVISION: ['EN_MANTENIMIENTO', 'DERIVADO_PROVEEDOR'],
  EN_MANTENIMIENTO: ['DERIVADO_PROVEEDOR'],
  DERIVADO_PROVEEDOR: ['EN_MANTENIMIENTO']
};

const ABIERTOS = ['SOLICITADO', 'EN_REVISION', 'EN_MANTENIMIENTO', 'DERIVADO_PROVEEDOR'];
const RESULTADOS = ['ATENDIDO', 'SIN_REPARACION', 'RECOMENDADO_BAJA'];

const dash = '<span class="text-muted">—</span>';
const legible = v => (v ?? '').replace(/_/g, ' ');
const fmtFecha = f => (f ? f.split('-').reverse().join('/') : null);
const fmtCosto = c => (c !== null && c !== undefined && c !== '' ? `S/ ${Number(c).toFixed(2)}` : null);
const csrf = () => $('meta[name="csrf-token"]').attr('content');

const buscar = id => window.mantenimientos.find(m => m.id_mantenimiento === id);

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
        render: (d, t, row) =>
          `<span class="fw-semibold d-block">${d}</span>` +
          `<small class="text-muted">${origenTexto[row.origen] ?? ''}</small>`
      },
      {
        data: 'activo_codigo',
        render: (d, t, row) =>
          `<a href="${row.activo_url ?? '#'}" class="fw-semibold d-block">${d ?? '—'}</a>` +
          `<small class="text-muted">${row.activo_modelo || '—'}</small>`
      },
      {
        data: 'tipo',
        render: d => `<span class="badge ${tipoBadge[d] ?? 'bg-label-secondary'}">${legible(d)}</span>`
      },
      {
        data: 'descripcion',
        orderable: false,
        render: (d, t, row) => {
          const problema = d ? `<span class="d-block text-truncate" style="max-width: 240px;">${d}</span>` : dash;
          const diag = row.diagnostico
            ? `<small class="text-muted d-block text-truncate" style="max-width: 240px;">${row.diagnostico}</small>`
            : '';
          return problema + diag;
        }
      },
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
        data: 'prioridad',
        render: d => `<span class="badge ${prioridadBadge[d] ?? 'bg-label-secondary'}">${legible(d)}</span>`
      },
      {
        data: 'estado',
        render: d => `<span class="badge ${estadoBadge[d] ?? 'bg-label-secondary'}">${estadoTexto[d] ?? legible(d)}</span>`
      },
      {
        data: 'costo',
        render: d => fmtCosto(d) ?? '<span class="text-muted">Pendiente</span>'
      },
      {
        data: 'fecha_reporte',
        render: (d, t, row) => {
          if (t === 'sort' || t === 'type') return d ?? '';
          return `<span class="d-block">${fmtFecha(d) ?? '—'}</span>` +
            (row.fecha_fin ? `<small class="text-muted">Fin: ${fmtFecha(row.fecha_fin)}</small>` : '');
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-end',
        render: row => {
          const abierto = ABIERTOS.includes(row.estado);
          const conResultado = RESULTADOS.includes(row.estado);

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
            </li>
            <li>
              <a class="dropdown-item btn-finalizar-mant d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_mantenimiento}">
                <i class="bx bx-check-circle me-1"></i> Finalizar
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger btn-cancelar-mant d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_mantenimiento}">
                <i class="bx bx-x-circle me-1"></i> Cancelar
              </a>
            </li>`;
          }

          if (conResultado) {
            items += `
            <li>
              <a class="dropdown-item btn-cerrar-mant d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_mantenimiento}">
                <i class="bx bx-lock me-1"></i> Cerrar mantenimiento
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
  // KPIs Y ALERTAS
  // ═══════════════════════════════════════════
  function refrescarIndicadores() {
    const data = window.mantenimientos;
    const abiertos = data.filter(m => ABIERTOS.includes(m.estado));
    const enAtencion = data.filter(m => ['EN_MANTENIMIENTO', 'DERIVADO_PROVEEDOR'].includes(m.estado));
    const finalizados = data.filter(m => [...RESULTADOS, 'CERRADO'].includes(m.estado));
    const mesActual = new Date().toISOString().slice(0, 7);

    $('#kpi-abiertos').text(abiertos.length);
    $('#kpi-abiertos-detalle').text(`${enAtencion.length} en atención`);

    const preventivos = data.filter(m => m.tipo === 'PREVENTIVO');
    $('#kpi-preventivos').text(preventivos.length);
    $('#kpi-preventivos-detalle').text(`${preventivos.filter(m => ABIERTOS.includes(m.estado)).length} en curso`);

    const correctivos = data.filter(m => m.tipo === 'CORRECTIVO');
    const criticosAbiertos = data.filter(m => m.prioridad === 'CRITICA' && ABIERTOS.includes(m.estado));
    $('#kpi-correctivos').text(correctivos.length);
    $('#kpi-correctivos-detalle').text(`${criticosAbiertos.length} críticos`);

    $('#kpi-finalizados').text(finalizados.length);
    $('#kpi-finalizados-detalle').text(
      `${finalizados.filter(m => (m.fecha_fin ?? '').startsWith(mesActual)).length} este mes`
    );

    // Mini stats del panel de flujo
    const cerradosOk = data.filter(m => ['ATENDIDO', 'CERRADO'].includes(m.estado) && !m.recomienda_baja);
    $('#stat-atendidos').text(data.length ? Math.round((cerradosOk.length / data.length) * 100) + '%' : '0%');
    $('#stat-criticos').text(criticosAbiertos.length);
    $('#stat-garantia').text(data.filter(m => m.tipo === 'GARANTIA').length);
    $('#stat-baja').text(data.filter(m => m.recomienda_baja || m.estado === 'RECOMENDADO_BAJA').length);

    // Alertas técnicas
    const alertas = [];
    const critCorrectivos = data.filter(m => m.tipo === 'CORRECTIVO' && m.prioridad === 'CRITICA' && ABIERTOS.includes(m.estado));
    if (critCorrectivos.length) {
      alertas.push(['bg-label-danger', 'bx-error', `${critCorrectivos.length} correctivo(s) crítico(s)`, 'Equipos posiblemente fuera de servicio.']);
    }
    const conProveedor = data.filter(m => m.estado === 'DERIVADO_PROVEEDOR');
    if (conProveedor.length) {
      alertas.push(['bg-label-info', 'bx-package', `${conProveedor.length} con proveedor`, 'Garantía o reparación externa en curso.']);
    }
    const recomBaja = data.filter(m => m.estado === 'RECOMENDADO_BAJA');
    if (recomBaja.length) {
      alertas.push(['bg-label-secondary', 'bx-trash', `${recomBaja.length} recomendado(s) para baja`, 'Pendientes de propuesta formal de baja.']);
    }
    const sinTecnico = abiertos.filter(m => !m.tecnico && !m.proveedor);
    if (sinTecnico.length) {
      alertas.push(['bg-label-warning', 'bx-user-x', `${sinTecnico.length} sin técnico asignado`, 'Solicitudes a la espera de asignación.']);
    }

    $('#alertas-tecnicas').html(
      alertas.length
        ? alertas
            .map(
              ([color, icono, titulo, detalle]) => `
                <div class="maintenance-alert-item">
                  <div class="maintenance-alert-icon ${color}"><i class="bx ${icono}"></i></div>
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

    const prioridad = $('#filtro-prioridad').val();
    if (prioridad && row.prioridad !== prioridad) return false;

    const desde = $('#filtro-fecha').val();
    if (desde && (!row.fecha_reporte || row.fecha_reporte < desde)) return false;

    return true;
  });

  $('#filtro-tipo, #filtro-estado, #filtro-prioridad, #filtro-fecha').on('change', () => window.tablaMant.draw());
  $('#filtro-reset').on('click', () => {
    $('#filtro-tipo, #filtro-estado, #filtro-prioridad').val('');
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
  // NUEVO MANTENIMIENTO
  // ═══════════════════════════════════════════
  $('#nuevo-activo').on('change', function () {
    $('#resumen-activo').text($(this).find('option:selected').text().split('—')[0].trim() || 'No seleccionado');
  });
  $('#nuevo-tipo').on('change', function () {
    $('#resumen-tipo').text($(this).find('option:selected').text() || 'No seleccionado');
  });
  $('#nuevo-prioridad').on('change', function () {
    $('#resumen-prioridad').text($(this).find('option:selected').text());
  });

  $('#form-nuevo-mantenimiento').on('submit', function (e) {
    e.preventDefault();
    const form = this;
    const usado = $('#nuevo-activo').val();

    enviar(
      window.routesMant.store,
      'POST',
      $(form).serialize(),
      'modalNuevoMantenimiento',
      'No se pudo registrar el mantenimiento.',
      () => {
        // El activo usado deja de ser elegible hasta que se cierre/cancele
        if (usado) $(`#nuevo-activo option[value="${usado}"]`).remove();
        form.reset();
        $('#resumen-activo, #resumen-tipo').text('No seleccionado');
        $('#resumen-prioridad').text('Media');
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

  $(document).on('click', '.btn-detalle-mant', function () {
    const m = buscar(parseInt($(this).data('id')));
    if (!m) return;

    $('#det-codigo').text(m.codigo);
    $('#det-tipo').attr('class', `badge mb-2 ${tipoBadge[m.tipo] ?? 'bg-label-secondary'}`).text(legible(m.tipo));
    $('#det-titulo').text(m.descripcion ? m.descripcion.split('\n')[0].slice(0, 80) : m.codigo);
    $('#det-subtitulo').text(
      `Reportado el ${fmtFecha(m.fecha_reporte) ?? '—'} · Origen: ${origenTexto[m.origen] ?? '—'}`
    );
    $('#det-prioridad').text(legible(m.prioridad));

    $('#det-activo-modelo').text(m.activo_modelo || m.activo_codigo || '—');
    $('#det-activo-codigo').text(`Código interno: ${m.activo_codigo ?? '—'}`);
    $('#det-activo-patrimonial').text(`Código patrimonial: ${m.activo_patrimonial ?? '—'}`);
    $('#det-activo-responsable').text(
      `Responsable: ${m.activo_responsable ?? 'Sin responsable'} · Situación: ${m.activo_situacion ?? '—'}`
    );
    $('#det-activo-url').attr('href', m.activo_url ?? '#');

    $('#det-descripcion').text(m.descripcion || '—');
    $('#det-diagnostico').text(m.diagnostico || 'Pendiente de diagnóstico.');
    $('#det-resultado').text(m.resultado || 'Pendiente de resultado.');

    $('#det-estado').attr('class', `badge ${estadoBadge[m.estado] ?? 'bg-label-secondary'}`).text(estadoTexto[m.estado] ?? legible(m.estado));
    $('#det-origen').text(origenTexto[m.origen] ?? '—');
    $('#det-solicitante').text(m.solicitado_por || '—');
    $('#det-tecnico').text(m.tecnico || 'Por asignar');
    $('#det-proveedor').text(m.proveedor || '—');
    $('#det-costo').text(fmtCosto(m.costo) ?? 'Pendiente');
    $('#det-registrador').text(m.registrado_por || '—');

    $('#det-fecha-reporte').text(fmtFecha(m.fecha_reporte) ?? '—');
    $('#det-fecha-inicio').text(fmtFecha(m.fecha_inicio) ?? 'No iniciado');
    $('#det-fecha-fin').text(fmtFecha(m.fecha_fin) ?? 'En curso');

    // Evidencias
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
      acciones.push(`<button class="btn btn-primary btn-avance-mant" data-id="${m.id_mantenimiento}" data-bs-dismiss="modal"><i class="bx bx-edit me-1"></i> Avance técnico</button>`);
      acciones.push(`<button class="btn btn-outline-success btn-finalizar-mant" data-id="${m.id_mantenimiento}" data-bs-dismiss="modal"><i class="bx bx-check me-1"></i> Finalizar</button>`);
      acciones.push(`<button class="btn btn-outline-danger btn-cancelar-mant" data-id="${m.id_mantenimiento}" data-bs-dismiss="modal"><i class="bx bx-x-circle me-1"></i> Cancelar</button>`);
    } else if (RESULTADOS.includes(m.estado)) {
      acciones.push(`<button class="btn btn-primary btn-cerrar-mant" data-id="${m.id_mantenimiento}" data-bs-dismiss="modal"><i class="bx bx-lock me-1"></i> Cerrar mantenimiento</button>`);
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

    $('#avance-codigo').text(m.codigo);
    const opciones = (AVANCES[m.estado] ?? [])
      .map(e => `<option value="${e}">${estadoTexto[e]}</option>`)
      .join('');
    $('#avance-estado').html(opciones);
    $('#avance-diagnostico').val(m.diagnostico ?? '');
    $('#avance-proveedor').val(m.proveedor ?? '');
    $('#avance-costo').val(m.costo ?? '');
    $('#avance-tecnico').val(m.id_tecnico ?? '');

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAvance')).show();
  });

  $('#form-avance').on('submit', function (e) {
    e.preventDefault();
    enviar(
      window.routesMant.avanzar.replace('{id}', idAvance),
      'PUT',
      $(this).serialize(),
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

    $('#fin-codigo').text(m.codigo);
    $('#fin-estado').val('ATENDIDO').trigger('change');
    $('#fin-diagnostico').val(m.diagnostico ?? '');
    $('#fin-resultado').val('');
    $('#fin-costo').val(m.costo ?? '');

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalFinalizar')).show();
  });

  $('#fin-estado').on('change', function () {
    $('#fin-alerta-baja').toggleClass('d-none', $(this).val() !== 'RECOMENDADO_BAJA');
  });

  $('#form-finalizar').on('submit', function (e) {
    e.preventDefault();
    enviar(
      window.routesMant.finalizar.replace('{id}', idFinalizar),
      'PUT',
      $(this).serialize(),
      'modalFinalizar',
      'No se pudo finalizar el mantenimiento.'
    );
  });

  // ═══════════════════════════════════════════
  // CERRAR / CANCELAR
  // ═══════════════════════════════════════════
  $(document).on('click', '.btn-cerrar-mant', function () {
    const m = buscar(parseInt($(this).data('id')));
    if (!m) return;

    Swal.fire({
      icon: 'question',
      title: `¿Cerrar ${m.codigo}?`,
      text: 'El cierre valida el resultado registrado y termina el proceso. No se puede reabrir.',
      showCancelButton: true,
      confirmButtonText: 'Sí, cerrar',
      cancelButtonText: 'Volver',
      customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(r => {
      if (!r.isConfirmed) return;
      enviar(window.routesMant.cerrar.replace('{id}', m.id_mantenimiento), 'PUT', {}, null, 'No se pudo cerrar el mantenimiento.');
    });
  });

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
