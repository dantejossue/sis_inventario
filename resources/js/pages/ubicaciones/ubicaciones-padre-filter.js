/**
 * Muestra en el select de "ubicación superior" solo las opciones que pertenecen
 * a la sede seleccionada, ocultando el resto. Opcionalmente excluye un id
 * (la propia ubicación que se está editando, para no colgarla de sí misma).
 *
 * Si la opción actualmente seleccionada deja de ser válida, vuelve a «Raíz».
 */
export function filtrarPadresPorSede(sedeSel, padreSel, excluirId = null) {
  const idSede = String(sedeSel.val() || '');
  const padre = padreSel.get(0);
  if (!padre) return;

  let seleccionInvalida = false;

  Array.from(padre.options).forEach(opt => {
    if (!opt.value) return; // opción «Raíz», siempre visible

    const mismaSede = idSede !== '' && opt.dataset.sede === idSede;
    const esExcluida = excluirId != null && opt.value === String(excluirId);
    const visible = mismaSede && !esExcluida;

    opt.hidden = !visible;
    opt.disabled = !visible;

    if (!visible && opt.selected) seleccionInvalida = true;
  });

  if (seleccionInvalida) padreSel.val('');
}

/**
 * Inserta (o actualiza si ya existe) la opción de una ubicación en los selects de
 * "ubicación superior", para que una ubicación recién creada/editada quede
 * disponible como padre sin necesidad de recargar la página.
 */
export function upsertOpcionPadre(ubic) {
  if (!ubic || ubic.estado !== 'ACTIVO') return;

  ['#id_ubicacion_padre', '#edit-id_ubicacion_padre'].forEach(sel => {
    const select = document.querySelector(sel);
    if (!select) return;

    let opt = select.querySelector(`option[value="${ubic.id_ubicacion}"]`);
    if (!opt) {
      opt = document.createElement('option');
      opt.value = ubic.id_ubicacion;
      select.appendChild(opt);
    }
    opt.dataset.sede = ubic.id_sede;
    opt.textContent = ubic.ruta;
  });
}
