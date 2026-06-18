$(function () {
  // Select2 — Modal Nuevo
  $('#selectColaborador').select2({
    dropdownParent: $('#modalNuevoUsuario'),
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
