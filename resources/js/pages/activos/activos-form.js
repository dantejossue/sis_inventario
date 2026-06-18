import $ from 'jquery';

// Inicializa Select2 en los selects del formulario de crear/editar activo
// (ambas vistas usan el mismo partial form-fields, con los mismos IDs).
$(function () {
  $('#id_modelo, #id_condicion_actual, #id_responsable_actual, #id_ubicacion_actual')
    .select2({
      width: '100%',
      placeholder: 'Seleccionar...'
    });
});
