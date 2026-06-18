import dtDefaults from '../../../plugins/datatables-defaults';
import { initTooltips } from '../../../plugins/bootstrap-tooltips';

$(function () {
  window.tablaCategorias = $('#miTablaCategorias').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.categorias,
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
        data: 'descripcion',
        render: d => d ?? '<span class="text-muted">—</span>'
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
          const urlToggle = window.routes.toggleEstado.replace('{id}', row.id_categoria);

          const btnToggle =
            row.estado === 'ACTIVO'
              ? `<button type="button" class="btn-action-icon action-deactivate btn-toggle-estado"
                   data-id="${row.id_categoria}" data-estado="ACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Desactivar categoría">
                   <i class="bx bx-power-off"></i></button>`
              : `<button type="button" class="btn-action-icon action-activate btn-toggle-estado"
                   data-id="${row.id_categoria}" data-estado="INACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Activar categoría">
                   <i class="bx bx-check-shield"></i></button>`;

          return `
            <div class="d-flex gap-2">
              <button type="button" class="btn-action-icon action-edit btn-editar"
                data-id="${row.id_categoria}"
                data-nombre="${row.nombre}"
                data-descripcion="${row.descripcion ?? ''}"
                data-bs-toggle="tooltip" title="Editar categoría">
                <i class="bx bx-edit-alt"></i>
              </button>
              ${btnToggle}
            </div>`;
        }
      }
    ]
  });

  window.tablaCategorias.on('draw.dt', initTooltips);
  initTooltips();
});
