import dtDefaults from '../../plugins/datatables-defaults';
import { initTooltips } from '../../plugins/bootstrap-tooltips';

const tipoBadge = {
  ASIGNAR: 'bg-label-primary',
  TRANSFERENCIA: 'bg-label-info',
  PRESTAMO: 'bg-label-warning',
  DEVOLUCION: 'bg-label-success',
  REUBICACION: 'bg-label-secondary',
  BAJA: 'bg-label-danger'
};

// Texto vacío seguro
const dash = '<span class="text-muted">—</span>';

$(function () {
  window.tablaMovimientos = $('#miTablaMovimientos').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.movimientos,
    order: [[4, 'desc']],
    columns: [
      {
        data: 'codigo',
        render: d => `<span class="fw-semibold">${d}</span>`
      },
      {
        data: 'tipo',
        render: t => `<span class="badge ${tipoBadge[t] ?? 'bg-label-secondary'}">${t}</span>`
      },
      {
        data: 'activos',
        orderable: false,
        render: arr => {
          if (!arr || !arr.length) return dash;
          const max = 4;
          const chips = arr.slice(0, max).map(c => `<span class="badge bg-label-dark me-1">${c}</span>`).join('');
          const resto = arr.length > max ? `<span class="badge bg-label-secondary">+${arr.length - max}</span>` : '';
          return chips + resto;
        }
      },
      {
        // Origen → destino: muestra colaborador y/o ubicación según corresponda
        data: null,
        orderable: false,
        render: row => {
          const linea = (label, origen, destino) => {
            if (!origen && !destino) return '';
            const o = origen || '—';
            const d = destino || '—';
            return `<div><span class="text-muted">${label}:</span> ${o} <i class="bx bx-right-arrow-alt"></i> <span class="fw-semibold">${d}</span></div>`;
          };
          const colab = linea('Colaborador', row.colaborador_origen, row.colaborador_destino);
          const ubic = linea('Ubicación', row.ubicacion_origen, row.ubicacion_destino);
          return colab + ubic || dash;
        }
      },
      {
        data: 'fecha',
        render: (d, t, row) => {
          if (!d) return dash;
          const dev = row.fecha_devolucion
            ? `<small class="text-muted d-block">Dev. prog.: ${row.fecha_devolucion}</small>`
            : '';
          return `<span>${d}</span>${dev}`;
        }
      },
      {
        data: 'estado',
        render: e =>
          e === 'ABIERTO'
            ? '<span class="badge bg-warning fw-bold">Abierto</span>'
            : '<span class="badge bg-label-secondary fw-bold">Cerrado</span>'
      },
      {
        data: 'registrado_por',
        render: d => d ?? dash
      }
    ]
  });

  window.tablaMovimientos.on('draw.dt', initTooltips);
  initTooltips();
});
