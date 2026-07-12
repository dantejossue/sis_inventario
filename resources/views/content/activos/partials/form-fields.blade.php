{{-- Card 1: Identificación --}}
<div class="row">
  <div class="col-xl-8 col-lg-7">

    <div class="card mb-4 rounded-4">
      <div class="card-header border-bottom d-flex align-items-center py-4 bg-label-primary rounded-top-4">
        <i class="bx bx-detail me-2 text-primary"></i>
        <h6 class="m-0 fw-bold d-flex align-items-center">Datos generales del
          activo</h6>
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
              <input type="text"
                class="form-control text-uppercase @error('codigo_patrimonial') is-invalid @enderror"
                id="codigo_patrimonial" name="codigo_patrimonial"
                value="{{ old('codigo_patrimonial', $activo?->codigo_patrimonial) }}" placeholder="Ej: 740800001234">
              <label>Código Patrimonial</label>
              @error('codigo_patrimonial')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Opcional; único si se registra (dato referencial SIGA/Patrimonio).</small>
            </div>
          </div>

          {{-- <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <select class="form-select @error('id_validacion') is-invalid @enderror" id="id_validacion"
                name="id_validacion">
                <option value="" disabled selected>-- Seleccionar --</option>
                <option value="VALIDADO">Validado</option>
                <option value="PENDIENTE_DE_VALIDACION">Pendiente de validacion</option>
                <option value="OBSERVADO">Observado</option>
                <option value="EN_REVISION">En revisión</option>
              </select>
              <label>Estado Validación <span class="text-danger">*</span></label>
              @error('id_modelo')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div> --}}


          <div class="col-md-12">
            <div class="form-floating form-floating-outline">
              <select class="form-select @error('id_modelo') is-invalid @enderror" id="id_modelo" name="id_modelo">
                <option value="">Seleccionar modelo...</option>
                @foreach ($modelos as $m)
                  <option value="{{ $m['id_modelo'] }}" data-ficha="{{ $m['requiere_ficha'] ? 1 : 0 }}"
                    data-marca="{{ $m['marca_nombre'] }}" data-modelo="{{ $m['nombre'] }}"
                    data-categoria="{{ $m['categoria_nombre'] }}" data-icono="{{ $m['categoria_icono'] }}"
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

          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control @error('numero_serie') is-invalid @enderror" id="numero_serie"
                name="numero_serie" value="{{ old('numero_serie', $activo?->numero_serie) }}"
                placeholder="Ej: 5CD123456">
              <label>Número de Serie</label>
              @error('numero_serie')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <select class="form-select @error('condicion_actual') is-invalid @enderror" id="condicion_actual"
                name="condicion_actual">
                <option value="">Seleccionar condición...</option>
                @foreach ($condiciones as $code => $label)
                  <option value="{{ $code }}"
                    {{ old('condicion_actual', $activo?->condicion_actual ?? 'BUENO') == $code ? 'selected' : '' }}>
                    {{ $label }}
                  </option>
                @endforeach
              </select>
              <label>Condición <span class="text-danger">*</span></label>
              @error('condicion_actual')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="situacion_actual_info" readonly
                value="{{ $activo ? ($situaciones[$activo->situacion_actual] ?? $activo->situacion_actual) : 'Se asigna automáticamente' }}">
              <label>Situación</label>
            </div>
            <small class="text-muted">La gestionan los movimientos (préstamo, transferencia, regularización).</small>
          </div>

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

    {{-- Card 2: Responsable y ubicación física --}}
    <div class="card mb-4 rounded-4">
      <div class="card-header border-bottom d-flex align-items-center py-4">
        <i class="bx bx-map-pin me-2 text-primary"></i>
        <h6 class="mb-0 fw-bold d-flex align-items-center">Responsable y ubicación física</h6>
      </div>
      <div class="card-body pt-4">
        <div class="row g-4">
          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <select class="form-select @error('id_responsable_actual') is-invalid @enderror"
                id="id_responsable_actual" name="id_responsable_actual">
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
              <label>Responsable asignado</label>
              @error('id_responsable_actual')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
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
              <button class="btn btn-outline-secondary" type="button" id="btnLimpiarUbicacion"
                title="Quitar ubicación">
                <i class="bx bx-x"></i>
              </button>
            </div>
            @error('id_ubicacion_actual')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          {{-- <div class="col-md-6">
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
          </div> --}}

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
        </div>
      </div>
    </div>

    {{-- Card 3: Ficha Técnica TI — solo visible si la categoría del modelo la requiere.
     El JS (activos-form.js) muestra/oculta esta card según el modelo elegido. --}}
    @php $tec = $activo?->activoTecnico; @endphp
    <div class="card mb-4 rounded-4" id="ficha-tecnica-wrap">
      <div class="card-header border-bottom py-4">
        <h6 class="mb-0 fw-bold d-flex align-items-center"><i class="bx bx-chip me-2 text-primary"></i>Ficha Técnica
          TI
        </h6>
      </div>
      <div class="card-body pt-4">
        <div class="row g-4">

          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="tec_procesador" name="tec_procesador"
                value="{{ old('tec_procesador', $tec?->procesador) }}" placeholder="Ej: Intel Core i5-1135G7">
              <label>Procesador</label>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="tec_memoria_ram" name="tec_memoria_ram"
                value="{{ old('tec_memoria_ram', $tec?->memoria_ram) }}" placeholder="Ej: 8 GB">
              <label>Memoria RAM</label>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="tec_almacenamiento" name="tec_almacenamiento"
                value="{{ old('tec_almacenamiento', $tec?->almacenamiento) }}" placeholder="Ej: 512 GB">
              <label>Almacenamiento</label>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-floating form-floating-outline">
              <select class="form-select" id="tec_tipo_almacenamiento" name="tec_tipo_almacenamiento">
                <option value="">—</option>
                @foreach (['HDD', 'SSD', 'NVME', 'EMMC', 'OTRO'] as $opt)
                  <option value="{{ $opt }}"
                    {{ old('tec_tipo_almacenamiento', $tec?->tipo_almacenamiento) === $opt ? 'selected' : '' }}>
                    {{ $opt }}</option>
                @endforeach
              </select>
              <label>Tipo de Disco</label>
            </div>
          </div>

          <div class="col-md-5">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="tec_sistema_operativo" name="tec_sistema_operativo"
                value="{{ old('tec_sistema_operativo', $tec?->sistema_operativo) }}"
                placeholder="Ej: Windows 11 Pro">
              <label>Sistema Operativo</label>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <select class="form-select" id="tec_estado_operativo" name="tec_estado_operativo">
                @php $eo = old('tec_estado_operativo', $tec?->estado_operativo ?? 'OPERATIVO'); @endphp
                @foreach (['OPERATIVO', 'INOPERATIVO', 'EN_REVISION', 'EN_MANTENIMIENTO', 'PENDIENTE_BAJA', 'DADO_DE_BAJA'] as $opt)
                  <option value="{{ $opt }}" {{ $eo === $opt ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', $opt) }}</option>
                @endforeach
              </select>
              <label>Estado Operativo</label>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="tec_nombre_equipo" name="tec_nombre_equipo"
                value="{{ old('tec_nombre_equipo', $tec?->nombre_equipo) }}" placeholder="Ej: PC-OTI-01">
              <label>Nombre de Equipo</label>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="tec_direccion_ip" name="tec_direccion_ip"
                value="{{ old('tec_direccion_ip', $tec?->direccion_ip) }}" placeholder="Ej: 192.168.1.50">
              <label>Dirección IP</label>
            </div>
          </div>

          <div class="col-md-5">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="tec_direccion_mac" name="tec_direccion_mac"
                value="{{ old('tec_direccion_mac', $tec?->direccion_mac) }}" placeholder="Ej: 00:1A:2B:3C:4D:5E">
              <label>Dirección MAC</label>
            </div>
          </div>

          {{-- <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="tec_dominio" name="tec_dominio"
                value="{{ old('tec_dominio', $tec?->dominio) }}" placeholder="Ej: undc.local">
              <label>Dominio</label>
            </div>
          </div> --}}

          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="tec_licencia_office" name="tec_licencia_office"
                value="{{ old('tec_licencia_office', $tec?->licencia_office) }}"
                placeholder="Ej: Office 2021 / M365">
              <label>Licencia Office</label>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="tec_antivirus" name="tec_antivirus"
                value="{{ old('tec_antivirus', $tec?->antivirus) }}" placeholder="Ej: Kaspersky Endpoint">
              <label>Antivirus</label>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control" id="tec_accesorios" name="tec_accesorios"
                value="{{ old('tec_accesorios', $tec?->accesorios) }}" placeholder="Ej: Mouse, teclado, cargador">
              <label>Accesorios</label>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-floating form-floating-outline">
              <textarea class="form-control" id="tec_observaciones_tecnicas" name="tec_observaciones_tecnicas"
                placeholder="Observaciones técnicas" style="height:80px">{{ old('tec_observaciones_tecnicas', $tec?->observaciones_tecnicas) }}</textarea>
              <label>Observaciones Técnicas</label>
            </div>
          </div>

        </div>
      </div>
    </div>

    {{-- Card 4. Datos patrimoniales --}}
    <div class="card mb-4 rounded-4">
      <div class="card-header border-bottom d-flex align-items-center py-4">
        <i class="bx bx-data me-2 text-primary"></i>
        <h6 class="mb-0 fw-bold d-flex align-items-center">Datos patrimoniales</h6>
      </div>
      <div class="card-body pt-4">
        <div class="row g-4">

          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control text-uppercase @error('codigo_siga') is-invalid @enderror"
                id="codigo_siga" name="codigo_siga" value="{{ old('codigo_siga', $activo?->codigo_siga) }}"
                placeholder="Ej: 74080001">
              <label>Código SIGA</label>
              @error('codigo_siga')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control text-uppercase @error('numero_pecosa') is-invalid @enderror"
                id="numero_pecosa" name="numero_pecosa" value="{{ old('numero_pecosa', $activo?->numero_pecosa) }}"
                placeholder="Ej: PECOSA-2025-001">
              <label>N° PECOSA</label>
              @error('numero_pecosa')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control text-uppercase @error('numero_orden_compra') is-invalid @enderror"
                id="numero_orden_compra" name="numero_orden_compra"
                value="{{ old('numero_orden_compra', $activo?->numero_orden_compra) }}" placeholder="Ej: OC-2025-045">
              <label>N° Orden de Compra</label>
              @error('numero_orden_compra')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <select class="form-select @error('estado_siga') is-invalid @enderror" id="estado_siga"
                name="estado_siga">
                @php $es = old('estado_siga', $activo?->estado_siga ?? 'NO_APLICA'); @endphp
                <option value="NO_APLICA" {{ $es === 'NO_APLICA' ? 'selected' : '' }}>No aplica</option>
                <option value="PENDIENTE_ACTUALIZACION" {{ $es === 'PENDIENTE_ACTUALIZACION' ? 'selected' : '' }}>Pendiente de actualización</option>
                <option value="REGISTRADO" {{ $es === 'REGISTRADO' ? 'selected' : '' }}>Registrado</option>
                <option value="OBSERVADO" {{ $es === 'OBSERVADO' ? 'selected' : '' }}>Observado</option>
              </select>
              <label>Estado SIGA (referencial)</label>
              @error('estado_siga')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <input type="date" class="form-control @error('fecha_alta_siga') is-invalid @enderror"
                id="fecha_alta_siga" name="fecha_alta_siga"
                value="{{ old('fecha_alta_siga', optional($activo?->fecha_alta_siga)->format('Y-m-d')) }}">
              <label>Fecha de Alta SIGA</label>
              @error('fecha_alta_siga')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <input type="date" class="form-control @error('fecha_adquisicion') is-invalid @enderror"
                id="fecha_adquisicion" name="fecha_adquisicion"
                value="{{ old('fecha_adquisicion', optional($activo?->fecha_adquisicion)->format('Y-m-d')) }}">
              <label>Fecha de Adquisición</label>
              @error('fecha_adquisicion')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <input type="number" class="form-control @error('valor_compra') is-invalid @enderror"
                id="valor_compra" name="valor_compra" value="{{ old('valor_compra', $activo?->valor_compra) }}"
                placeholder="0.00" step="0.01" min="0">
              <label>Valor de Compra (S/.)</label>
              @error('valor_compra')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-md-8">
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
                value="{{ old('garantia_inicio', optional($activo?->garantia_inicio)->format('Y-m-d')) }}">
              <label>Garantía — Inicio</label>
              @error('garantia_inicio')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <input type="date" class="form-control @error('garantia_fin') is-invalid @enderror"
                id="garantia_fin" name="garantia_fin"
                value="{{ old('garantia_fin', optional($activo?->garantia_fin)->format('Y-m-d')) }}">
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

    {{-- Card 5. Documentos y evidencias --}}
    <div class="card mb-4 rounded-4">
      <div class="card-header border-bottom d-flex align-items-center py-4">
        <i class="bx bx-paperclip me-2 text-primary"></i>
        <h6 class="mb-0 fw-bold">Documentos y evidencias</h6>
      </div>

      <div class="card-body pt-4">
        <div class="row g-4">

          <div class="col-md-12">
            <label for="tipo_documento" class="form-label">
              Tipo de documento
            </label>

            <select id="tipo_documento" name="tipo_documento" class="form-select">
              <option value="">Seleccione...</option>
              <option value="ORDEN_COMPRA">Orden de compra</option>
              <option value="ACTA_ENTREGA">Acta de entrega</option>
              <option value="FACTURA">Factura</option>
              <option value="GARANTIA">Garantía</option>
              <option value="FOTO_ACTIVO">Foto del activo</option>
              <option value="FICHA_TECNICA">Ficha técnica</option>
              <option value="SUSTENTO_SIGA">Sustento SIGA</option>
              <option value="EXPEDIENTE_TRAMITE">
                Expediente de trámite documentario
              </option>
              <option value="ARCHIVO_COMPRIMIDO">
                Archivo comprimido
              </option>
              <option value="OTRO">Otro</option>
            </select>
          </div>

          <div class="col-md-12">
            <label for="documentos_activo" class="register-asset-document-zone" id="zonaDocumentos">
              <div class="register-asset-document-icon">
                <i class="bx bx-cloud-upload"></i>
              </div>

              <div>
                <h6 class="mb-1">
                  Arrastra o selecciona documentos del activo
                </h6>

                <small class="text-muted">
                  Puedes adjuntar PDF, imágenes y documentos de Office (máx. 5 MB c/u).
                </small>
              </div>
            </label>

            <input type="file" id="documentos_activo" name="documentos[]" class="d-none" multiple
              accept=".pdf,.jpg,.jpeg,.png,.webp,.xls,.xlsx,.doc,.docx" />

            <div id="listaDocumentos" class="mt-3"></div>
          </div>

        </div>
      </div>
    </div>


  </div>
  <div class="col-xl-4 col-lg-5">

    <!-- Resumen -->
    <div class="card mb-4 rounded-4 register-asset-sticky">
      <div class="card-header lh-1">
        <div class="d-flex align-items-center">
          <i class="bx bx-info-circle me-1 text-warning"></i>
          <h5 class="mb-0">
            Resumen del activo
          </h5>
        </div>
        <small class="text-secondary fw-light">
          Vista previa antes de registrar.
        </small>
      </div>

      <div class="card-body">

        <div class="d-flex flex-column align-items-center">
          <div class="p-4 rounded-5 bg-label-primary d-flex align-items-center">
            <i class="bx bx-box fs-4" id="previewIconoActivo"></i>
          </div>

          <h5 id="previewNombreActivo" class="mb-1">
            Laptop Lenovo ThinkPad E14
          </h5>

          <p class="text-muted mb-3" id="previewCodigoActivo">
            LAP-OTI-000125
          </p>

          <div class="d-flex flex-wrap justify-content-center gap-2">
            <span class="badge bg-label-primary" id="previewCategoria">Laptop</span>
            <span class="badge bg-label-success" id="previewCondicion">Bueno</span>
            <span class="badge bg-label-info" id="previewSituacion">Disponible</span>
          </div>
        </div>

        <hr class="my-4">

        <div class="data-list">

          <div class="data-list-item">
            <span class="text-secondary">Código interno</span>
            <strong id="resCodigoActivo">LAP-OTI-000125</strong>
          </div>

          <div class="data-list-item">
            <span class="text-secondary">Marca</span>
            <strong id="resMarcaActivo">Lenovo</strong>
          </div>

          <div class="data-list-item">
            <span class="text-secondary">Modelo</span>
            <strong id="resModeloActivo">ThinkPad E14</strong>
          </div>

          <div class="data-list-item">
            <span class="text-secondary">Estado</span>
            <span class="badge bg-label-success" id="resEstadoRegistro">Activo</span>
          </div>

          <div class="data-list-item">
            <span class="text-secondary">Responsable</span>
            <strong id="resResponsableActivo">Sin responsable</strong>
          </div>

        </div>

      </div>
    </div>

    {{-- <!-- Etiqueta -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-barcode me-1"></i>
          Etiqueta del activo
        </h5>
        <small class="text-muted">
          Vista previa para código QR o código de barras.
        </small>
      </div>

      <div class="card-body">

        <div class="register-asset-label-card">
          <div class="register-asset-label-header">
            <strong>ACTIVO TI</strong>
            <span>OTI</span>
          </div>

          <div class="register-asset-label-body">
            <div class="register-asset-qr">
              <i class="bx bx-qr"></i>
            </div>

            <div>
              <strong id="labelCodigoActivo">LAP-OTI-000125</strong>
              <small id="labelNombreActivo">Lenovo ThinkPad E14</small>
              <small>Escanear para ver ficha técnica</small>
            </div>
          </div>
        </div>

        <div class="d-grid gap-2 mt-3">
          <button class="btn btn-outline-primary">
            <i class="bx bx-qr me-1"></i>
            Generar QR
          </button>

          <button class="btn btn-outline-secondary">
            <i class="bx bx-barcode me-1"></i>
            Generar barras
          </button>
        </div>

      </div>
    </div>

    <!-- Checklist -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-check-shield me-1"></i>
          Checklist de registro
        </h5>
        <small class="text-muted">
          Validaciones mínimas antes de guardar.
        </small>
      </div>

      <div class="card-body">

        <div class="register-asset-check-item">
          <i class="bx bx-check-circle text-success"></i>
          <div>
            <strong>Código interno definido</strong>
            <small>Identificador único del sistema</small>
          </div>
        </div>

        <div class="register-asset-check-item">
          <i class="bx bx-check-circle text-success"></i>
          <div>
            <strong>Categoría seleccionada</strong>
            <small>Clasificación del activo</small>
          </div>
        </div>

        <div class="register-asset-check-item">
          <i class="bx bx-error-circle text-warning"></i>
          <div>
            <strong>Código patrimonial opcional</strong>
            <small>Puede completarse con SIGA</small>
          </div>
        </div>

        <div class="register-asset-check-item">
          <i class="bx bx-error-circle text-warning"></i>
          <div>
            <strong>Documentos pendientes</strong>
            <small>Adjuntar si corresponde</small>
          </div>
        </div>

      </div>
    </div>

    <!-- Acciones -->
    <div class="card">
      <div class="card-body">
        <div class="d-grid gap-2">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalConfirmarRegistroActivo">
            <i class="bx bx-check me-1"></i>
            Registrar activo
          </button>

          <button class="btn btn-outline-primary">
            <i class="bx bx-save me-1"></i>
            Guardar borrador
          </button>

          <a href="activos.html" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i>
            Cancelar y volver
          </a>
        </div>
      </div>
    </div> --}}

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
