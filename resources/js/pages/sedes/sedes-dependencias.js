import $ from 'jquery';
import Swal from 'sweetalert2';

$(function () {
  const modal = $('#modalDependencias');
  const loading = $('#dep-loading');
  const body = $('#dep-body');
  const btn = $('#btnGuardarDeps');
  const spinner = btn.find('.spinner-border');

  const buscar = $('#buscarDependencia');
  const seleccionarTodas = $('#seleccionarTodasDeps');
  const limpiar = $('#limpiarDependencias');
  const contador = $('#contadorDependencias');
  const sinResultados = $('#sinResultadosDependencias');

  let sedeActualId = null;

  // ═══════════════════════════════════════
  // ABRIR MODAL
  // ═══════════════════════════════════════

  $('#miTablaSedes').on('click', '.btn-dependencias', function () {
    const b = $(this);
    sedeActualId = b.data('id');
    $('#dep-sede-nombre').text(b.data('nombre'));

    loading.removeClass('d-none');
    body.addClass('d-none');
    modal.modal('show');

    $.ajax({
      url: window.routes.dependencias.replace('{id}', sedeActualId),
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

  // ═══════════════════════════════════════
  // BUSCAR
  // ═══════════════════════════════════════

  buscar.on('input', function () {
    const texto = $(this).val().toLowerCase().trim();

    let encontrados = 0;

    $('.dep-item').each(function () {
      const item = $(this);

      const nombre = item.data('nombre').toString().toLowerCase();

      const coincide = nombre.includes(texto);

      item.toggleClass('d-none', !coincide);

      if (coincide) {
        encontrados++;
      }
    });

    sinResultados.toggleClass('d-none', encontrados > 0);

    actualizarSeleccionarTodas();
  });

  // ═══════════════════════════════════════
  // SELECCIONAR VISIBLES
  // ═══════════════════════════════════════

  seleccionarTodas.on('change', function () {
    const marcado = $(this).is(':checked');

    $('.dep-item:not(.d-none)').find('.dep-check').prop('checked', marcado);

    actualizarSeleccionadas();
  });

  // ═══════════════════════════════════════
  // CAMBIO INDIVIDUAL
  // ═══════════════════════════════════════

  $(document).on('change', '.dep-check', function () {
    actualizarSeleccionadas();
    actualizarSeleccionarTodas();
  });

  // ═══════════════════════════════════════
  // LIMPIAR
  // ═══════════════════════════════════════

  limpiar.on('click', function () {
    $('.dep-check').prop('checked', false);

    seleccionarTodas.prop('checked', false);

    actualizarSeleccionadas();
  });

  // ═══════════════════════════════════════
  // GUARDAR
  // ═══════════════════════════════════════
  btn.on('click', function () {
    if (!sedeActualId) return;

    const seleccionados = $('.dep-check:checked')
      .map(function () {
        return $(this).val();
      })
      .get();

    btn.prop('disabled', true);
    spinner.removeClass('d-none');

    $.ajax({
      url: window.routes.dependencias.replace('{id}', sedeActualId),
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        dependencias: seleccionados
      },

      success(res) {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        modal.modal('hide');

        const row = encontrarFila(sedeActualId);
        if (row) {
          const d = row.data();
          d.dependencias_count = res.dependencias_count;
          // row.data(d).draw(false);
          row.invalidate().draw(false);
        }

        Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 2200,
          timerProgressBar: true
        }).fire({ icon: 'success', title: res.message });
      },

      error() {
        btn.prop('disabled', false);
        spinner.addClass('d-none');
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron guardar las dependencias.' });
      }
    });
  });

  // ═══════════════════════════════════════
  // CERRAR MODAL
  // ═══════════════════════════════════════
  modal.on('hidden.bs.modal', () => {
    sedeActualId = null;
    buscar.val('');
    seleccionarTodas.prop('checked', false);
    loading.removeClass('d-none');
    body.addClass('d-none');
  });

  function encontrarFila(id) {
    let found = null;
    window.tablaSedes.rows().every(function () {
      if (this.data().id_sede == id) found = this;
    });
    return found;
  }

  // ═══════════════════════════════════════
  // FUNCIONES
  // ═══════════════════════════════════════
  function actualizarSeleccionadas() {
    const cantidad = $('.dep-check:checked').length;

    contador.text(`${cantidad} seleccionada${cantidad !== 1 ? 's' : ''}`);
  }

  function actualizarSeleccionarTodas() {
    const visibles = $('.dep-item:not(.d-none) .dep-check');

    const marcadas = visibles.filter(':checked');

    seleccionarTodas.prop('checked', visibles.length > 0 && visibles.length === marcadas.length);
  }

  function encontrarFila(id) {
    let found = null;

    window.tablaSedes.rows().every(function () {
      if (this.data().id_sede == id) {
        found = this;
      }
    });

    return found;
  }
});
