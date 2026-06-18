$(function () {
  // Select2 — Modal Nuevo
  $('#modelo_id_marca').select2({
    dropdownParent: $('#formNuevoModelo'),
    width: '100%'
  });

  $('#edit-modelo-id-marca').select2({
    dropdownParent: $('#formEditarModelo'),
    width: '100%'
  });

  $('#modelo_id_categoria').select2({
    dropdownParent: $('#formNuevoModelo'),
    width: '100%'
  });

  $('#edit-modelo-id-categoria').select2({
    dropdownParent: $('#formEditarModelo'),
    width: '100%'
  });
});
