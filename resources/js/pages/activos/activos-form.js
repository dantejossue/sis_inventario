import $ from 'jquery';

// Inicializa Select2 en los selects del formulario de crear/editar activo
// (ambas vistas usan el mismo partial form-fields, con los mismos IDs).
// La ubicación ya NO usa Select2: se elige mediante el árbol modal.
$(function () {
  $('#id_modelo, #condicion_actual, #id_responsable_actual').select2({
    width: '100%',
    placeholder: 'Seleccionar...'
  });

  // Ficha Técnica TI: visible solo si la categoría del modelo elegido la requiere
  // (el <option> trae data-ficha="1|0", calculado en el servidor).
  const $modelo = $('#id_modelo');
  const $ficha = $('#ficha-tecnica-wrap');

  function toggleFichaTecnica() {
    const requiere = $modelo.find('option:selected').data('ficha') == 1;
    $ficha.toggleClass('d-none', !requiere);
  }

  $modelo.on('change', toggleFichaTecnica);
  toggleFichaTecnica(); // estado inicial (modo editar / validación fallida)

  // ── Resumen del activo en vivo ────────────────────────────────────────────
  const setText = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  };

  function actualizarResumen() {
    const opt = $modelo.find('option:selected');
    const marca = opt.data('marca') || '';
    const modelo = opt.data('modelo') || '';
    const categoria = opt.data('categoria') || '—';
    const icono = opt.data('icono') || 'bx-box';

    const codigo = ($('#codigo_patrimonial').val() || $('#codigo_interno').val() || '').trim().toUpperCase() || '—';
    const condSel = document.querySelector('#condicion_actual option:checked');
    const condicion = condSel && condSel.value ? condSel.textContent.trim() : '—';
    const respSel = document.querySelector('#id_responsable_actual option:checked');
    const responsable = respSel && respSel.value ? respSel.textContent.trim() : 'Sin responsable';
    const situacion = respSel && respSel.value ? 'En uso' : 'Disponible';
    const nombre = (marca || modelo) ? `${marca} ${modelo}`.trim() : 'Nuevo activo';

    setText('previewNombreActivo', nombre);
    setText('previewCodigoActivo', codigo);
    setText('previewCategoria', categoria);
    setText('previewCondicion', condicion);
    setText('previewSituacion', situacion);
    setText('resCodigoActivo', codigo);
    setText('resMarcaActivo', marca || '—');
    setText('resModeloActivo', modelo || '—');
    setText('resResponsableActivo', responsable);

    const ico = document.getElementById('previewIconoActivo');
    if (ico) ico.className = 'bx ' + icono + ' fs-4';
  }

  $('#id_modelo, #condicion_actual, #id_responsable_actual').on('change', actualizarResumen);
  $('#codigo_interno, #codigo_patrimonial').on('input', actualizarResumen);
  actualizarResumen();

  // ── Motivo del cambio de condición (solo edición) ─────────────────────────
  // Se muestra y exige únicamente cuando la condición elegida difiere de la
  // original del activo. El servidor lo valida igual (fuente de verdad).
  const $condicion = $('#condicion_actual');
  const $motivoWrap = $('#motivo-condicion-wrap');
  const $motivo = $('#motivo_condicion');
  if ($motivoWrap.length && $motivo.length) {
    const condicionOriginal = String($motivo.data('condicion-original') ?? '');
    const toggleMotivo = () => {
      const cambia = String($condicion.val() ?? '') !== condicionOriginal;
      $motivoWrap.toggleClass('d-none', !cambia);
      $motivo.prop('required', cambia);
      if (!cambia) $motivo.val('');
    };
    $condicion.on('change', toggleMotivo);
    toggleMotivo(); // estado inicial (y tras validación fallida)
  }

  // ── Documentos del activo: arrastrar + acumular + quitar ───────────────────
  // El <input type="file"> nativo REEMPLAZA su contenido en cada selección y no
  // sabe recibir archivos arrastrados. Para poder (1) arrastrar a la zona y
  // (2) ir sumando archivos en varias elecciones, usamos un DataTransfer propio
  // ("bolsa") como fuente de verdad y lo reasignamos a input.files en cada
  // cambio, que es lo que finalmente viaja en el POST del formulario.
  const inputDocs = document.getElementById('documentos_activo');
  const listaDocs = document.getElementById('listaDocumentos');
  const zonaDocs = document.getElementById('zonaDocumentos');

  if (inputDocs && listaDocs) {
    const bolsa = new DataTransfer();

    // Deben coincidir con EXT_DOCUMENTOS y el límite del servidor (ActivoController).
    const EXT_OK = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
    const MAX_KB = 5 * 1024; // 5 MB por archivo.

    const claveArchivo = f => `${f.name}|${f.size}|${f.lastModified}`;
    const formatoTamano = kb => (kb >= 1024 ? `${(kb / 1024).toFixed(1)} MB` : `${Math.round(kb)} KB`);

    // Vuelca la bolsa al input real (setear .files NO dispara 'change').
    function sincronizarInput() {
      const dt = new DataTransfer();
      Array.from(bolsa.files).forEach(f => dt.items.add(f));
      inputDocs.files = dt.files;
    }

    function render() {
      const files = Array.from(bolsa.files);
      listaDocs.innerHTML = files
        .map(
          (f, i) =>
            `<div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
               <span class="text-truncate"><i class="bx bx-file me-1"></i>${f.name}</span>
               <span class="d-flex align-items-center ms-2 flex-shrink-0">
                 <small class="text-muted me-2">${formatoTamano(f.size / 1024)}</small>
                 <button type="button" class="btn btn-sm btn-icon btn-text-danger p-0 btn-quitar-doc"
                         data-index="${i}" title="Quitar">
                   <i class="bx bx-x"></i>
                 </button>
               </span>
             </div>`
        )
        .join('');
    }

    function agregar(fileList) {
      const rechazados = [];
      Array.from(fileList || []).forEach(f => {
        const ext = (f.name.split('.').pop() || '').toLowerCase();
        if (!EXT_OK.includes(ext)) {
          rechazados.push(`${f.name} — formato no permitido`);
          return;
        }
        if (f.size / 1024 > MAX_KB) {
          rechazados.push(`${f.name} — supera 5 MB`);
          return;
        }
        const duplicado = Array.from(bolsa.files).some(x => claveArchivo(x) === claveArchivo(f));
        if (!duplicado) bolsa.items.add(f);
      });
      sincronizarInput();
      render();
      if (rechazados.length) {
        window.alert('No se adjuntaron estos archivos:\n\n' + rechazados.join('\n'));
      }
    }

    // Selección por diálogo: el input trae SOLO lo nuevo → lo sumamos a la bolsa.
    inputDocs.addEventListener('change', function () {
      agregar(this.files);
    });

    // Quitar un archivo puntual de la lista antes de guardar.
    listaDocs.addEventListener('click', function (e) {
      const btn = e.target.closest('.btn-quitar-doc');
      if (!btn) return;
      bolsa.items.remove(parseInt(btn.dataset.index, 10));
      sincronizarInput();
      render();
    });

    // Arrastrar y soltar sobre la zona (la etiqueta que abre el diálogo).
    if (zonaDocs) {
      const resaltar = on => {
        zonaDocs.style.borderColor = on ? 'var(--bs-primary, #696cff)' : '';
        zonaDocs.style.opacity = on ? '0.85' : '';
      };
      ['dragenter', 'dragover'].forEach(ev =>
        zonaDocs.addEventListener(ev, e => {
          e.preventDefault();
          e.stopPropagation();
          resaltar(true);
        })
      );
      ['dragleave', 'dragend', 'drop'].forEach(ev =>
        zonaDocs.addEventListener(ev, e => {
          e.preventDefault();
          e.stopPropagation();
          resaltar(false);
        })
      );
      zonaDocs.addEventListener('drop', e => {
        const soltados = e.dataTransfer?.files;
        if (soltados && soltados.length) agregar(soltados);
      });
    }
  }
});

// ───────────────────────────────────────────────────────────────────────────
// Árbol de ubicaciones físicas (Sede › Pabellón › Piso › Ambiente).
// Solo los nodos hoja (sin hijos activos) son seleccionables; los intermedios
// solo sirven para navegar. Al elegir una hoja se guarda su id en el hidden
// #id_ubicacion_actual y se muestra la ruta completa en #ubicacionDisplay.
// ───────────────────────────────────────────────────────────────────────────
(function initUbicacionTree() {
  const dataEl = document.getElementById('ubicacionesData');
  const treeEl = document.getElementById('ubicacionTree');
  const hidden = document.getElementById('id_ubicacion_actual');
  const display = document.getElementById('ubicacionDisplay');
  if (!dataEl || !treeEl || !hidden || !display) return;

  let registros = [];
  try {
    registros = JSON.parse(dataEl.textContent) || [];
  } catch (e) {
    registros = [];
  }

  const vacioEl = document.getElementById('ubicacionTreeVacio');
  const buscarEl = document.getElementById('ubicacionBuscar');
  const btnLimpiar = document.getElementById('btnLimpiarUbicacion');

  // 1. Indexar nodos y enlazar hijos. Las "sedes" son raíces sintéticas que
  //    agrupan los nodos cuyo padre no existe en el conjunto activo.
  const porId = new Map();
  registros.forEach(r => porId.set(r.id, { ...r, hijos: [] }));

  const sedes = new Map(); // sede_id -> { sedeId, nombre, hijos: [] }
  porId.forEach(nodo => {
    const padre = nodo.padre != null ? porId.get(nodo.padre) : null;
    if (padre) {
      padre.hijos.push(nodo);
    } else {
      if (!sedes.has(nodo.sede_id)) {
        sedes.set(nodo.sede_id, { sedeId: nodo.sede_id, nombre: nodo.sede, hijos: [] });
      }
      sedes.get(nodo.sede_id).hijos.push(nodo);
    }
  });

  const esHoja = nodo => nodo.hijos.length === 0;

  // Ruta jerárquica completa de un nodo: "Sede › Pabellón › Piso › Ambiente".
  function rutaDe(id) {
    const partes = [];
    let cursor = porId.get(id);
    let guard = 0;
    while (cursor && guard++ < 30) {
      partes.unshift(cursor.nombre);
      cursor = cursor.padre != null ? porId.get(cursor.padre) : null;
    }
    const nodo = porId.get(id);
    if (nodo) partes.unshift(nodo.sede);
    return partes.join(' › ');
  }

  // 2. Render recursivo.
  function ordenar(arr) {
    return [...arr].sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));
  }

  function nodoLi(nodo) {
    const li = document.createElement('li');
    const hoja = esHoja(nodo);
    li.className = hoja ? 'ubic-leaf' : 'ubic-branch';
    li.dataset.id = nodo.id;
    li.dataset.nombre = nodo.nombre.toLowerCase();

    const row = document.createElement('div');
    row.className = 'ubic-node';

    const toggle = document.createElement('span');
    toggle.className = 'ubic-toggle' + (hoja ? ' is-leaf' : '');
    toggle.innerHTML = hoja ? '' : '<i class="bx bx-chevron-right"></i>';
    row.appendChild(toggle);

    const label = document.createElement('span');
    label.className = 'ubic-label';
    const codigo = nodo.codigo ? ` — ${nodo.codigo}` : '';
    label.innerHTML = `${nodo.nombre}<span class="ubic-tipo">${nodo.tipo}${codigo}</span>`;
    row.appendChild(label);

    li.appendChild(row);

    if (!hoja) {
      const ul = document.createElement('ul');
      ul.className = 'ubic-children collapsed';
      ordenar(nodo.hijos).forEach(h => ul.appendChild(nodoLi(h)));
      li.appendChild(ul);

      const toggleFn = () => {
        ul.classList.toggle('collapsed');
        const icon = toggle.querySelector('i');
        if (icon) icon.className = ul.classList.contains('collapsed') ? 'bx bx-chevron-right' : 'bx bx-chevron-down';
      };
      toggle.addEventListener('click', toggleFn);
      label.addEventListener('click', toggleFn);
    } else {
      // Hoja: seleccionable.
      row.addEventListener('click', () => seleccionar(nodo.id));
    }

    return li;
  }

  function render() {
    treeEl.innerHTML = '';
    if (sedes.size === 0) {
      vacioEl?.classList.remove('d-none');
      return;
    }
    const rootUl = document.createElement('ul');
    [...sedes.values()]
      .sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'))
      .forEach(sede => {
        const li = document.createElement('li');
        li.className = 'ubic-group';
        li.dataset.nombre = sede.nombre.toLowerCase();

        const row = document.createElement('div');
        row.className = 'ubic-node';
        const toggle = document.createElement('span');
        toggle.className = 'ubic-toggle';
        toggle.innerHTML = '<i class="bx bx-chevron-down"></i>';
        row.appendChild(toggle);
        const label = document.createElement('span');
        label.className = 'ubic-label';
        label.innerHTML = `<i class="bx bx-building-house me-1"></i>${sede.nombre}`;
        row.appendChild(label);
        li.appendChild(row);

        const ul = document.createElement('ul');
        ul.className = 'ubic-children';
        ordenar(sede.hijos).forEach(h => ul.appendChild(nodoLi(h)));
        li.appendChild(ul);

        const toggleFn = () => {
          ul.classList.toggle('collapsed');
          const icon = toggle.querySelector('i');
          if (icon) icon.className = ul.classList.contains('collapsed') ? 'bx bx-chevron-right' : 'bx bx-chevron-down';
        };
        toggle.addEventListener('click', toggleFn);
        label.addEventListener('click', toggleFn);

        rootUl.appendChild(li);
      });
    treeEl.appendChild(rootUl);
  }

  // 3. Selección.
  function marcarSeleccion(id) {
    treeEl.querySelectorAll('.ubic-leaf.is-selected').forEach(el => el.classList.remove('is-selected'));
    if (id) {
      const li = treeEl.querySelector(`.ubic-leaf[data-id="${id}"]`);
      if (li) li.classList.add('is-selected');
    }
  }

  function expandirHasta(id) {
    let cursor = porId.get(id);
    while (cursor) {
      const li = treeEl.querySelector(`li[data-id="${cursor.id}"]`);
      const ul = li?.parentElement;
      if (ul && ul.classList.contains('ubic-children')) {
        ul.classList.remove('collapsed');
        const icon = ul.previousElementSibling?.querySelector('.ubic-toggle i');
        if (icon) icon.className = 'bx bx-chevron-down';
      }
      cursor = cursor.padre != null ? porId.get(cursor.padre) : null;
    }
  }

  function seleccionar(id) {
    hidden.value = id;
    display.value = rutaDe(id);
    marcarSeleccion(id);
    // Cierra el modal usando la API de Bootstrap (expuesta en window.bootstrap).
    const modalEl = document.getElementById('modalUbicacionTree');
    const Modal = window.bootstrap?.Modal;
    if (modalEl && Modal) {
      (Modal.getInstance(modalEl) || new Modal(modalEl)).hide();
    }
  }

  function limpiar() {
    hidden.value = '';
    display.value = '';
    marcarSeleccion(null);
  }

  // 4. Buscador: muestra un nodo si él o algún descendiente coincide.
  function filtrar(termino) {
    const q = termino.trim().toLowerCase();
    const items = treeEl.querySelectorAll('li');
    if (!q) {
      items.forEach(li => li.classList.remove('d-none'));
      // Al limpiar la búsqueda: colapsa ramas internas, deja las sedes abiertas.
      treeEl.querySelectorAll('.ubic-branch > .ubic-children').forEach(ul => ul.classList.add('collapsed'));
      treeEl.querySelectorAll('.ubic-group > .ubic-children').forEach(ul => ul.classList.remove('collapsed'));
      sincronizarIconos();
      return;
    }
    items.forEach(li => {
      const coincide = (li.dataset.nombre || '').includes(q);
      const tieneHijoVisible =
        !!li.querySelector('li') &&
        [...li.querySelectorAll('li')].some(c => (c.dataset.nombre || '').includes(q));
      const visible = coincide || tieneHijoVisible;
      li.classList.toggle('d-none', !visible);
      const ul = li.querySelector(':scope > .ubic-children');
      if (ul && visible) ul.classList.remove('collapsed');
    });
    sincronizarIconos();
  }

  function sincronizarIconos() {
    treeEl.querySelectorAll('.ubic-children').forEach(ul => {
      const toggle = ul.previousElementSibling?.querySelector('.ubic-toggle');
      const icon = toggle?.querySelector('i');
      if (icon && !toggle.classList.contains('is-leaf')) {
        icon.className = ul.classList.contains('collapsed') ? 'bx bx-chevron-right' : 'bx bx-chevron-down';
      }
    });
  }

  // 5. Arranque.
  render();

  // Estado inicial: si ya hay un id (modo editar / validación fallida), mostrar
  // su ruta y dejar el árbol listo para expandirlo al abrir el modal.
  const idInicial = hidden.value ? parseInt(hidden.value, 10) : null;
  if (idInicial && porId.has(idInicial)) {
    display.value = rutaDe(idInicial);
  } else if (idInicial) {
    // El id apunta a una ubicación inactiva (no está en el árbol activo).
    display.value = 'Ubicación actual (inactiva)';
  }

  document.getElementById('modalUbicacionTree')?.addEventListener('shown.bs.modal', () => {
    const id = hidden.value ? parseInt(hidden.value, 10) : null;
    if (id && porId.has(id)) {
      expandirHasta(id);
      marcarSeleccion(id);
      treeEl.querySelector(`.ubic-leaf[data-id="${id}"]`)?.scrollIntoView({ block: 'center' });
    }
    buscarEl?.focus();
  });

  buscarEl?.addEventListener('input', e => filtrar(e.target.value));
  btnLimpiar?.addEventListener('click', limpiar);
})();
