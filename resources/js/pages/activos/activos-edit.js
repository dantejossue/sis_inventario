import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const form = $('#formEditarActivo');
  const modal = $('#modalEditarActivo');
  const btn = $('#btnActualizarActivo');
  const spinner = btn.find('.spinner-border');

  // ── Abrir modal con datos del activo ─────────────────────────────────────────
  $(document).on('click', '.btn-editar-activo', function () {
    const id = parseInt($(this).data('id'));
    const activo = window.activos.find(a => a.id_activo === id);
    if (!activo) return;

    limpiarErrores();
    modal.find('[data-bs-target="#editar-basicos"]').tab('show');

    form.attr('action', window.routes.update.replace('{id}', id));

    // Básicos
    $('#edit-codigo-interno').val(activo.codigo_interno);
    $('#edit-codigo-patrimonial').val(activo.codigo_patrimonial);
    $('#edit-id-modelo').val(activo.id_modelo);
    $('#edit-id-condicion').val(activo.id_condicion_actual);
    $('#edit-id-situacion').val(activo.id_situacion_actual);

    // Detalles
    $('#edit-numero-serie').val(activo.numero_serie ?? '');
    $('#edit-id-responsable').val(activo.id_responsable_actual ?? '');
    $('#edit-descripcion').val(activo.descripcion ?? '');
    $('#edit-fecha-adquisicion').val(activo.fecha_adquisicion ?? '');
    $('#edit-valor-compra').val(activo.valor_compra ?? '');
    $('#edit-proveedor').val(activo.proveedor ?? '');
    $('#edit-garantia-inicio').val(activo.garantia_inicio ?? '');
    $('#edit-garantia-fin').val(activo.garantia_fin ?? '');
    $('#edit-observaciones').val(activo.observaciones ?? '');

    modal.modal('show');
  });

  form.on('input change', 'input, select, textarea', function () {
    $(this).removeClass('is-invalid');
    $(this).closest('.form-floating').find('.invalid-feedback').text('');
  });

  form.on('submit', function (e) {
    e.preventDefault();
    limpiarErrores();

    btn.prop('disabled', true);
    spinner.removeClass('d-none');

    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize(),

      success: function (res) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        modal.modal('hide');

        if (res.data) {
          // Actualizar array local
          const idx = window.activos.findIndex(a => a.id_activo === res.data.id_activo);
          if (idx !== -1) window.activos[idx] = res.data;
          else window.activos.push(res.data);

          window.actualizarFilaActivo(res.data);
        }

        Swal.fire({
          icon: 'success',
          title: 'Activo actualizado',
          text: res.message,
          timer: 2200,
          showConfirmButton: false
        });
      },

      error: function (xhr) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');

        if (xhr.status === 422) {
          const errors = xhr.responseJSON.errors;
          Object.keys(errors).forEach(campo => {
            const input = form.find(`[name="${campo}"]`);
            input.addClass('is-invalid');
            input.closest('.form-floating').find('.invalid-feedback').text(errors[campo][0]);

            const tabPane = input.closest('.tab-pane');
            if (tabPane.length && !tabPane.hasClass('active')) {
              const targetId = '#' + tabPane.attr('id');
              modal.find(`[data-bs-target="${targetId}"]`).tab('show');
            }
          });
          return;
        }

        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el activo.' });
      }
    });
  });

  function limpiarErrores() {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
  }
});
