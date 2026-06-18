import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const modal   = $('#modalDependencias');
  const loading = $('#dep-loading');
  const body    = $('#dep-body');
  const btn     = $('#btnGuardarDeps');
  const spinner = btn.find('.spinner-border');

  let sedeActualId = null;

  $('#miTablaSedes').on('click', '.btn-dependencias', function () {
    const b = $(this);
    sedeActualId = b.data('id');
    $('#dep-sede-nombre').text(b.data('nombre'));

    loading.removeClass('d-none');
    body.addClass('d-none');
    modal.modal('show');

    $.ajax({
      url:  window.routes.dependencias.replace('{id}', sedeActualId),
      type: 'GET',

      success(res) {
        $('.dep-check').prop('checked', false);
        res.asignadas.forEach(id => $(`#dep_${id}`).prop('checked', true));
        loading.addClass('d-none');
        body.removeClass('d-none');
      },

      error() {
        modal.modal('hide');
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las dependencias.' });
      }
    });
  });

  modal.on('hidden.bs.modal', () => {
    sedeActualId = null;
    loading.removeClass('d-none');
    body.addClass('d-none');
  });

  btn.on('click', function () {
    if (!sedeActualId) return;

    const seleccionados = $('.dep-check:checked').map(function () {
      return $(this).val();
    }).get();

    btn.prop('disabled', true); spinner.removeClass('d-none');

    $.ajax({
      url:  window.routes.dependencias.replace('{id}', sedeActualId),
      type: 'POST',
      data: {
        _token:       $('meta[name="csrf-token"]').attr('content'),
        dependencias: seleccionados,
      },

      success(res) {
        btn.prop('disabled', false); spinner.addClass('d-none');
        modal.modal('hide');

        const row = encontrarFila(sedeActualId);
        if (row) {
          const d = row.data();
          d.dependencias_count = res.dependencias_count;
          row.data(d).draw(false);
        }

        Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2200, timerProgressBar: true })
          .fire({ icon: 'success', title: res.message });
      },

      error() {
        btn.prop('disabled', false); spinner.addClass('d-none');
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron guardar las dependencias.' });
      }
    });
  });

  function encontrarFila(id) {
    let found = null;
    window.tablaSedes.rows().every(function () {
      if (this.data().id_sede == id) found = this;
    });
    return found;
  }
});
