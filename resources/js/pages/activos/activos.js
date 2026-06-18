$(function () {
  // Select2 — Modal Nuevo
  $('#id_modelo').select2({
    width: '100%'
  });

  $('#selectRol').select2({
    dropdownParent: $('#modalNuevoUsuario'),
    width: '100%'
  });

  // Select2 — Modal Editar
  $('#editSelectRol').select2({
    dropdownParent: $('#modalEditarUsuario'),
    width: '100%'
  });
});
