{{--
  Partial compartido entre create.blade.php y edit.blade.php
  Variables disponibles: $colaborador (solo en edit), $sedes, $sedeDependencias
--}}
@php $col = $colaborador ?? null; @endphp

<div class="row g-4">

  {{-- ══════════════════════════════════════════════
       COLUMNA IZQUIERDA — Datos personales
  ══════════════════════════════════════════════ --}}
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold d-flex align-items-center"><i class="bx bx-id-card me-2 text-primary"></i>Datos Personales
        </h6>
      </div>
      <div class="card-body py-5">

        {{-- DNI --}}
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">DNI <span class="text-danger">*</span></label>
            <input type="text" name="nro_documento" maxlength="8"
              class="form-control @error('nro_documento') is-invalid @enderror"
              value="{{ old('nro_documento', $col?->nro_documento) }}" placeholder="Ej: 74432978">
            @error('nro_documento')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-8">
            <label class="form-label fw-semibold">Nombres <span class="text-danger">*</span></label>
            <input type="text" name="per_nombre"
              class="form-control text-uppercase @error('per_nombre') is-invalid @enderror"
              value="{{ old('per_nombre', $col?->per_nombre) }}" placeholder="Ej: DANTE JOSSUE">
            @error('per_nombre')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Apellido Paterno <span class="text-danger">*</span></label>
            <input type="text" name="per_apepat"
              class="form-control text-uppercase @error('per_apepat') is-invalid @enderror"
              value="{{ old('per_apepat', $col?->per_apepat) }}" placeholder="Ej: CAMPOS">
            @error('per_apepat')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Apellido Materno</label>
            <input type="text" name="per_apemat"
              class="form-control text-uppercase @error('per_apemat') is-invalid @enderror"
              value="{{ old('per_apemat', $col?->per_apemat) }}" placeholder="Ej: OCHOA">
            @error('per_apemat')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Fecha de Nacimiento</label>
            <input type="date" name="fecha_nacimiento"
              class="form-control @error('fecha_nacimiento') is-invalid @enderror"
              value="{{ old('fecha_nacimiento', $col?->fecha_nacimiento?->format('Y-m-d')) }}">
            @error('fecha_nacimiento')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Teléfono Móvil</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bx bx-phone"></i></span>
              <input type="text" name="nro_movil" maxlength="20"
                class="form-control @error('nro_movil') is-invalid @enderror"
                value="{{ old('nro_movil', $col?->nro_movil) }}" placeholder="Ej: 987654321">
              @error('nro_movil')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Correo Electrónico</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bx bx-envelope"></i></span>
            <input type="email" name="per_email" class="form-control @error('per_email') is-invalid @enderror"
              value="{{ old('per_email', $col?->per_email) }}" placeholder="Ej: dcampos@undc.edu.pe">
            @error('per_email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mb-0">
          <label class="form-label fw-semibold">Dirección</label>
          <textarea name="per_direccion" rows="2" class="form-control @error('per_direccion') is-invalid @enderror"
            placeholder="Ej: Av. Los Álamos 123, Cañete">{{ old('per_direccion', $col?->per_direccion) }}</textarea>
          @error('per_direccion')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════
       COLUMNA DERECHA — Foto + Datos laborales
  ══════════════════════════════════════════════ --}}
  <div class="col-lg-5">

    {{-- Foto --}}
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold d-flex align-items-center"><i class="bx bx-image me-2 text-primary"></i>Foto del
          Colaborador</h6>
      </div>
      <div class="card-body text-center py-5">
        <div class="mb-3">
          <img id="fotoPreview"
            src="{{ $col?->per_foto ? asset('storage/' . $col->per_foto) : asset('assets/img/avatars/1.png') }}"
            class="rounded-circle shadow" style="width:110px;height:110px;object-fit:cover;">
        </div>
        <input type="file" name="per_foto" id="inputFoto" accept="image/*" class="d-none">
        <button type="button" class="btn btn-label-primary btn-sm"
          onclick="document.getElementById('inputFoto').click()">
          <i class="bx bx-upload me-1"></i> Subir foto
        </button>
        @if ($col?->per_foto)
          <p class="text-muted mt-2 mb-0" style="font-size:.8rem;">Foto actual guardada</p>
        @else
          <p class="text-muted mt-2 mb-0" style="font-size:.8rem;">JPG, PNG · máx. 2 MB</p>
        @endif
        @error('per_foto')
          <div class="text-danger mt-1" style="font-size:.85rem;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    {{-- Datos laborales --}}
    <div class="card">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold d-flex align-items-center"><i class="bx bx-briefcase me-2 text-primary"></i>Datos
          Laborales</h6>
      </div>
      <div class="card-body py-5">

        {{-- Sede --}}
        <div class="mb-3">
          <label class="form-label fw-semibold">Sede (Campus)</label>
          <select id="selectSede" class="form-select">
            <option value="">— Seleccione una sede —</option>
            @foreach ($sedes as $sede)
              <option value="{{ $sede->id_sede }}"
                {{ old('_sede_id', $col?->sedeDependencia?->id_sede) == $sede->id_sede ? 'selected' : '' }}>
                {{ $sede->nombre_sede }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Dependencia (filtrada por sede vía JS) --}}
        <div class="mb-3">
          <label class="form-label fw-semibold">Dependencia / Unidad</label>
          <select name="id_sede_dependencia" id="selectDependencia"
            class="form-select @error('id_sede_dependencia') is-invalid @enderror">
            <option value="">— Seleccione primero una sede —</option>
          </select>
          @error('id_sede_dependencia')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Cargo --}}
        <div class="mb-3">
          <label class="form-label fw-semibold">Cargo</label>
          <input type="text" name="cargo"
            class="form-control text-uppercase @error('cargo') is-invalid @enderror"
            value="{{ old('cargo', $col?->cargo) }}" placeholder="Ej: DIRECTOR OTI">
          @error('cargo')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Tipo colaborador --}}
        <div class="mb-3">
          <label class="form-label fw-semibold">Tipo de Colaborador <span class="text-danger">*</span></label>
          <select name="tipo_colaborador" class="form-select @error('tipo_colaborador') is-invalid @enderror">
            @foreach (['ADMINISTRATIVO', 'DOCENTE', 'TECNICO', 'PRACTICANTE', 'EXTERNO'] as $tipo)
              <option value="{{ $tipo }}"
                {{ old('tipo_colaborador', $col?->tipo_colaborador ?? 'ADMINISTRATIVO') === $tipo ? 'selected' : '' }}>
                {{ ucfirst(strtolower($tipo)) }}
              </option>
            @endforeach
          </select>
          @error('tipo_colaborador')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="row g-3">
          <div class="col-6 mb-3">
            <label class="form-label fw-semibold">Fecha Ingreso</label>
            <input type="date" name="fecha_ingreso"
              class="form-control @error('fecha_ingreso') is-invalid @enderror"
              value="{{ old('fecha_ingreso', $col?->fecha_ingreso?->format('Y-m-d')) }}">
            @error('fecha_ingreso')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-6 mb-3">
            <label class="form-label fw-semibold">Fecha Salida</label>
            <input type="date" name="fecha_salida"
              class="form-control @error('fecha_salida') is-invalid @enderror"
              value="{{ old('fecha_salida', $col?->fecha_salida?->format('Y-m-d')) }}">
            @error('fecha_salida')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

      </div>
    </div>
  </div>

</div>
