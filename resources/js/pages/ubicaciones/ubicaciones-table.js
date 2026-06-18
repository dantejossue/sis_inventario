import dtDefaults from '../../plugins/datatables-defaults';
import { initTooltips } from '../../plugins/bootstrap-tooltips';

const tipoBadge = {
  EDIFICIO: 'bg-label-primary',
  PISO: 'bg-label-info',
  AMBIENTE: 'bg-label-secondary',
  ALMACEN: 'bg-label-warning',
  OTRO: 'bg-label-dark'
};

$(function () {
  window.tablaUbicaciones = $('#miTablaUbicaciones').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.ubicaciones,
    order: [[1, 'asc']],
    columns: [
      {
        data: null,
        orderable: false,
        searchable: false,
        render: (_d, _t, _r, meta) => `<strong>${meta.row + 1}</strong>`
      },
      {
        // Nombre indentado según el nivel del árbol + ruta completa como subtítulo
        data: 'ruta',
        render: (_d, _t, row) => {
          const codigo = row.codigo ? ` <span class="badge bg-label-dark">${row.codigo}</span>` : '';
          return (
            `<div><span class="fw-semibold">${row.nombre}</span>${codigo}</div>` +
            `<small class="text-muted">${row.ruta}</small>`
          );
        }
      },
      {
        data: 'sede_nombre',
        render: d => d ?? '<span class="text-muted">—</span>'
      },
      {
        data: 'tipo',
        render: t => `<span class="badge ${tipoBadge[t] ?? 'bg-label-secondary'}">${t}</span>`
      },
      {
        data: 'activos_count',
        render: c =>
          c > 0
            ? `<span class="badge bg-label-success">${c} activo${c !== 1 ? 's' : ''}</span>`
            : '<span class="text-muted">0</span>'
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
          const urlToggle = window.routes.toggleEstado.replace('{id}', row.id_ubicacion);

          const btnToggle =
            row.estado === 'ACTIVO'
              ? `<button type="button" class="btn-action-icon action-deactivate btn-toggle-estado"
                   data-id="${row.id_ubicacion}" data-estado="ACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Desactivar ubicación">
                   <i class="bx bx-power-off"></i></button>`
              : `<button type="button" class="btn-action-icon action-activate btn-toggle-estado"
                   data-id="${row.id_ubicacion}" data-estado="INACTIVO" data-url="${urlToggle}"
                   data-bs-toggle="tooltip" title="Activar ubicación">
                   <i class="bx bx-check-shield"></i></button>`;

          return `
            <div class="d-flex gap-2">
              <button type="button" class="btn-action-icon action-edit btn-editar"
                data-id="${row.id_ubicacion}"
                data-sede="${row.id_sede}"
                data-padre="${row.id_ubicacion_padre ?? ''}"
                data-nombre="${row.nombre}"
                data-tipo="${row.tipo}"
                data-codigo="${row.codigo ?? ''}"
                data-descripcion="${row.descripcion ?? ''}"
                data-bs-toggle="tooltip" title="Editar ubicación">
                <i class="bx bx-edit-alt"></i>
              </button>

              ${btnToggle}
            </div>`;
        }
      }
    ]
  });

  window.tablaUbicaciones.on('draw.dt', initTooltips);
  initTooltips();
});
