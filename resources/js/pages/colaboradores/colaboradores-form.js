// Lógica del formulario de crear/editar colaborador

document.addEventListener('DOMContentLoaded', function () {

  // ── Cascada Sede → Dependencia ────────────────────────────────────────
  const selectSede  = document.getElementById('selectSede');
  const selectDep   = document.getElementById('selectDependencia');
  const sdData      = window.sedeDependencias ?? [];   // [{id_sede_dependencia, id_sede, dependencia:{...}, ...}]
  const sdActual    = window.sedeDependenciaActual;    // null en crear, id en editar

  function cargarDependencias(idSede, sdSeleccionado = null) {
    selectDep.innerHTML = '<option value="">— Seleccione una dependencia —</option>';

    if (!idSede) return;

    const filtradas = sdData.filter(sd => String(sd.id_sede) === String(idSede));

    filtradas.forEach(sd => {
      const opt = document.createElement('option');
      opt.value       = sd.id_sede_dependencia;
      opt.textContent = sd.dependencia?.nombre_dependencia ?? `Dependencia ${sd.id_dependencia}`;
      if (sdSeleccionado && String(sd.id_sede_dependencia) === String(sdSeleccionado)) {
        opt.selected = true;
      }
      selectDep.appendChild(opt);
    });

    if (filtradas.length === 0) {
      selectDep.innerHTML = '<option value="">Sin dependencias para esta sede</option>';
    }
  }

  selectSede.addEventListener('change', function () {
    cargarDependencias(this.value);
  });

  // En edición: pre-cargar dependencias según la sede guardada
  if (sdActual) {
    const sdRegistro = sdData.find(sd => String(sd.id_sede_dependencia) === String(sdActual));
    if (sdRegistro) {
      selectSede.value = sdRegistro.id_sede;
      cargarDependencias(sdRegistro.id_sede, sdActual);
    }
  }

  // ── Preview de foto ───────────────────────────────────────────────────
  const inputFoto   = document.getElementById('inputFoto');
  const fotoPreview = document.getElementById('fotoPreview');

  if (inputFoto && fotoPreview) {
    inputFoto.addEventListener('change', function () {
      const file = this.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => { fotoPreview.src = e.target.result; };
      reader.readAsDataURL(file);
    });
  }

  // ── Auto-uppercase en campos de texto ────────────────────────────────
  document.querySelectorAll('input.text-uppercase').forEach(input => {
    input.addEventListener('input', function () {
      const pos = this.selectionStart;
      this.value = this.value.toUpperCase();
      this.setSelectionRange(pos, pos);
    });
  });

});
