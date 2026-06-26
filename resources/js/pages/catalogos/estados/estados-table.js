import dtDefaults from '../../../plugins/datatables-defaults';
import { initTooltips } from '../../../plugins/bootstrap-tooltips';

$(function () {
  // ─── Tabla Condiciones ───────────────────────────────────────────────────────
  window.tablaCondiciones = $('#miTablaCondiciones').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.condiciones,
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
          const urlToggle = window.routes.toggleEstado.replace('{id}', row.id_estado_activo);

          const btnToggle =
            row.estado === 'ACTIVO'
              ? `<button type="button" class="btn-action-icon action-deactivate btn-toggle-estado"
                   data-id="${row.id_estado_activo}" data-estado="ACTIVO" data-url="${urlToggle}" data-tabla="condiciones"
                   data-bs-toggle="tooltip" title="Desactivar">
                   <i class="bx bx-power-off"></i></button>`
              : `<button type="button" class="btn-action-icon action-activate btn-toggle-estado"
                   data-id="${row.id_estado_activo}" data-estado="INACTIVO" data-url="${urlToggle}" data-tabla="condiciones"
                   data-bs-toggle="tooltip" title="Activar">
                   <i class="bx bx-check-shield"></i></button>`;

          return `
            <div class="d-flex gap-2">
              <button type="button" class="btn-action-icon action-edit btn-editar-estado"
                data-id="${row.id_estado_activo}"
                data-nombre="${row.nombre}"
                data-tipo="${row.tipo}"
                data-descripcion="${row.descripcion ?? ''}"
                data-tabla="condiciones"
                data-bs-toggle="tooltip" title="Editar">
                <i class="bx bx-edit-alt"></i>
              </button>
              ${btnToggle}
            </div>`;
        }
      }
    ]
  });

  window.tablaCondiciones.on('draw.dt', initTooltips);

  // ─── Tabla Situaciones ───────────────────────────────────────────────────────
  window.tablaSituaciones = $('#miTablaSituaciones').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.situaciones,
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
          const urlToggle = window.routes.toggleEstado.replace('{id}', row.id_estado_activo);

          const btnToggle =
            row.estado === 'ACTIVO'
              ? `<button type="button" class="btn-action-icon action-deactivate btn-toggle-estado"
                   data-id="${row.id_estado_activo}" data-estado="ACTIVO" data-url="${urlToggle}" data-tabla="situaciones"
                   data-bs-toggle="tooltip" title="Desactivar">
                   <i class="bx bx-power-off"></i></button>`
              : `<button type="button" class="btn-action-icon action-activate btn-toggle-estado"
                   data-id="${row.id_estado_activo}" data-estado="INACTIVO" data-url="${urlToggle}" data-tabla="situaciones"
                   data-bs-toggle="tooltip" title="Activar">
                   <i class="bx bx-check-shield"></i></button>`;

          return `
            <div class="d-flex gap-2">
              <button type="button" class="btn-action-icon action-edit btn-editar-estado"
                data-id="${row.id_estado_activo}"
                data-nombre="${row.nombre}"
                data-tipo="${row.tipo}"
                data-descripcion="${row.descripcion ?? ''}"
                data-tabla="situaciones"
                data-bs-toggle="tooltip" title="Editar">
                <i class="bx bx-edit-alt"></i>
              </button>
              ${btnToggle}
            </div>`;
        }
      }
    ]
  });

  window.tablaSituaciones.on('draw.dt', initTooltips);
  initTooltips();
});
