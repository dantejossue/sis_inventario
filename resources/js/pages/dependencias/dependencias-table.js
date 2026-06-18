import dtDefaults from '../../plugins/datatables-defaults';
import { initTooltips } from '../../plugins/bootstrap-tooltips';

$(function () {
  window.tablaDependencias = $('#miTablaDependencias').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.dependencias,
    columns: [
      { data: null, render: (_d, _t, _r, meta) => `<strong>${meta.row + 1}</strong>` },
      { data: 'nombre_dependencia', render: n => `<span class="fw-semibold">${n}</span>` },
      {
        data: 'descripcion',
        defaultContent: '<span class="text-muted">—</span>',
        render: d => d ?? '<span class="text-muted">—</span>'
      },
      {
        data: 'sedes_count',
        render: c => `<span class="badge bg-label-secondary">${c} sede${c !== 1 ? 's' : ''}</span>`
      },
      {
        data: 'estado',
        render: e =>
          e === 'ACTIVO'
            ? '<span class="badge bg-success fw-bold">Activo</span>'
            : '<span class="badge bg-danger fw-bold">Inactivo</span>'
      },
      {
        data: null, orderable: false, searchable: false,
        render: function (row) {
          const urlToggle = window.routes.toggleEstado.replace('{id}', row.id_dependencia);
          const btnToggle =
            row.estado === 'ACTIVO'
              ? `<button type="button" class="btn-action-icon action-deactivate btn-toggle-estado"
                   data-id="${row.id_dependencia}" data-estado="ACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Desactivar dependencia"><i class="bx bx-power-off"></i></button>`
              : `<button type="button" class="btn-action-icon action-activate btn-toggle-estado"
                   data-id="${row.id_dependencia}" data-estado="INACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Activar dependencia"><i class="bx bx-check-shield"></i></button>`;

          return `
            <div class="d-flex gap-2">
              <button type="button" class="btn-action-icon action-edit btn-editar"
                data-id="${row.id_dependencia}"
                data-nombre="${row.nombre_dependencia}"
                data-descripcion="${row.descripcion ?? ''}"
                data-bs-toggle="tooltip" title="Editar dependencia">
                <i class="bx bx-edit-alt"></i>
              </button>
              ${btnToggle}
            </div>`;
        }
      }
    ]
  });

  window.tablaDependencias.on('draw.dt', initTooltips);
  initTooltips();
});
