import dtDefaults from '../../plugins/datatables-defaults';
import { initTooltips } from '../../plugins/bootstrap-tooltips';

const tipoBadge = {
  ADMINISTRATIVO: 'bg-label-primary',
  DOCENTE:        'bg-label-info',
  TECNICO:        'bg-label-warning',
  PRACTICANTE:    'bg-label-success',
  EXTERNO:        'bg-label-secondary',
};

$(function () {
  window.tablaColaboradores = $('#tablaColaboradores').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.colaboradores,
    columns: [
      {
        data: null,
        render: (_d, _t, _r, meta) => `<strong>${meta.row + 1}</strong>`
      },
      {
        data: null,
        render: function (row) {
          const foto = row.per_foto
            ? `${window.storageUrl}/${row.per_foto}`
            : window.avatarDefault;
          const nombre = `${row.per_nombre} ${row.per_apepat} ${row.per_apemat ?? ''}`.trim();
          return `
            <div class="d-flex align-items-center">
              <div class="avatar avatar-sm me-3">
                <img src="${foto}" class="rounded-circle" style="object-fit:cover;">
              </div>
              <div>
                <h6 class="mb-0">${nombre}</h6>
                <small class="text-muted">${row.per_email ?? 'Sin correo'}</small>
              </div>
            </div>`;
        }
      },
      { data: 'nro_documento' },
      {
        data: 'cargo',
        render: c => c ? `<span class="text-uppercase">${c}</span>` : '<span class="text-muted">—</span>'
      },
      {
        data: 'sede_dependencia',
        defaultContent: '<span class="text-muted">—</span>',
        render: function (sd) {
          if (!sd?.dependencia) return '<span class="text-muted">—</span>';
          return `<span title="${sd.dependencia.nombre_dependencia}">${sd.dependencia.nombre_dependencia}</span>`;
        }
      },
      {
        data: 'tipo_colaborador',
        render: function (tipo) {
          const cls = tipoBadge[tipo] ?? 'bg-label-secondary';
          return `<span class="badge ${cls}">${tipo}</span>`;
        }
      },
      {
        data: 'estado',
        render: e => e === 'ACTIVO'
          ? '<span class="badge bg-success fw-bold">Activo</span>'
          : '<span class="badge bg-danger fw-bold">Inactivo</span>'
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (row) {
          const urlEdit   = window.routesColab.edit.replace('{id}', row.id_colaborador);
          const urlToggle = window.routesColab.toggleEstado.replace('{id}', row.id_colaborador);

          const btnToggle = row.estado === 'ACTIVO'
            ? `<button type="button" class="btn-action-icon action-deactivate btn-toggle-colab"
                 data-id="${row.id_colaborador}" data-estado="ACTIVO" data-url="${urlToggle}"
                 data-bs-toggle="tooltip" title="Desactivar">
                 <i class="bx bx-power-off"></i></button>`
            : `<button type="button" class="btn-action-icon action-activate btn-toggle-colab"
                 data-id="${row.id_colaborador}" data-estado="INACTIVO" data-url="${urlToggle}"
                 data-bs-toggle="tooltip" title="Activar">
                 <i class="bx bx-check-shield"></i></button>`;

          return `
            <div class="d-flex gap-2">
              <a href="${urlEdit}" class="btn-action-icon action-edit"
                 data-bs-toggle="tooltip" title="Editar colaborador">
                <i class="bx bx-edit-alt"></i>
              </a>
              ${btnToggle}
            </div>`;
        }
      }
    ]
  });

  window.tablaColaboradores.on('draw.dt', initTooltips);
  initTooltips();
});
