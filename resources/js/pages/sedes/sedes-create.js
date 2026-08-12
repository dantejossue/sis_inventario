import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const form = $('#formNuevaSede');
  const modal = $('#modalNuevaSede');
  const btn = $('#btnGuardarSede');
  const spinner = btn.find('.spinner-border');

  modal.on('show.bs.modal', () => {
    limpiar();
    form.trigger('reset');
  });

  form.on('input', 'input', function () {
    $(this).removeClass('is-invalid');
    $(this).closest('.form-floating').find('.invalid-feedback').text('');
  });

  form.on('submit', function (e) {
    e.preventDefault();
    limpiar();
    btn.prop('disabled', true);
    spinner.removeClass('d-none');

    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize(),

      success(res) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        modal.modal('hide');
        if (res.data) window.tablaSedes.row.add(res.data).draw();
        Swal.fire({
          icon: 'success',
          title: 'Sede registrada',
          text: res.message,
          timer: 2000,
          showConfirmButton: false
        });
      },
      // success(res) {
      //   btn.prop('disabled', false);
      //   spinner.addClass('d-none');
      //   modal.modal('hide');

      //   if (res.data) {
      //     window.tablaSedes.row.add(res.data).draw();
      //   }

      //   Swal.fire({
      //     toast: true,
      //     position: 'top-end',
      //     icon: 'success',
      //     title: 'Sede registrada',
      //     text: res.message,

      //     background: '#2b2c40',
      //     color: '#fff',

      //     showConfirmButton: false,
      //     timer: 3000,
      //     timerProgressBar: true,

      //     didOpen: toast => {
      //       toast.onmouseenter = Swal.stopTimer;
      //       toast.onmouseleave = Swal.resumeTimer;
      //     }
      //   });
      // },

      error(xhr) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        if (xhr.status === 422) {
          const errors = xhr.responseJSON.errors;
          Object.keys(errors).forEach(c => {
            const inp = form.find(`[name="${c}"]`);
            inp.addClass('is-invalid');
            inp.closest('.form-floating').find('.invalid-feedback').text(errors[c][0]);
          });
          return;
        }
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo registrar la sede.' });
      }
    });
  });

  function limpiar() {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
  }
});
