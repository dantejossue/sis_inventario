import dtDefaults from '../../plugins/datatables-defaults';
import { initTooltips } from '../../plugins/bootstrap-tooltips';
import $ from 'jquery';
import Swal from 'sweetalert2';

// Tipo de movimiento interno OTI (brief §13)
const tipoBadge = {
  PRESTAMO: 'bg-label-warning',
  TRANSFERENCIA: 'bg-label-info',
  REGULARIZACION: 'bg-label-primary'
};

const tipoLabel = {
  PRESTAMO: 'Préstamo',
  TRANSFERENCIA: 'Transferencia',
  REGULARIZACION: 'Regularización'
};

// Estado del movimiento
const estadoBadge = {
  BORRADOR: 'bg-label-secondary',
  EJECUTADO: 'bg-success',
  OBSERVADO: 'bg-label-warning',
  CANCELADO: 'bg-label-dark'
};

// Estado de la devolución (solo préstamos)
const devolucionBadge = {
  NO_APLICA: 'bg-label-secondary',
  PENDIENTE_DEVOLUCION: 'bg-label-warning',
  DEVUELTO: 'bg-label-success',
  DEVUELTO_OBSERVADO: 'bg-label-danger',
  VENCIDO: 'bg-label-danger'
};

const devolucionLabel = {
  NO_APLICA: '—',
  PENDIENTE_DEVOLUCION: 'Pendiente',
  DEVUELTO: 'Devuelto',
  DEVUELTO_OBSERVADO: 'Devuelto (obs.)',
  VENCIDO: 'Vencido'
};

const dash = '<span class="text-muted">—</span>';

$(function () {
  window.tablaMovimientos = $('#miTablaMovimientos').DataTable({
    ...dtDefaults,
    serverSide: false,
    processing: false,
    ajax: null,
    data: window.movimientos,
    order: [],
    columns: [
      {
        data: 'codigo',
        render: d => `<span class="fw-semibold">${d}</span>`
      },
      {
        data: 'tipo',
        render: t => `<span class="badge ${tipoBadge[t] ?? 'bg-label-secondary'}">${tipoLabel[t] ?? t}</span>`
      },
      {
        data: null,
        orderable: false,
        render: row => row.colaborador_origen || row.ubicacion_origen || dash
      },
      {
        data: null,
        orderable: false,
        render: row => row.colaborador_destino || row.ubicacion_destino || dash
      },
      {
        data: 'estado',
        render: e =>
          `<span class="badge ${estadoBadge[e] ?? 'bg-label-secondary'} fw-bold">${(e ?? '').replace(/_/g, ' ')}</span>`
      },
      {
        // Devolución (reemplaza la antigua columna SIGA, fuera de alcance)
        data: 'estado_devolucion',
        render: (e, t, row) => {
          if (!e || e === 'NO_APLICA') return dash;
          const cls = devolucionBadge[e] ?? 'bg-label-secondary';
          const fechas = row.fecha_devolucion_real
            ? `<small class="text-muted d-block">Devuelto: ${row.fecha_devolucion_real}</small>`
            : row.fecha_devolucion_estimada
              ? `<small class="text-muted d-block">Est.: ${row.fecha_devolucion_estimada}</small>`
              : '';
          return `<span class="badge ${cls} fw-bold">${devolucionLabel[e] ?? e}</span>${fechas}`;
        }
      },
      {
        data: 'registrado_por',
        render: d => d ?? dash
      },
      {
        data: 'fecha',
        render: d => d ?? dash
      },
      {
        data: null,
        orderable: false,
        render: row =>
          row.es_prestamo_pendiente
            ? `<button class="btn btn-sm btn-label-success btn-devolver" data-id="${row.id_movimiento}" data-codigo="${row.codigo}">
                 <i class="bx bx-undo me-1"></i>Devolver
               </button>`
            : dash
      }
    ]
  });

  window.tablaMovimientos.on('draw.dt', initTooltips);
  initTooltips();

  // ── Devolución de préstamo ─────────────────────────────────────────
  $(document).on('click', '.btn-devolver', function () {
    const id = $(this).data('id');
    const codigo = $(this).data('codigo');

    Swal.fire({
      title: `Devolver préstamo ${codigo}`,
      html: `
        <div class="text-start">
          <label class="form-label mt-2">Condición de retorno</label>
          <select id="swal-condicion" class="form-select">
            <option value="BUENO">Bueno</option>
            <option value="NUEVO">Nuevo</option>
            <option value="REGULAR">Regular</option>
            <option value="MALO">Malo</option>
          </select>
          <label class="form-label mt-3">Resultado</label>
          <select id="swal-estado" class="form-select">
            <option value="DEVUELTO">Conforme</option>
            <option value="DEVUELTO_OBSERVADO">Observado</option>
          </select>
          <label class="form-label mt-3">Observación (opcional)</label>
          <textarea id="swal-obs" class="form-control" rows="2"></textarea>
        </div>`,
      showCancelButton: true,
      confirmButtonText: 'Registrar devolución',
      cancelButtonText: 'Cancelar',
      preConfirm: () => ({
        condicion_retorno: document.getElementById('swal-condicion').value,
        estado_devolucion: document.getElementById('swal-estado').value,
        observacion_devolucion: document.getElementById('swal-obs').value
      })
    }).then(result => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: window.routes.devolver.replace('__ID__', id),
        type: 'PUT',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: result.value,
        success: res => {
          Swal.fire({ icon: 'success', title: 'Devolución registrada', text: res.message, timer: 2200, showConfirmButton: false })
            .then(() => window.location.reload());
        },
        error: xhr => {
          const msg = xhr.responseJSON?.message
            || Object.values(xhr.responseJSON?.errors ?? {})[0]?.[0]
            || 'No se pudo registrar la devolución.';
          Swal.fire({ icon: 'error', title: 'Error', text: msg });
        }
      });
    });
  });
});
