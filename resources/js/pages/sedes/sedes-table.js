import dtDefaults from '../../plugins/datatables-defaults';
import { initTooltips } from '../../plugins/bootstrap-tooltips';

$(function () {
  window.tablaSedes = $('#miTablaSedes').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.sedes,
    order: [],
    columns: [
      {
        data: null,
        orderable: true,
        searchable: false,
        render: (_d, _t, _r, meta) => `<strong>${meta.row + 1}</strong>`
      },
      {
        data: 'nombre_sede',
        render: n => `<span class="fw-semibold">${n}</span>`
      },
      {
        data: 'ubicacion',
        defaultContent: '<span class="text-muted">—</span>',
        render: d => d ?? '<span class="text-muted">sin dirección</span>'
      },
      {
        data: 'dependencias_count',
        render: c => `<span class="badge bg-label-info">${c} dependencia${c !== 1 ? 's' : ''}</span>`
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
          const urlToggle = window.routes.toggleEstado.replace('{id}', row.id_sede);

          const btnToggle =
            row.estado === 'ACTIVO'
              ? `<button type="button" class="btn-action-icon action-deactivate btn-toggle-estado"
                   data-id="${row.id_sede}" data-estado="ACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Desactivar sede">
                   <i class="bx bx-power-off"></i></button>`
              : `<button type="button" class="btn-action-icon action-activate btn-toggle-estado"
                   data-id="${row.id_sede}" data-estado="INACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Activar sede">
                   <i class="bx bx-check-shield"></i></button>`;

          return `
            <div class="d-flex gap-2">
              <button type="button" class="btn-action-icon action-edit btn-editar"
                data-id="${row.id_sede}"
                data-nombre="${row.nombre_sede}"
                data-ubicacion="${row.ubicacion ?? ''}"
                data-bs-toggle="tooltip" title="Editar sede">
                <i class="bx bx-edit-alt"></i>
              </button>

              <button type="button" class="btn-action-icon action-view btn-dependencias"
                data-id="${row.id_sede}"
                data-nombre="${row.nombre_sede}"
                data-bs-toggle="tooltip" title="Gestionar dependencias">
                <i class="bx bx-sitemap"></i>
              </button>

              ${btnToggle}
            </div>`;
        }
      }
    ]
  });

  window.tablaSedes.on('draw.dt', initTooltips);
  initTooltips();
});
