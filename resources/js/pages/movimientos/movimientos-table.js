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

const estadoLabel = {
  EJECUTADO: 'Ejecutado',
  CANCELADO: 'Cancelado'
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
        render: t =>
          `<span class="fw-semibold badge ${tipoBadge[t] ?? 'bg-label-secondary'}">${tipoLabel[t] ?? t}</span>`
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
          `<span class="badge ${estadoBadge[e] ?? 'bg-label-secondary'} fw-semibold">${(estadoLabel[e] ?? '').replace(/_/g, ' ')}</span>`
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
          return `<span class="badge ${cls} fw-semibold">${devolucionLabel[e] ?? e}</span>${fechas}`;
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
          let items = `<li><a class="dropdown-item d-flex align-items-center" href="${window.routes.ver.replace('__ID__', row.id_movimiento)}"><i class="bx bx-show me-1"></i> Ver detalle</a></li>`;
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

  function initRowDropdowns() {
    document.querySelectorAll('#miTablaMovimientos [data-bs-toggle="dropdown"]').forEach(element => {
      bootstrap.Dropdown.getOrCreateInstance(element, {
        boundary: document.body,
        popperConfig(defaultConfig) {
          return {
            ...defaultConfig,
            strategy: 'fixed'
          };
        }
      });
    });
  }

  window.tablaMovimientos.on('draw.dt', function () {
    initTooltips();
    initRowDropdowns();
  });
  initTooltips();
  initRowDropdowns();

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
  $('#filtro-tipo, #filtro-devolucion, #filtro-responsable, #filtro-fecha-inicio, #filtro-fecha-fin').on('change', () =>
    window.tablaMovimientos.draw()
  );
  $('#filtro-reset').on('click', function () {
    $('#filtro-tipo, #filtro-devolucion, #filtro-fecha-inicio, #filtro-fecha-fin').val('');
    $('#filtro-responsable').val('').trigger('change');
    window.tablaMovimientos.draw();
  });

  // La devolución se maneja en movimientos-devolucion.js (modal reutilizable).
});
