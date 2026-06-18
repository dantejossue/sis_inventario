import dtDefaults from '../../../plugins/datatables-defaults';
import { initTooltips } from '../../../plugins/bootstrap-tooltips';

$(function () {
  // ─── Tabla Marcas ────────────────────────────────────────────────────────────
  window.tablaMarcas = $('#miTablaMarcas').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.marcas,
    columns: [
      {
        data: null,
        render: (_d, _t, _r, meta) => `<strong>${meta.row + 1}</strong>`
      },
      {
        data: 'nombre',
        render: n => `<span class="fw-semibold">${n}</span>`
      },
      {
        data: 'modelos_count',
        render: c =>
          `<span class="badge bg-label-info">${c} modelo${c !== 1 ? 's' : ''}</span>`
      },
      {
        data: 'estado',
        render: e =>
          e === 'ACTIVO'
            ? '<span class="badge bg-success fw-bold">Activo</span>'
            : '<span class="badge bg-danger fw-bold">Inactivo</span>'
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (row) {
          const urlToggle = window.routes.toggleEstado.replace('{id}', row.id_marca);

          const btnToggle =
            row.estado === 'ACTIVO'
              ? `<button type="button" class="btn-action-icon action-deactivate btn-toggle-marca"
                   data-id="${row.id_marca}" data-estado="ACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Desactivar marca">
                   <i class="bx bx-power-off"></i></button>`
              : `<button type="button" class="btn-action-icon action-activate btn-toggle-marca"
                   data-id="${row.id_marca}" data-estado="INACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Activar marca">
                   <i class="bx bx-check-shield"></i></button>`;

          return `
            <div class="d-flex gap-2">
              <button type="button" class="btn-action-icon action-edit btn-editar-marca"
                data-id="${row.id_marca}"
                data-nombre="${row.nombre}"
                data-bs-toggle="tooltip" title="Editar marca">
                <i class="bx bx-edit-alt"></i>
              </button>
              ${btnToggle}
            </div>`;
        }
      }
    ]
  });

  window.tablaMarcas.on('draw.dt', initTooltips);

  // ─── Tabla Modelos ───────────────────────────────────────────────────────────
  window.tablaModelos = $('#miTablaModelos').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.modelos,
    columns: [
      {
        data: null,
        render: (_d, _t, _r, meta) => `<strong>${meta.row + 1}</strong>`
      },
      {
        data: 'marca_nombre',
        render: n => `<span class="badge bg-label-secondary">${n}</span>`
      },
      {
        data: 'categoria_nombre',
        render: d => d && d !== '—' ? `<span class="badge bg-label-primary">${d}</span>` : '<span class="text-muted">—</span>'
      },
      {
        data: 'nombre',
        render: n => `<span class="fw-semibold">${n}</span>`
      },
      {
        data: 'descripcion',
        render: d => d ?? '<span class="text-muted">—</span>'
      },
      {
        data: 'estado',
        render: e =>
          e === 'ACTIVO'
            ? '<span class="badge bg-success fw-bold">Activo</span>'
            : '<span class="badge bg-danger fw-bold">Inactivo</span>'
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (row) {
          const urlToggle = window.routes.toggleEstadoModelo.replace('{id}', row.id_modelo);

          const btnToggle =
            row.estado === 'ACTIVO'
              ? `<button type="button" class="btn-action-icon action-deactivate btn-toggle-modelo"
                   data-id="${row.id_modelo}" data-estado="ACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Desactivar modelo">
                   <i class="bx bx-power-off"></i></button>`
              : `<button type="button" class="btn-action-icon action-activate btn-toggle-modelo"
                   data-id="${row.id_modelo}" data-estado="INACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Activar modelo">
                   <i class="bx bx-check-shield"></i></button>`;

          return `
            <div class="d-flex gap-2">
              <button type="button" class="btn-action-icon action-edit btn-editar-modelo"
                data-id="${row.id_modelo}"
                data-id-marca="${row.id_marca}"
                data-id-categoria="${row.id_categoria ?? ''}"
                data-nombre="${row.nombre}"
                data-descripcion="${row.descripcion ?? ''}"
                data-bs-toggle="tooltip" title="Editar modelo">
                <i class="bx bx-edit-alt"></i>
              </button>
              ${btnToggle}
            </div>`;
        }
      }
    ]
  });

  window.tablaModelos.on('draw.dt', initTooltips);
  initTooltips();

  // Recalcular anchos cuando el tab de Modelos se hace visible por primera vez
  document.querySelector('#tab-modelos').addEventListener('shown.bs.tab', function () {
    window.tablaModelos.columns.adjust().draw(false);
  });
});
