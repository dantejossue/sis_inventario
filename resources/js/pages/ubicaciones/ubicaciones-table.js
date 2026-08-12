import dtDefaults from '../../plugins/datatables-defaults';
import { initTooltips } from '../../plugins/bootstrap-tooltips';

const tipoBadge = {
  EDIFICIO: 'bg-label-primary',
  PISO: 'bg-label-info',
  AMBIENTE: 'bg-label-secondary',
  ALMACEN: 'bg-label-warning',
  OTRO: 'bg-label-dark'
};

const tipoIcono = {
  EDIFICIO: 'bx-buildings',
  PISO: 'bx-layer',
  AMBIENTE: 'bx-door-open',
  ALMACEN: 'bx-package',
  OTRO: 'bx-map-pin'
};

$(function () {
  window.tablaUbicaciones = $('#miTablaUbicaciones').DataTable({
    ...dtDefaults,

    serverSide: false,
    processing: false,
    ajax: null,
    data: window.ubicaciones,

    // Muy importante para conservar el orden del árbol
    order: [],

    columns: [
      // UBICACIÓN
      {
        data: 'nombre',
        render: (_d, type, row) => {
          if (type !== 'display') {
            return `${row.sede_nombre} ${row.ruta ?? ''} ${row.nombre}`;
          }

          const codigo = row.codigo ? `<span class="badge bg-label-dark ms-2">${row.codigo}</span>` : '';

          const nivel = row.nivel ?? 0;

          // +1 porque visualmente la SEDE será el nivel raíz
          const indentacion = (nivel + 1) * 24;

          const icono = tipoIcono[row.tipo] ?? 'bx-map-pin';

          return `
            <div
              class="d-flex align-items-center"
              style="padding-left: ${indentacion}px;"
              data-bs-toggle="tooltip"
              title="${row.ruta ?? row.nombre}"
            >
              <i class="bx bx-subdirectory-right text-muted me-2"></i>

              <i class="bx ${icono} text-muted me-2"></i>

              <span class="fw-semibold">
                ${row.nombre}
              </span>

              ${codigo}
            </div>
          `;
        }
      },

      // TIPO
      {
        data: 'tipo',
        render: tipo => `
          <span class="badge ${tipoBadge[tipo] ?? 'bg-label-secondary'}">
            ${tipo}
          </span>
        `
      },

      // ACTIVOS
      {
        data: 'activos_count',
        render: cantidad =>
          cantidad > 0
            ? `
              <span class="badge bg-label-success">
                ${cantidad} activo${cantidad !== 1 ? 's' : ''}
              </span>
            `
            : '<span class="text-muted">0</span>'
      },

      // ESTADO
      {
        data: 'estado',
        render: estado =>
          estado === 'ACTIVO'
            ? '<span class="badge bg-success fw-bold">Activo</span>'
            : '<span class="badge bg-danger fw-bold">Inactivo</span>'
      },

      // ACCIONES
      {
        data: null,
        orderable: false,
        searchable: false,

        render: function (row) {
          const urlToggle = window.routes.toggleEstado.replace('{id}', row.id_ubicacion);

          const btnToggle =
            row.estado === 'ACTIVO'
              ? `
                <button
                  type="button"
                  class="btn-action-icon action-deactivate btn-toggle-estado"
                  data-id="${row.id_ubicacion}"
                  data-estado="ACTIVO"
                  data-url="${urlToggle}"
                  data-bs-toggle="tooltip"
                  title="Desactivar ubicación"
                >
                  <i class="bx bx-power-off"></i>
                </button>
              `
              : `
                <button
                  type="button"
                  class="btn-action-icon action-activate btn-toggle-estado"
                  data-id="${row.id_ubicacion}"
                  data-estado="INACTIVO"
                  data-url="${urlToggle}"
                  data-bs-toggle="tooltip"
                  title="Activar ubicación"
                >
                  <i class="bx bx-check-shield"></i>
                </button>
              `;

          return `
            <div class="d-flex gap-2">

              <button
                type="button"
                class="btn-action-icon action-edit btn-editar"
                data-id="${row.id_ubicacion}"
                data-sede="${row.id_sede}"
                data-padre="${row.id_ubicacion_padre ?? ''}"
                data-nombre="${row.nombre}"
                data-tipo="${row.tipo}"
                data-codigo="${row.codigo ?? ''}"
                data-descripcion="${row.descripcion ?? ''}"
                data-bs-toggle="tooltip"
                title="Editar ubicación"
              >
                <i class="bx bx-edit-alt"></i>
              </button>

              ${btnToggle}

            </div>
          `;
        }
      }
    ],

    /*
     * Dibujamos la SEDE como nodo raíz.
     */
    drawCallback: function () {
      const api = this.api();

      let sedeAnterior = null;

      api.rows({ page: 'current' }).every(function () {
        const row = this.data();
        const nodo = $(this.node());

        if (row.sede_nombre !== sedeAnterior) {
          sedeAnterior = row.sede_nombre;

          const filaSede = `
            <tr class="fila-sede">
              <td colspan="5">
                <div class="d-flex align-items-center fw-semibold py-1">

                  <i class="bx bx-map-pin text-primary me-2 fs-5"></i>

                  <span>
                    ${row.sede_nombre}
                  </span>

                </div>
              </td>
            </tr>
          `;

          nodo.before(filaSede);
        }
      });

      initTooltips();
    }
  });

  initTooltips();
});
