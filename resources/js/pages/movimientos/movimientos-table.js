import dtDefaults from '../../plugins/datatables-defaults';
import { initTooltips } from '../../plugins/bootstrap-tooltips';
import $ from 'jquery';
import Swal from 'sweetalert2';

// Tipo de movimiento interno OTI (brief §13)
const tipoBadge = {
  PRESTAMO: 'bg-label-warning',
  TRANSFERENCIA: 'bg-label-info',
  REGULARIZACION: 'bg-label-primary'
};

const tipoLabel = {
  PRESTAMO: 'Préstamo',
  TRANSFERENCIA: 'Transferencia',
  REGULARIZACION: 'Regularización'
};

// Estado del movimiento
const estadoBadge = {
  BORRADOR: 'bg-label-secondary',
  EJECUTADO: 'bg-success',
  OBSERVADO: 'bg-label-warning',
  CANCELADO: 'bg-label-dark'
};

// Estado de la devolución (solo préstamos)
const devolucionBadge = {
  NO_APLICA: 'bg-label-secondary',
  PENDIENTE_DEVOLUCION: 'bg-label-warning',
  DEVUELTO: 'bg-label-success',
  DEVUELTO_OBSERVADO: 'bg-label-danger',
  VENCIDO: 'bg-label-danger'
};

const devolucionLabel = {
  NO_APLICA: '—',
  PENDIENTE_DEVOLUCION: 'Pendiente',
  DEVUELTO: 'Devuelto',
  DEVUELTO_OBSERVADO: 'Devuelto (obs.)',
  VENCIDO: 'Vencido'
};

const dash = '<span class="text-muted">—</span>';

$(function () {
  window.tablaMovimientos = $('#miTablaMovimientos').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.movimientos,
    order: [],
    columns: [
      {
        data: 'codigo',
        render: d => `<span class="fw-semibold">${d}</span>`
      },
      {
        data: 'tipo',
        render: t => `<span class="badge ${tipoBadge[t] ?? 'bg-label-secondary'}">${tipoLabel[t] ?? t}</span>`
      },
      {
        data: null,
        orderable: false,
        render: row => row.colaborador_origen || row.ubicacion_origen || dash
      },
      {
        data: null,
        orderable: false,
        render: row => row.colaborador_destino || row.ubicacion_destino || dash
      },
      {
        data: 'estado',
        render: e =>
          `<span class="badge ${estadoBadge[e] ?? 'bg-label-secondary'} fw-bold">${(e ?? '').replace(/_/g, ' ')}</span>`
      },
      {
        // Devolución (reemplaza la antigua columna SIGA, fuera de alcance)
        data: 'estado_devolucion',
        render: (e, t, row) => {
          if (!e || e === 'NO_APLICA') return dash;
          const cls = devolucionBadge[e] ?? 'bg-label-secondary';
          const fechas = row.fecha_devolucion_real
            ? `<small class="text-muted d-block">Devuelto: ${row.fecha_devolucion_real}</small>`
            : row.fecha_devolucion_estimada
              ? `<small class="text-muted d-block">Est.: ${row.fecha_devolucion_estimada}</small>`
              : '';
          return `<span class="badge ${cls} fw-bold">${devolucionLabel[e] ?? e}</span>${fechas}`;
        }
      },
      {
        data: 'registrado_por',
        render: d => d ?? dash
      },
      {
        data: 'fecha',
        render: d => d ?? dash
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-end',
        render: row => {
          let items = `<li><a class="dropdown-item btn-detalle-mov d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_movimiento}"><i class="bx bx-show me-1"></i> Ver detalle</a></li>`;
          if (row.es_prestamo_pendiente) {
            items += `<li><a class="dropdown-item btn-devolver d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_movimiento}" data-codigo="${row.codigo}"><i class="bx bx-undo me-1"></i> Devolución</a></li>`;
          }
          items += `<li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger btn-eliminar-mov d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_movimiento}" data-codigo="${row.codigo}"><i class="bx bx-trash me-1"></i> Eliminar</a></li>`;
          return `<div class="dropdown">
              <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">${items}</ul>
            </div>`;
        }
      }
    ]
  });

  const buscarMov = id => window.movimientos.find(m => m.id_movimiento === id);

  window.tablaMovimientos.on('draw.dt', initTooltips);
  initTooltips();

  // ── Ver detalle ────────────────────────────────────────────────────
  $(document).on('click', '.btn-detalle-mov', function () {
    const m = buscarMov(parseInt($(this).data('id')));
    if (!m) return;
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    set('det-mov-codigo', m.codigo);
    set('det-mov-sub', `${tipoLabel[m.tipo] ?? m.tipo} · ${m.fecha ?? '—'}`);
    set('det-mov-tipo', tipoLabel[m.tipo] ?? m.tipo);
    set('det-mov-estado', (m.estado ?? '').replace(/_/g, ' '));
    set('det-mov-devolucion', devolucionLabel[m.estado_devolucion] ?? '—');
    set('det-mov-responsable', m.registrado_por ?? '—');
    set('det-mov-origen', m.colaborador_origen || m.ubicacion_origen || '—');
    set('det-mov-destino', m.colaborador_destino || m.ubicacion_destino || '—');
    set('det-mov-fecha', m.fecha ?? '—');
    set('det-mov-devfechas', `${m.fecha_devolucion_estimada ?? '—'} / ${m.fecha_devolucion_real ?? '—'}`);
    set('det-mov-motivo', m.motivo || m.observaciones || '—');
    document.getElementById('det-mov-activos').innerHTML =
      (m.activos || []).map(c => `<span class="badge bg-label-dark">${c}</span>`).join('') || '—';
    document.getElementById('det-mov-sustento').innerHTML = m.sustento
      ? `<a href="${m.sustento.url}" class="btn btn-sm btn-outline-primary"><i class="bx bx-download me-1"></i>${m.sustento.nombre}</a>`
      : '<span class="text-muted">Sin documento.</span>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleMov')).show();
  });

  // ── Eliminar movimiento ────────────────────────────────────────────
  $(document).on('click', '.btn-eliminar-mov', function () {
    const id = $(this).data('id');
    const codigo = $(this).data('codigo');
    Swal.fire({
      icon: 'warning',
      title: `¿Eliminar ${codigo}?`,
      html: 'Se eliminará el registro del movimiento y su documento de sustento.<br><small class="text-muted">No revierte el estado actual de los activos.</small>',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#d33'
    }).then(r => {
      if (!r.isConfirmed) return;
      $.ajax({
        url: window.routes.destroy.replace('__ID__', id),
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: res => {
          window.movimientos = window.movimientos.filter(m => m.id_movimiento !== id);
          window.tablaMovimientos.clear().rows.add(window.movimientos).draw(false);
          Swal.fire({ icon: 'success', title: 'Eliminado', text: res.message, timer: 2000, showConfirmButton: false });
        },
        error: () => Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar el movimiento.' })
      });
    });
  });

  // ── Filtros (tipo / devolución / responsable / rango de fechas) ─────
  if ($.fn.select2) {
    $('#filtro-responsable').select2({ width: '100%', placeholder: 'Todos', allowClear: true });
  }
  $.fn.dataTable.ext.search.push((settings, _data, dataIndex) => {
    if (settings.nTable.id !== 'miTablaMovimientos') return true;
    const row = window.tablaMovimientos.row(dataIndex).data();
    const tipo = $('#filtro-tipo').val();
    if (tipo && row.tipo !== tipo) return false;
    const dev = $('#filtro-devolucion').val();
    if (dev && row.estado_devolucion !== dev) return false;
    const resp = $('#filtro-responsable').val();
    if (resp && String(row.registrado_por_id) !== String(resp)) return false;
    const ini = $('#filtro-fecha-inicio').val();
    const fin = $('#filtro-fecha-fin').val();
    const fecha = row.fecha_registro || (row.fecha ? row.fecha.slice(0, 10) : null);
    if (ini && (!fecha || fecha < ini)) return false;
    if (fin && (!fecha || fecha > fin)) return false;
    return true;
  });
  $('#filtro-tipo, #filtro-devolucion, #filtro-responsable, #filtro-fecha-inicio, #filtro-fecha-fin')
    .on('change', () => window.tablaMovimientos.draw());
  $('#filtro-reset').on('click', function () {
    $('#filtro-tipo, #filtro-devolucion, #filtro-fecha-inicio, #filtro-fecha-fin').val('');
    $('#filtro-responsable').val('').trigger('change');
    window.tablaMovimientos.draw();
  });

  // ── Devolución de préstamo ─────────────────────────────────────────
  $(document).on('click', '.btn-devolver', function () {
    const id = $(this).data('id');
    const codigo = $(this).data('codigo');

    Swal.fire({
      title: `Devolver préstamo ${codigo}`,
      html: `
        <div class="text-start">
          <label class="form-label mt-2">Condición de retorno</label>
          <select id="swal-condicion" class="form-select">
            <option value="BUENO">Bueno</option>
            <option value="NUEVO">Nuevo</option>
            <option value="REGULAR">Regular</option>
            <option value="MALO">Malo</option>
          </select>
          <label class="form-label mt-3">Resultado</label>
          <select id="swal-estado" class="form-select">
            <option value="DEVUELTO">Conforme</option>
            <option value="DEVUELTO_OBSERVADO">Observado</option>
          </select>
          <label class="form-label mt-3">Observación (opcional)</label>
          <textarea id="swal-obs" class="form-control" rows="2"></textarea>
        </div>`,
      showCancelButton: true,
      confirmButtonText: 'Registrar devolución',
      cancelButtonText: 'Cancelar',
      preConfirm: () => ({
        condicion_retorno: document.getElementById('swal-condicion').value,
        estado_devolucion: document.getElementById('swal-estado').value,
        observacion_devolucion: document.getElementById('swal-obs').value
      })
    }).then(result => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: window.routes.devolver.replace('__ID__', id),
        type: 'PUT',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: result.value,
        success: res => {
          Swal.fire({ icon: 'success', title: 'Devolución registrada', text: res.message, timer: 2200, showConfirmButton: false })
            .then(() => window.location.reload());
        },
        error: xhr => {
          const msg = xhr.responseJSON?.message
            || Object.values(xhr.responseJSON?.errors ?? {})[0]?.[0]
            || 'No se pudo registrar la devolución.';
          Swal.fire({ icon: 'error', title: 'Error', text: msg });
        }
      });
    });
  });
});
