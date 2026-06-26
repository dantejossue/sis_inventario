import dtDefaults from '../../plugins/datatables-defaults';
import { initTooltips } from '../../plugins/bootstrap-tooltips';

$(function () {
  window.activosSeleccionados = new Set();

  const condicionBadge = {
    BUENO: 'bg-success',
    REGULAR: 'bg-warning',
    MALO: 'bg-danger',
    OBSOLETO: 'bg-secondary'
  };

  // Etiqueta (badge suave) de color según la situación del activo
  const situacionBadge = {
    EN_USO: 'bg-label-primary',
    EN_ALMACEN: 'bg-label-success',
    EN_MANTENIMIENTO: 'bg-label-warning',
    EN_DESPLAZAMIENTO: 'bg-label-info',
    PENDIENTE_BAJA: 'bg-label-secondary',
    DADO_DE_BAJA: 'bg-label-danger'
  };

  // Clase de color que pinta la fila completa según la situación del activo
  const situacionRowClass = {
    EN_USO: 'table-primary',
    EN_ALMACEN: 'table-success',
    EN_MANTENIMIENTO: 'table-warning',
    EN_DESPLAZAMIENTO: 'table-info',
    PENDIENTE_BAJA: 'table-secondary',
    DADO_DE_BAJA: 'table-danger'
  };

  window.tablaActivos = $('#miTablaActivos').DataTable({
    ...dtDefaults,
    data: window.activos,
    order: [[1, 'asc']],
    // createdRow: function (row, data) {
    //   const cls = situacionRowClass[data.situacion_nombre];
    //   if (cls) row.classList.add(cls);
    // },
    columnDefs: [
      { targets: 0, orderable: false, searchable: false, className: 'text-center', width: '40px' },
      { targets: -1, orderable: false, searchable: false, className: 'text-center' }
    ],
    columns: [
      {
        data: null,
        render: row => `<input type="checkbox" class="form-check-input row-check" data-id="${row.id_activo}">`
      },
      {
        data: 'codigo_interno',
        render: (d, t, row) =>
          `<span class="fw-semibold d-block">${d}</span>` +
          `<small class="text-muted">${row.codigo_patrimonial}</small>`
      },
      {
        data: 'modelo_nombre',
        render: (d, t, row) =>
          `<span class="fw-semibold d-block">${d}</span>` +
          `<span class="badge bg-label-secondary me-1" style="margin-bottom:0.25rem">${row.marca_nombre}</span>` +
          (row.categoria_nombre !== '—' ? `<span class="badge bg-label-primary">${row.categoria_nombre}</span>` : '')
      },
      {
        data: 'sede_nombre',
        render: d => (d && d !== '—' ? d : '<span class="text-muted">—</span>')
      },
      {
        data: 'ubicacion_nombre',
        render: (d, t, row) => {
          if (!row.id_ubicacion) return '<span class="text-muted">—</span>';
          return (
            `<button type="button" class="btn btn-sm btn-label-primary btn-ver-ubicacion" ` +
            `data-id="${row.id_activo}" data-bs-toggle="tooltip" title="Ver ubicación física completa">` +
            `<i class="bx bx-search me-1"></i>Ver</button>`
          );
        }
      },
      {
        data: 'responsable_nombre',
        render: d =>
          d ? `<span class="fw-semibold">${d}</span>` : '<span class="text-muted fst-italic">Sin asignar</span>'
      },
      {
        data: 'condicion_nombre',
        render: d => `<span class="badge ${condicionBadge[d] ?? 'bg-secondary'}">${d}</span>`
      },
      {
        data: 'situacion_nombre',
        render: d => {
          if (!d || d === '—') return '<span class="text-muted">—</span>';
          const cls = situacionBadge[d] ?? 'bg-label-secondary';
          return `<span class="badge ${cls} fw-bold">${d.replace(/_/g, ' ')}</span>`;
        }
      },
      {
        data: null,
        render: function (row) {
          return `
            <div class="dropdown">
              <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                      data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item btn-mas-info d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_activo}">
                    <i class="bx bx-info-circle me-1"></i> <span style="margin-top:3px">Más Info</span> 
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item d-flex align-items-center" href="${window.routes.edit.replace('{id}', row.id_activo)}">
                    <i class="bx bx-edit-alt me-1"></i> <span style="margin-top:3px">Editar</span> 
                  </a>
                </li>
                <li>
                  <a class="dropdown-item btn-mover-activo d-flex align-items-center" href="javascript:void(0)" data-id="${row.id_activo}">
                    <i class="bx bx-transfer me-1"></i> <span style="margin-top:3px">Mover</span> 
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-danger btn-eliminar-activo d-flex align-items-center" href="javascript:void(0)"
                     data-id="${row.id_activo}" data-codigo="${row.codigo_interno}">
                    <i class="bx bx-trash me-2"></i> <span style="margin-top:3px">Eliminar</span> 
                  </a>
                </li>
              </ul>
            </div>`;
        }
      }
    ]
  });

  // El menú de acciones se posiciona con estrategia "fixed" (Popper) para que se
  // despliegue por fuera de la tabla y no genere scroll ni descuadre las filas.
  function initRowDropdowns() {
    document.querySelectorAll('#miTablaActivos [data-bs-toggle="dropdown"]').forEach(el => {
      bootstrap.Dropdown.getOrCreateInstance(el, {
        popperConfig(defaultConfig) {
          return { ...defaultConfig, strategy: 'fixed' };
        }
      });
    });
  }

  window.tablaActivos.on('draw.dt', function () {
    restoreCheckboxes();
    initTooltips();
    initRowDropdowns();
  });
  initTooltips();
  initRowDropdowns();

  // ── Select All ───────────────────────────────────────────────────────────────
  $(document).on('change', '#check-all', function () {
    const checked = $(this).prop('checked');
    window.tablaActivos.rows({ search: 'applied' }).every(function () {
      const row = this.data();
      $(this.node()).find('.row-check').prop('checked', checked);
      checked ? window.activosSeleccionados.add(row.id_activo) : window.activosSeleccionados.delete(row.id_activo);
    });
    actualizarBulkBar();
  });

  // ── Individual checkbox ──────────────────────────────────────────────────────
  $(document).on('change', '.row-check', function () {
    const id = parseInt($(this).data('id'));
    $(this).prop('checked') ? window.activosSeleccionados.add(id) : window.activosSeleccionados.delete(id);
    actualizarBulkBar();
    syncHeaderCheckbox();
  });

  // ── Deseleccionar todo ───────────────────────────────────────────────────────
  $(document).on('click', '#btn-deselect-all', function () {
    window.activosSeleccionados.clear();
    $('.row-check, #check-all').prop('checked', false);
    actualizarBulkBar();
  });

  function restoreCheckboxes() {
    window.tablaActivos.rows().every(function () {
      const row = this.data();
      $(this.node()).find('.row-check').prop('checked', window.activosSeleccionados.has(row.id_activo));
    });
    syncHeaderCheckbox();
  }

  function syncHeaderCheckbox() {
    const total = window.tablaActivos.rows({ search: 'applied' }).count();
    const checked = window.tablaActivos
      .rows({ search: 'applied' })
      .nodes()
      .toArray()
      .filter(n => $(n).find('.row-check').prop('checked')).length;
    $('#check-all').prop('checked', total > 0 && checked === total);
  }

  function actualizarBulkBar() {
    const n = window.activosSeleccionados.size;
    if (n > 0) {
      $('#bulk-count').text(`${n} activo${n !== 1 ? 's' : ''} seleccionado${n !== 1 ? 's' : ''}`);
      $('#bulk-bar').removeClass('d-none');
    } else {
      $('#bulk-bar').addClass('d-none');
    }
  }

  window.actualizarBulkBar = actualizarBulkBar;

  // ── Helpers para actualizar filas ────────────────────────────────────────────
  window.actualizarFilaActivo = function (updatedData) {
    window.tablaActivos.rows().every(function () {
      if (this.data().id_activo === updatedData.id_activo) {
        this.data(updatedData).draw(false);
      }
    });
  };

  window.eliminarFilaActivo = function (idActivo) {
    window.tablaActivos
      .rows(function (idx, data) {
        return data.id_activo === idActivo;
      })
      .remove()
      .draw(false);
  };
});
