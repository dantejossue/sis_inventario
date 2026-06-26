{{-- Card 1: Identificación --}}
<div class="card mb-4">
  <div class="card-header border-bottom">
    <h6 class="mb-0 fw-bold d-flex align-items-center"><i class="bx bx-tag me-2 text-primary"></i>Identificación del
      Activo</h6>
  </div>
  <div class="card-body pt-4">
    <div class="row g-4">

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input type="text" class="form-control text-uppercase @error('codigo_interno') is-invalid @enderror"
            id="codigo_interno" name="codigo_interno" value="{{ old('codigo_interno', $activo?->codigo_interno) }}"
            placeholder="Ej: TI-001">
          <label>Código Interno <span class="text-danger">*</span></label>
          @error('codigo_interno')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input type="text" class="form-control text-uppercase @error('codigo_patrimonial') is-invalid @enderror"
            id="codigo_patrimonial" name="codigo_patrimonial"
            value="{{ old('codigo_patrimonial', $activo?->codigo_patrimonial) }}" placeholder="Ej: PAT-0001">
          <label>Código Patrimonial <span class="text-danger">*</span></label>
          @error('codigo_patrimonial')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="col-md-12">
        <div class="form-floating form-floating-outline">
          <select class="form-select @error('id_modelo') is-invalid @enderror" id="id_modelo" name="id_modelo">
            <option value="">Seleccionar modelo...</option>
            @foreach ($modelos as $m)
              <option value="{{ $m['id_modelo'] }}"
                {{ old('id_modelo', $activo?->id_modelo) == $m['id_modelo'] ? 'selected' : '' }}>
                {{ $m['marca_nombre'] }} — {{ $m['nombre'] }}
                @if ($m['categoria_nombre'])
                  ({{ $m['categoria_nombre'] }})
                @endif
              </option>
            @endforeach
          </select>
          <label>Modelo <span class="text-danger">*</span></label>
          @error('id_modelo')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <select class="form-select @error('id_condicion_actual') is-invalid @enderror" id="id_condicion_actual"
            name="id_condicion_actual">
            <option value="">Seleccionar condición...</option>
            @foreach ($condiciones as $c)
              <option value="{{ $c->id_estado_activo }}"
                {{ old('id_condicion_actual', $activo?->id_condicion_actual) == $c->id_estado_activo ? 'selected' : '' }}>
                {{ $c->nombre }}
              </option>
            @endforeach
          </select>
          <label>Condición <span class="text-danger">*</span></label>
          @error('id_condicion_actual')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      {{-- <div class="col-12">
        <div class="alert alert-info mb-0 d-flex align-items-center" role="alert">
          <i class="bx bx-info-circle me-2"></i>
          <span>La <strong>situación</strong> se gestiona automáticamente: el activo queda
            <strong>DISPONIBLE</strong> si no asignas colaborador, o <strong>ASIGNADO</strong> si lo asignas.
            Luego puedes cambiarla con un movimiento (Asignar, Préstamo, etc.).</span>
        </div>
      </div> --}}

    </div>
  </div>
</div>

{{-- Card 2: Detalles adicionales --}}
<div class="card mb-4">
  <div class="card-header border-bottom">
    <h6 class="mb-0 fw-bold d-flex align-items-center"><i class="bx bx-detail me-2 text-primary"></i>Detalles
      Adicionales</h6>
  </div>
  <div class="card-body pt-4">
    <div class="row g-4">

      <div class="col-md-6 mt-10">
        <div class="col-md-12">

          <div class="form-floating form-floating-outline">
            <input type="text" class="form-control @error('numero_serie') is-invalid @enderror" id="numero_serie"
              name="numero_serie" value="{{ old('numero_serie', $activo?->numero_serie) }}"
              placeholder="Ej: 5CD123456">
            <label>Número de Serie</label>
            @error('numero_serie')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div><br>

        <div class="col-md-12">
          <div class="form-floating form-floating-outline">
            <select class="form-select @error('id_responsable_actual') is-invalid @enderror" id="id_responsable_actual"
              name="id_responsable_actual">
              <option value="">Sin asignar</option>
              @foreach ($colaboradores as $col)
                <option value="{{ $col->id_colaborador }}"
                  {{ old('id_responsable_actual', $activo?->id_responsable_actual) == $col->id_colaborador ? 'selected' : '' }}>
                  {{ $col->per_apepat }} {{ $col->per_apemat }}, {{ $col->per_nombre }}
                  @if ($col->cargo)
                    — {{ $col->cargo }}
                  @endif
                </option>
              @endforeach
            </select>
            <label>Colaborador asignado</label>
            @error('id_responsable_actual')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div><br>

        <div class="col-md-12">
          <label class="form-label d-flex align-items-center mb-1"><i class="bx bx-map me-1"></i>Ubicación
            Física</label>

          {{-- Valor real que viaja al servidor: el id del ambiente final (nodo hoja). --}}
          <input type="hidden" name="id_ubicacion_actual" id="id_ubicacion_actual"
            value="{{ old('id_ubicacion_actual', $activo?->id_ubicacion_actual) }}">

          <div class="input-group @error('id_ubicacion_actual') is-invalid @enderror">
            {{-- Muestra la ruta jerárquica completa; JS la rellena según el id seleccionado. --}}
            <input type="text" class="form-control bg-label-secondary" id="ubicacionDisplay" readonly
              placeholder="Sin ubicación seleccionada" style="cursor:pointer" data-bs-toggle="modal"
              data-bs-target="#modalUbicacionTree">
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal"
              data-bs-target="#modalUbicacionTree">
              <i class="bx bx-folder-open me-1"></i> Elegir
            </button>
            <button class="btn btn-outline-secondary" type="button" id="btnLimpiarUbicacion" title="Quitar ubicación">
              <i class="bx bx-x"></i>
            </button>
          </div>
          @error('id_ubicacion_actual')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <small class="text-muted">Navega hasta el ambiente final (último nivel); la ruta completa se guarda
            automáticamente.</small>
        </div>
      </div>

      <div class="col-md-6">
        @php
          $imgPlaceholder =
              "data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180' viewBox='0 0 180 180'%3E%3Crect width='180' height='180' fill='%23f1f1f4'/%3E%3Cg fill='%23a8aab4'%3E%3Cpath d='M60 66h60a4 4 0 0 1 4 4v40a4 4 0 0 1-4 4H60a4 4 0 0 1-4-4V70a4 4 0 0 1 4-4zm0 4v33l16-16 9 9 18-18 13 13V70H60zm14 12a6 6 0 1 0 0-12 6 6 0 0 0 0 12z'/%3E%3C/g%3E%3C/svg%3E";
        @endphp
        <div class="card-body text-center py-5">
          <div class="mb-3">
            <img id="imagenPreview"
              src="{{ $activo?->imagen ? asset('storage/' . $activo->imagen) : $imgPlaceholder }}"
              class="rounded shadow" style="width:180px;height:180px;object-fit:cover;background:#f1f1f4;">
          </div>
          <input type="file" name="imagen" id="inputImagen" accept="image/*" class="d-none">
          <button type="button" class="btn btn-label-primary btn-sm"
            onclick="document.getElementById('inputImagen').click()">
            <i class="bx bx-upload me-1"></i> Subir imagen
          </button>
          @if ($activo?->imagen)
            <p class="text-muted mt-2 mb-0" style="font-size:.8rem;">Imagen actual guardada</p>
          @else
            <p class="text-muted mt-2 mb-0" style="font-size:.8rem;">JPG, PNG, WEBP · máx. 2 MB</p>
          @endif
          @error('imagen')
            <div class="text-danger mt-1" style="font-size:.85rem;">{{ $message }}</div>
          @enderror
        </div>
      </div>

      {{-- <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <select class="form-select @error('id_responsable_actual') is-invalid @enderror" id="id_responsable_actual"
            name="id_responsable_actual">
            <option value="">Sin asignar</option>
            @foreach ($colaboradores as $col)
              <option value="{{ $col->id_colaborador }}"
                {{ old('id_responsable_actual', $activo?->id_responsable_actual) == $col->id_colaborador ? 'selected' : '' }}>
                {{ $col->per_apepat }} {{ $col->per_apemat }}, {{ $col->per_nombre }}
                @if ($col->cargo)
                  — {{ $col->cargo }}
                @endif
              </option>
            @endforeach
          </select>
          <label>Colaborador asignado</label>
          @error('id_responsable_actual')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <small class="text-muted">Opcional.</small>
      </div> --}}



      {{-- Ubicación física del activo (independiente del responsable) --}}
      {{-- <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <select class="form-select @error('id_ubicacion_actual') is-invalid @enderror" id="id_ubicacion_actual"
            name="id_ubicacion_actual">
            <option value="">Sin ubicación</option>
            @foreach ($ubicaciones->groupBy(fn($u) => $u->sede?->nombre_sede ?? 'Sin sede') as $sedeNombre => $items)
              <optgroup label="{{ $sedeNombre }}">
                @foreach ($items as $u)
                  <option value="{{ $u->id_ubicacion }}"
                    {{ old('id_ubicacion_actual', $activo?->id_ubicacion_actual) == $u->id_ubicacion ? 'selected' : '' }}>
                    {{ $u->nombre }} ({{ $u->tipo }}){{ $u->codigo ? ' — ' . $u->codigo : '' }}
                  </option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
          <label class="d-flex align-items-center"><i class="bx bx-map me-1"></i>Ubicación Física</label>
          @error('id_ubicacion_actual')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <small class="text-muted">Lugar físico donde se encuentra el equipo.</small>
      </div> --}}

      <div class="col-md-12">
        <div class="form-floating form-floating-outline">
          <input type="text" class="form-control @error('descripcion') is-invalid @enderror" id="descripcion"
            name="descripcion" value="{{ old('descripcion', $activo?->descripcion) }}"
            placeholder="Descripción breve">
          <label>Descripción</label>
          @error('descripcion')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-floating form-floating-outline">
          <input type="date" class="form-control @error('fecha_adquisicion') is-invalid @enderror"
            id="fecha_adquisicion" name="fecha_adquisicion"
            value="{{ old('fecha_adquisicion', $activo?->fecha_adquisicion) }}">
          <label>Fecha de Adquisición</label>
          @error('fecha_adquisicion')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-floating form-floating-outline">
          <input type="number" class="form-control @error('valor_compra') is-invalid @enderror" id="valor_compra"
            name="valor_compra" value="{{ old('valor_compra', $activo?->valor_compra) }}" placeholder="0.00"
            step="0.01" min="0">
          <label>Valor de Compra (S/.)</label>
          @error('valor_compra')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-floating form-floating-outline">
          <input type="text" class="form-control text-uppercase @error('proveedor') is-invalid @enderror"
            id="proveedor" name="proveedor" value="{{ old('proveedor', $activo?->proveedor) }}"
            placeholder="Ej: HP PERU SAC">
          <label>Proveedor</label>
          @error('proveedor')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input type="date" class="form-control @error('garantia_inicio') is-invalid @enderror"
            id="garantia_inicio" name="garantia_inicio"
            value="{{ old('garantia_inicio', $activo?->garantia_inicio) }}">
          <label>Garantía — Inicio</label>
          @error('garantia_inicio')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input type="date" class="form-control @error('garantia_fin') is-invalid @enderror" id="garantia_fin"
            name="garantia_fin" value="{{ old('garantia_fin', $activo?->garantia_fin) }}">
          <label>Garantía — Fin</label>
          @error('garantia_fin')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="col-md-12">
        <div class="form-floating form-floating-outline">
          <textarea class="form-control @error('observaciones') is-invalid @enderror" id="observaciones" name="observaciones"
            placeholder="Observaciones" style="height:90px">{{ old('observaciones', $activo?->observaciones) }}</textarea>
          <label>Observaciones</label>
          @error('observaciones')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

    </div>
  </div>
</div>

{{-- Card 3: Imagen del activo --}}
{{-- <div class="card mb-4">
  <div class="card-header border-bottom">
    <h6 class="mb-0 fw-bold d-flex align-items-center"><i class="bx bx-image me-2 text-primary"></i>Imagen del Activo
    </h6>
  </div>
  @php
    $imgPlaceholder =
        "data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180' viewBox='0 0 180 180'%3E%3Crect width='180' height='180' fill='%23f1f1f4'/%3E%3Cg fill='%23a8aab4'%3E%3Cpath d='M60 66h60a4 4 0 0 1 4 4v40a4 4 0 0 1-4 4H60a4 4 0 0 1-4-4V70a4 4 0 0 1 4-4zm0 4v33l16-16 9 9 18-18 13 13V70H60zm14 12a6 6 0 1 0 0-12 6 6 0 0 0 0 12z'/%3E%3C/g%3E%3C/svg%3E";
  @endphp
  <div class="card-body text-center py-5">
    <div class="mb-3">
      <img id="imagenPreview" src="{{ $activo?->imagen ? asset('storage/' . $activo->imagen) : $imgPlaceholder }}"
        class="rounded shadow" style="width:180px;height:180px;object-fit:cover;background:#f1f1f4;">
    </div>
    <input type="file" name="imagen" id="inputImagen" accept="image/*" class="d-none">
    <button type="button" class="btn btn-label-primary btn-sm"
      onclick="document.getElementById('inputImagen').click()">
      <i class="bx bx-upload me-1"></i> Subir imagen
    </button>
    @if ($activo?->imagen)
      <p class="text-muted mt-2 mb-0" style="font-size:.8rem;">Imagen actual guardada</p>
    @else
      <p class="text-muted mt-2 mb-0" style="font-size:.8rem;">JPG, PNG, WEBP · máx. 2 MB</p>
    @endif
    @error('imagen')
      <div class="text-danger mt-1" style="font-size:.85rem;">{{ $message }}</div>
    @enderror
  </div>
</div> --}}

<script>
  document.getElementById('inputImagen')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    document.getElementById('imagenPreview').src = URL.createObjectURL(file);
  });
</script>

{{-- ═══════════════════════════════════════════════════════════════════════
     MODAL — ÁRBOL DE UBICACIONES (Sede › Pabellón › Piso › Ambiente)
     Solo los nodos hoja (sin hijos activos) son seleccionables.
═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalUbicacionTree" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom py-4">
        <h5 class="modal-title fw-bold d-flex align-items-center">
          <i class="bx bx-map-pin me-2"></i>Seleccionar ubicación física
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="input-group input-group-merge mb-3">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" class="form-control" id="ubicacionBuscar"
            placeholder="Buscar ambiente, piso, pabellón...">
        </div>
        <div id="ubicacionTree" class="ubic-tree"></div>
        <div id="ubicacionTreeVacio" class="text-center text-muted py-4 d-none">
          <i class="bx bx-folder-open bx-sm d-block mb-2"></i>
          No hay ubicaciones activas registradas.
        </div>
      </div>
      <div class="modal-footer border-top py-4">
        <span class="me-auto small text-muted d-flex align-items-center">
          <i class="bx bx-info-circle me-1"></i>Solo puedes elegir el último nivel (ambiente final).
        </span>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

{{-- Datos del árbol: solo ubicaciones ACTIVAS, en plano (id + padre + sede). --}}
@php
  $ubicacionesData = $ubicaciones
      ->map(
          fn($u) => [
              'id' => $u->id_ubicacion,
              'padre' => $u->id_ubicacion_padre,
              'sede_id' => $u->id_sede,
              'sede' => $u->sede?->nombre_sede ?? 'Sin sede',
              'nombre' => $u->nombre,
              'tipo' => $u->tipo,
              'codigo' => $u->codigo,
          ],
      )
      ->values();
@endphp
<script id="ubicacionesData" type="application/json">
  {!! $ubicacionesData->toJson() !!}
</script>

<style>
  .ubic-tree ul {
    list-style: none;
    margin: 0;
    padding-left: 1.25rem;
  }

  .ubic-tree>ul {
    padding-left: 0;
  }

  .ubic-tree .ubic-node {
    display: flex;
    align-items: center;
    gap: .35rem;
    padding: .25rem .4rem;
    border-radius: .35rem;
  }

  .ubic-tree .ubic-node:hover {
    background: rgba(105, 108, 255, .08);
  }

  .ubic-tree .ubic-toggle {
    width: 1.1rem;
    flex: 0 0 1.1rem;
    text-align: center;
    cursor: pointer;
    color: #8592a3;
  }

  .ubic-tree .ubic-toggle.is-leaf {
    visibility: hidden;
  }

  .ubic-tree .ubic-label {
    cursor: pointer;
    flex: 1;
  }

  .ubic-tree .ubic-label .ubic-tipo {
    font-size: .72rem;
    color: #8592a3;
    margin-left: .35rem;
  }

  .ubic-tree .ubic-leaf>.ubic-node {
    cursor: pointer;
  }

  .ubic-tree .ubic-leaf.is-selected>.ubic-node {
    background: rgba(105, 108, 255, .16);
    font-weight: 600;
  }

  .ubic-tree .ubic-children.collapsed {
    display: none;
  }

  .ubic-tree .ubic-group>.ubic-node {
    font-weight: 600;
  }
</style>
