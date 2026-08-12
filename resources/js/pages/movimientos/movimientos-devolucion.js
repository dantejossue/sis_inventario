import $ from 'jquery';
import Swal from 'sweetalert2';

/**
 * Modal de devolución de préstamo. Se usa tanto en la lista como en el detalle
 * del movimiento. Envía por FormData con method spoofing (_method=PUT vía POST)
 * porque PHP no parsea archivos en peticiones PUT multipart.
 */
$(function () {
  const modalEl = document.getElementById('modalDevolucion');
  if (!modalEl) return;

  const modal = () => bootstrap.Modal.getOrCreateInstance(modalEl);
  const form = $('#formDevolucion');
  const btn = $('#btnDevolucion');
  const spinner = btn.find('.spinner-border');

  function limpiarErrores() {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
  }

  // Abrir modal desde cualquier botón .btn-devolver (lista o detalle).
  $(document).on('click', '.btn-devolver', async function () {
    limpiarErrores();
    const id = $(this).data('id');
    const codigo = $(this).data('codigo');

    $('#formDevolucion')[0].reset();
    $('#dev-id').val(id);
    $('#dev-codigo').text(codigo);

    $('#dev-activos').html(`
      <tr>
        <td colspan="3" class="text-center py-3">
          Cargando activos...
        </td>
      </tr>
    `);

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDevolucion'));

    modal.show();

    try {
      const url = window.routes.datosDevolucion.replace('__ID__', id);

      const response = await fetch(url, {
        headers: {
          Accept: 'application/json'
        }
      });

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.message || 'No se pudieron cargar los activos.');
      }

      const filas = result.data
        .map(
          detalle => `
        <tr>
          <td>
            <span class="fw-semibold d-block">
              ${detalle.codigo_patrimonial || detalle.codigo_interno || '—'}
            </span>

            <small class="text-muted">
              ${detalle.codigo_interno || ''}
            </small>
          </td>

          <td>
            ${detalle.condicion_label || '—'}
          </td>

          <td>
            <select
              class="form-select form-select-sm"
              name="detalles[${detalle.id_detalle}][condicion_retorno]"
              required
            >
              <option value="">Seleccionar</option>
              <option value="NUEVO">Nuevo</option>
              <option value="BUENO">Bueno</option>
              <option value="REGULAR">Regular</option>
              <option value="MALO">Malo</option>
            </select>
          </td>
        </tr>
      `
        )
        .join('');

      $('#dev-activos').html(filas);
    } catch (error) {
      $('#dev-activos').html(`
        <tr>
          <td colspan="3" class="text-center text-danger py-3">
            ${error.message}
          </td>
        </tr>
      `);
    }
  });

  form.on('submit', function (e) {
    e.preventDefault();
    limpiarErrores();

    const id = $('#dev-id').val();
    const doc = document.getElementById('dev-documento').files[0];
    if (!doc) {
      $('#dev-documento')
        .addClass('is-invalid')
        .siblings('.invalid-feedback')
        .text('Adjunta el acta de conformidad de retorno.');
      return;
    }

    // btn.prop('disabled', true);
    // spinner.removeClass('d-none');

    // const fd = new FormData();
    // fd.append('_method', 'PUT');
    // fd.append('condicion_retorno', $('#dev-condicion').val());
    // fd.append('estado_devolucion', $('#dev-estado').val());
    // fd.append('tipo_documento', $('#dev-tipodoc').val());
    // const obs = $('#dev-obs').val();
    // if (obs) fd.append('observacion_devolucion', obs);
    // fd.append('documento', doc);
    const condicionesSinSeleccionar = form.find('select[name^="detalles["]').filter(function () {
      return !$(this).val();
    });

    if (condicionesSinSeleccionar.length > 0) {
      condicionesSinSeleccionar.addClass('is-invalid');

      $('#dev-detalles-error').text('Selecciona la condición de retorno de todos los activos.').addClass('d-block');

      return;
    }

    btn.prop('disabled', true);
    spinner.removeClass('d-none');

    // Recoge automáticamente todos los campos del formulario,
    // incluyendo detalles[id][condicion_retorno].
    const fd = new FormData(form[0]);

    // Mantener solo si la ruta Laravel es PUT.
    fd.append('_method', 'PUT');

    $.ajax({
      url: window.routes.devolver.replace('__ID__', id),
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      data: fd,
      processData: false,
      contentType: false,
      success: res => {
        modal().hide();
        Swal.fire({
          icon: 'success',
          title: 'Devolución registrada',
          text: res.message,
          timer: 2000,
          showConfirmButton: false
        }).then(() => window.location.reload());
      },
      error: xhr => {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          Object.keys(xhr.responseJSON.errors).forEach(campo => {
            const map = {
              condicion_retorno: '#dev-condicion',
              estado_devolucion: '#dev-estado',
              documento: '#dev-documento'
            };
            const sel = map[campo];
            if (sel)
              $(sel).addClass('is-invalid').siblings('.invalid-feedback').text(xhr.responseJSON.errors[campo][0]);
          });
          return;
        }
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo registrar la devolución.' });
      }
    });
  });
});
