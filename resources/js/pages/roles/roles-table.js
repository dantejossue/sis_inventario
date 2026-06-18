import dtDefaults from '../../plugins/datatables-defaults';
import { initTooltips } from '../../plugins/bootstrap-tooltips';

$(function () {
  window.tablaRoles = $('#miTablaRoles').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.roles,
    columns: [
      {
        data: null,
        render: (_d, _t, _r, meta) => `<strong>${meta.row + 1}</strong>`
      },
      {
        data: 'nombre',
        render: nombre => `<span class="fw-semibold">${nombre}</span>`
      },
      {
        data: 'descripcion',
        defaultContent: '<span class="text-muted">—</span>',
        render: d => d ?? '<span class="text-muted">—</span>'
      },
      {
        data: 'estado',
        render: estado =>
          estado === 'ACTIVO'
            ? '<span class="badge bg-success fw-bold">Activo</span>'
            : '<span class="badge bg-danger fw-bold">Inactivo</span>'
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (row) {
          const urlToggle = window.routes.toggleEstado.replace('{id}', row.id_rol);

          const btnToggle =
            row.estado === 'ACTIVO'
              ? `<button type="button" class="btn-action-icon action-deactivate btn-toggle-estado"
                   data-id="${row.id_rol}" data-estado="ACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Desactivar rol">
                   <i class="bx bx-power-off"></i></button>`
              : `<button type="button" class="btn-action-icon action-activate btn-toggle-estado"
                   data-id="${row.id_rol}" data-estado="INACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Activar rol">
                   <i class="bx bx-check-shield"></i></button>`;

          return `
            <div class="d-flex gap-2">
              <button type="button" class="btn-action-icon action-edit btn-editar"
                data-id="${row.id_rol}"
                data-nombre="${row.nombre}"
                data-descripcion="${row.descripcion ?? ''}"
                data-bs-toggle="tooltip" title="Editar rol">
                <i class="bx bx-edit-alt"></i>
              </button>

              ${btnToggle}
            </div>`;
        }
      }
    ]
  });

  window.tablaRoles.on('draw.dt', initTooltips);
  initTooltips();
});
