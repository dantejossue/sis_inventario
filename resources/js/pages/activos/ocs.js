import $ from 'jquery';

const vacio = '<span class="text-muted">—</span>';

const valor = dato => {
  if (dato === null || dato === undefined || dato === '') {
    return vacio;
  }

  return dato;
};

const mbAGb = cantidad => {
  if (cantidad === null || cantidad === undefined || cantidad === '') {
    return '—';
  }

  return `${(Number(cantidad) / 1024).toFixed(2)} GB`;
};

$(function () {
  const pagina = $('#pagina-ocs');
  const url = pagina.data('url');

  $.ajax({
    url,
    type: 'GET',
    dataType: 'json',

    success: response => {
      const datos = response.data ?? {};

      renderResumen(datos);
      renderBios(datos.bios);
      renderProcesador(datos.cpu);
      renderMemorias(datos.memorias ?? []);
      renderAlmacenamiento(datos.almacenamientos ?? []);
      renderDiscos(datos.discos ?? []);
      renderVideo(datos.tarjeta_video ?? []);
      renderRedes(datos.redes ?? []);
      renderMonitores(datos.monitores ?? []);
      renderEntradas(datos.entradas ?? []);
      renderImpresoras(datos.impresoras ?? []);
      renderControladores(datos.controladores ?? []);
      renderSonidos(datos.sonidos ?? []);
      renderPuertos(datos.puertos ?? []);
      renderRanuras(datos.ranuras ?? []);

      $('#ocs-estado').addClass('d-none');
      $('#ocs-contenido').removeClass('d-none');
    },

    error: xhr => {
      $('#ocs-estado').html(`
        <div class="alert rounded-5 alert-danger">
          <div class="d-flex">
            <i class="bx bx-error-circle fs-4 me-2"></i>

            <div>
              <strong>No se pudo consultar OCS Inventory</strong>

              <div class="mt-1">
                ${xhr.responseJSON?.message ?? 'El servidor OCS no respondió correctamente.'}
              </div>
            </div>
          </div>
        </div>
      `);
    }
  });
});

// ── Helpers de render ─────────────────────────────────────────────────────────

// Tabla clave/valor para secciones de un solo registro (BIOS, procesador).
function tablaClaveValor(pares) {
  const filas = pares
    .map(
      ([label, dato]) => `
      <tr>
        <th class="text-muted fw-normal" style="width:240px">${label}</th>
        <td>${valor(dato)}</td>
      </tr>
    `
    )
    .join('');

  return `
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <tbody>${filas}</tbody>
      </table>
    </div>
  `;
}

// Tabla estándar para secciones con múltiples registros.
function tablaSimple(columnas, registros) {
  if (!registros.length) {
    return `
      <p class="text-muted mb-0">
        OCS no reportó información para esta sección.
      </p>
    `;
  }

  const encabezado = columnas.map(columna => `<th>${columna.label}</th>`).join('');

  const filas = registros
    .map(
      registro => `
      <tr>
        ${columnas
          .map(
            columna => `
            <td>
              ${valor(registro[columna.key])}
            </td>
          `
          )
          .join('')}
      </tr>
    `
    )
    .join('');

  return `
    <div class="table-responsive">
      <table class="table table-hover table-sm">
        <thead>
          <tr>${encabezado}</tr>
        </thead>
        <tbody>${filas}</tbody>
      </table>
    </div>
  `;
}

// ── Secciones ─────────────────────────────────────────────────────────────────

function renderResumen(d) {
  $('#ocs-resumen').html(`
    <div class="col-xl-3 col-md-6">
      <div class="card border-light shadow-none rounded-5 h-100" style="border-left:4px solid;">
        <div class="card-body">
          <small class="text-muted d-block">
            Nombre del equipo
          </small>
          <h5 class="mb-1">${valor(d.nombre_equipo)}</h5>
          <span class="text-muted">
            ${valor(d.user_equipo)}
          </span>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card shadow-none border-light rounded-5 h-100" style="border-left:4px solid;">
        <div class="card-body">
          <small class="text-muted d-block">
            Sistema operativo
          </small>
          <h6 class="mb-1">
            ${valor(d.os?.nombre)}
          </h6>
          <span class="text-muted">
            ${valor(d.os?.version)}
          </span>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card border-light shadow-none rounded-5 h-100" style="border-left:4px solid;">
        <div class="card-body">
          <small class="text-muted d-block">
            Procesador y RAM
          </small>
          <h6 class="mb-1">
            ${valor(d.procesador_equipo)}
          </h6>
          <span class="text-muted">
            ${mbAGb(d.ram_equipo)} RAM
          </span>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card border-light shadow-none rounded-5 h-100" style="border-left:4px solid;">
        <div class="card-body">
          <small class="text-muted d-block">
            Último inventario
          </small>
          <h6 class="mb-1">
            ${valor(d.ultimo_inventario)}
          </h6>
          <span class="badge bg-label-success fw-bold">
            Reportado por OCS
          </span>
        </div>
      </div>
    </div>
  `);
}

function renderBios(d) {
  if (!d) {
    $('#ocs-bios').html('<p class="text-muted mb-0">OCS no reportó información de BIOS.</p>');
    return;
  }

  $('#ocs-bios').html(
    tablaClaveValor([
      ['Fabricante del equipo', d.fabricante_dispositivo],
      ['Modelo del equipo', d.modelo_dispositivo],
      ['N.° de serie', d.sn_dispositivo],
      ['Tipo', d.tipo_dispositivo],
      ['Fabricante BIOS', d.fabricante_bios],
      ['Versión BIOS', d.version_bios],
      ['Fecha de lanzamiento BIOS', d.fecha_lanzamiento_bios]
    ])
  );
}

function renderProcesador(d) {
  if (!d) {
    $('#ocs-procesador').html('<p class="text-muted mb-0">OCS no reportó información del procesador.</p>');
    return;
  }

  $('#ocs-procesador').html(
    tablaClaveValor([
      ['Fabricante', d.fabricante_cpu],
      ['Descripción', d.descripcion_cpu],
      ['Serie', d.sn_cpu],
      ['Velocidad (MHz)', d.velocidad_cpu],
      ['Núcleos', d.nucleos_cpu],
      ['Hilos', d.hilos_cpu],
      ['Caché L2', d.cacheL2_cpu],
      ['Arquitectura', d.arquitectura_cpu],
      ['Ancho de banda', d.ancho_banda_cpu],
      ['Voltaje', d.voltaje_cpu],
      ['Socket', d.socket_cpu]
    ])
  );
}

function renderMemorias(registros) {
  $('#ocs-memorias').html(
    tablaSimple(
      [
        { key: 'descripcion_ram', label: 'Descripción' },
        { key: 'capacidad_ram', label: 'Capacidad MB' },
        { key: 'velocidad_ram', label: 'Velocidad' },
        { key: 'nranura_ram', label: 'Ranura' },
        { key: 'sn_ram', label: 'Serie' }
      ],
      registros
    )
  );
}

function renderAlmacenamiento(registros) {
  $('#ocs-almacenamiento').html(
    tablaSimple(
      [
        { key: 'nombre_almacenamiento', label: 'Nombre' },
        { key: 'fabricante_almacenamiento', label: 'Fabricante' },
        { key: 'tipo_almacenamiento', label: 'Tipo' },
        { key: 'tamano_almacenamiento', label: 'Tamaño MB' },
        { key: 'sn_almacenamiento', label: 'Serie' },
        { key: 'firmware_almacenamiento', label: 'Firmware' }
      ],
      registros
    )
  );
}
function barraCapacidad(total, libre) {
  total = parseFloat(total);
  libre = parseFloat(libre);

  if (!total || total <= 0) return '';

  const usado = total - libre;
  const porcentaje = Math.round((usado / total) * 100);

  let color = 'bg-success';
  if (porcentaje >= 70) color = 'bg-warning';
  if (porcentaje >= 90) color = 'bg-danger';

  return `
    <div class="progress" style="height: 18px; border-radius:10px;">
      <div class="progress-bar ${color}"
           role="progressbar"
           style="width: ${porcentaje}%; border-radius:10px;"
           aria-valuenow="${porcentaje}"
           aria-valuemin="0"
           aria-valuemax="100">
        ${porcentaje}%
      </div>
    </div>
  `;
}

function renderDiscos(registros) {
  if (!registros.length) {
    $('#ocs-discos').html('<p class="text-muted mb-0">OCS no reportó unidades.</p>');
    return;
  }

  const filas = registros
    .map(
      r => `
    <tr>
      <td>${valor(r.letra_unidad_disco)}</td>
      <td>${valor(r.tipo_unidad)}</td>
      <td>${valor(r.sistema_archivo)}</td>
      <td>${valor(r.espacio_total_disco)}</td>
      <td>${valor(r.espacio_libre_disco)}</td>
      <td>${valor(r.etiqueta_volumen_disco)}</td>
      <td style="min-width:160px">${barraCapacidad(r.espacio_total_disco, r.espacio_libre_disco)}</td>
    </tr>
  `
    )
    .join('');

  $('#ocs-discos').html(`
    <div class="table-responsive">
      <table class="table table-hover table-sm">
        <thead>
          <tr>
            <th>Unidad</th>
            <th>Tipo</th>
            <th>Sistema de archivos</th>
            <th>Total MB</th>
            <th>Libre MB</th>
            <th>Etiqueta</th>
            <th>Capacidad</th>
          </tr>
        </thead>
        <tbody>${filas}</tbody>
      </table>
    </div>
  `);
}

function renderVideo(registros) {
  $('#ocs-video').html(
    tablaSimple(
      [
        { key: 'nombre_video', label: 'Nombre' },
        { key: 'chipset_video', label: 'Chipset' },
        { key: 'memoria_video', label: 'Memoria MB' },
        { key: 'resolucion_video', label: 'Resolución' }
      ],
      registros
    )
  );
}

function renderRedes(registros) {
  if (!registros.length) {
    $('#ocs-redes').html('<tr><td colspan="7" class="text-muted">OCS no reportó interfaces de red.</td></tr>');
    return;
  }

  const filas = registros
    .map(
      r => `
      <tr>
        <td>${valor(r.descripcion_red)}</td>
        <td>${valor(r.tipo_red)}</td>
        <td>${valor(r.estado_reed)}</td>
        <td>${valor(r.ip_host_red)}</td>
        <td>${valor(r.mac_red)}</td>
        <td>${valor(r.velocidad_red)}</td>
        <td>${valor(r.puerta_enlace_red)}</td>
      </tr>
    `
    )
    .join('');

  $('#ocs-redes').html(filas);
}

function renderMonitores(registros) {
  $('#ocs-monitores').html(
    tablaSimple(
      [
        { key: 'fabricante_monitor', label: 'Fabricante' },
        { key: 'identificador_monitor', label: 'Identificador' },
        { key: 'descripcion_monitor', label: 'Descripción' },
        { key: 'tipo_monitor', label: 'Tipo' },
        { key: 'sn_monitor', label: 'Serie' }
      ],
      registros
    )
  );
}

function renderEntradas(registros) {
  $('#ocs-entradas').html(
    tablaSimple(
      [
        { key: 'tipo_dispositivo_entrada', label: 'Tipo' },
        { key: 'descripcion_entrada', label: 'Descripción' },
        { key: 'fabricante_entrada', label: 'Fabricante' },
        { key: 'captura_entrada', label: 'Detalle' },
        { key: 'interfaz_entrada', label: 'Interfaz' }
      ],
      registros
    )
  );
}

function renderImpresoras(registros) {
  $('#ocs-impresoras').html(
    tablaSimple(
      [
        { key: 'nombre_impresora', label: 'Nombre' },
        { key: 'controlador_impresora', label: 'Controlador' },
        { key: 'puerto_impresora', label: 'Puerto' },
        { key: 'resolucion_impresora', label: 'Resolución' }
      ],
      registros
    )
  );
}

function renderControladores(registros) {
  $('#contenido-controladores').html(
    tablaSimple(
      [
        { key: 'nombre_controlador', label: 'Nombre' },
        { key: 'fabricante_controlador', label: 'Fabricante' },
        { key: 'tipo_controlador', label: 'Tipo' },
        { key: 'version_controlador', label: 'Versión' }
      ],
      registros
    )
  );
}

function renderSonidos(registros) {
  $('#contenido-sonidos').html(
    tablaSimple(
      [
        { key: 'nombre_sonido', label: 'Nombre' },
        { key: 'fabricante_sonido', label: 'Fabricante' },
        { key: 'descripcion_sonido', label: 'Descripción' }
      ],
      registros
    )
  );
}

function renderPuertos(registros) {
  $('#contenido-puertos').html(
    tablaSimple(
      [
        { key: 'tipo_port', label: 'Tipo' },
        { key: 'nombre_port', label: 'Nombre' },
        { key: 'interfaz_port', label: 'Interfaz' },
        { key: 'descripcion_port', label: 'Descripción' }
      ],
      registros
    )
  );
}

function renderRanuras(registros) {
  $('#contenido-ranuras').html(
    tablaSimple(
      [
        { key: 'nombre_ranura', label: 'Nombre' },
        { key: 'descricion_ranura', label: 'Descripción' },
        { key: 'designacion_ranura', label: 'Designación' }
      ],
      registros
    )
  );
}
