import dtDefaults from '../../plugins/datatables-defaults';
import { initTooltips } from '../../plugins/bootstrap-tooltips';

const rolBadge = {
  ADMINISTRADOR:
    '<span class="badge bg-label-primary d-flex justify-content-center align-items-center"><i class="bx bx-crown me-1"></i>Administrador</span>',
  ALMACEN:
    '<span class="badge bg-label-info d-flex justify-content-center align-items-center"><i class="bx bx-store me-1"></i>Almacén</span>',
  USG: '<span class="badge bg-label-warning d-flex justify-content-center align-items-center"><i class="bx bx-bar-chart-alt-2 me-1"></i>Servicios Generales</span>',
  OPERADOR:
    '<span class="badge bg-label-secondary d-flex justify-content-center align-items-center"><i class="bx bx-wrench me-1"></i>Operador</span>',
  PROVEEDOR:
    '<span class="badge bg-label-secondary d-flex justify-content-center align-items-center"><i class="bx bx-building me-1"></i>Proveedor</span>'
};

$(function () {
  window.tablaUsuarios = $('#miTablaUsuarios').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.usuarios,
    order: [],
    columns: [
      {
        data: null,
        render: (_d, _t, _r, meta) => `<strong>${meta.row + 1}</strong>`
      },
      {
        data: 'colaborador',
        render: function (col) {
          if (!col) return '<span class="text-muted">Sin datos</span>';
          return `
            <div class="d-flex align-items-center">
              <div class="avatar avatar-sm me-3" style="flex-shrink:0;">
                <img src="${window.avatarDefault}" class="rounded-circle">
              </div>
              <div>
                <h6 class="mb-0">${col.per_nombre} ${col.per_apepat} ${col.per_apemat ?? ''}</h6>
                <small class="text-muted">${col.per_email ?? 'Sin correo'}</small>
              </div>
            </div>`;
        }
      },
      { data: 'nombre_usuario' },
      {
        data: 'rol',
        render: function (rol) {
          if (!rol)
            return '<span class="badge bg-label-danger d-flex justify-content-center align-items-center">Sin rol</span>';
          return (
            rolBadge[rol.nombre] ??
            `<span class="badge bg-label-secondary d-flex justify-content-center align-items-center">${rol.nombre}</span>`
          );
        }
      },
      {
        // Laravel serializa sedeDependencia -> sede_dependencia en JSON
        data: 'colaborador.sede_dependencia.dependencia',
        defaultContent: '<span class="text-muted">—</span>',
        render: function (dep) {
          if (!dep) return '<span class="text-muted">—</span>';
          return dep.nombre_dependencia ?? '—';
        }
      },
      {
        data: 'estado',
        render: function (estado) {
          return estado === 'ACTIVO'
            ? '<span class="badge bg-success fw-bold">Activo</span>'
            : '<span class="badge bg-danger fw-bold">Inactivo</span>';
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (row) {
          const urlToggle = window.routes.toggleEstado.replace('{id}', row.id_usuario);
          const esPropioUsuario = row.id_usuario === window.currentUserId;
          const btnToggle = esPropioUsuario
            ? `<button type="button" class="btn-action-icon action-deactivate" disabled
               data-bs-toggle="tooltip" title="No puedes desactivar tu propia cuenta">
               <i class="bx bx-power-off"></i></button>`
            : row.estado === 'ACTIVO'
              ? `<button type="button" class="btn-action-icon action-deactivate btn-toggle-estado"
                 data-id="${row.id_usuario}" data-estado="ACTIVO" data-url="${urlToggle}"
                 data-bs-toggle="tooltip" title="Desactivar acceso">
                 <i class="bx bx-power-off"></i></button>`
              : `<button type="button" class="btn-action-icon action-activate btn-toggle-estado"
                 data-id="${row.id_usuario}" data-estado="INACTIVO" data-url="${urlToggle}"
                 data-bs-toggle="tooltip" title="Activar acceso">
                 <i class="bx bx-check-shield"></i></button>`;

          return `
            <div class="d-flex gap-2">
              <button type="button" class="btn-action-icon action-edit btn-editar"
                data-id="${row.id_usuario}"
                data-nombre-usuario="${row.nombre_usuario}"
                data-id-rol="${row.id_rol}"
                data-nombre-colaborador="${row.colaborador?.per_nombre ?? ''} ${row.colaborador?.per_apepat ?? ''}"
                data-bs-toggle="tooltip" title="Editar usuario">
                <i class="bx bx-edit-alt"></i>
              </button>

              <button type="button" class="btn-action-icon action-password btn-cambiar-pwd"
                data-id="${row.id_usuario}"
                data-nombre-usuario="${row.nombre_usuario}"
                data-bs-toggle="tooltip" title="Cambiar contraseña">
                <i class="bx bx-key"></i>
              </button>

              ${btnToggle}
            </div>`;
        }
      }
    ]
  });

  window.tablaUsuarios.on('draw.dt', initTooltips);
  initTooltips();
});
