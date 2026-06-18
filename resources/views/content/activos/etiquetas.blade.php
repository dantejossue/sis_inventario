<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Etiquetas de Activos — OTI UNDC</title>
  <style>
    * { box-sizing: border-box; }

    body {
      margin: 0;
      background: #eceef1;
      font-family: 'Segoe UI', Roboto, Arial, sans-serif;
      color: #2b2c40;
    }

    /* Barra de acciones (no se imprime) */
    .toolbar {
      position: sticky;
      top: 0;
      z-index: 10;
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: .9rem 1.25rem;
      background: #fff;
      border-bottom: 1px solid #e0e0e6;
      box-shadow: 0 2px 6px rgba(0, 0, 0, .04);
    }

    .toolbar h1 { font-size: 1rem; margin: 0; font-weight: 700; }
    .toolbar .spacer { flex: 1; }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      border: 0;
      border-radius: .375rem;
      padding: .5rem .9rem;
      font-size: .85rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      color: #fff;
      background: #696cff;
    }
    .btn.secondary { background: #e4e4e9; color: #555; }

    /* Hoja de etiquetas */
    .sheet {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      padding: 16px;
    }

    .label {
      width: 62mm;
      min-height: 30mm;
      background: #fff;
      border: 1px dashed #b9bcc6;
      border-radius: 4px;
      padding: 6px 8px;
      display: flex;
      gap: 8px;
      align-items: center;
      page-break-inside: avoid;
      break-inside: avoid;
    }

    .label .qr { width: 26mm; flex: 0 0 26mm; }
    .label .qr svg { width: 100%; height: auto; display: block; }

    .label .info { flex: 1; min-width: 0; }
    .label .org { font-size: 8px; font-weight: 700; color: #696cff; letter-spacing: .3px; text-transform: uppercase; }
    .label .cod-int { font-size: 15px; font-weight: 800; line-height: 1.1; }
    .label .modelo {
      font-size: 9px; color: #6f6f7a;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
      margin-bottom: 2px;
    }
    .label .barcode svg { width: 100%; height: 46px; }

    .empty { padding: 3rem; text-align: center; color: #777; }

    @media print {
      body { background: #fff; }
      .toolbar { display: none; }
      .sheet { padding: 0; gap: 0; }
      .label { border: 1px solid #ccc; border-radius: 0; }
    }
  </style>
  @vite(['resources/js/pages/activos/etiquetas.js'])
</head>

<body>
  <div class="toolbar">
    <h1>🏷️ Etiquetas de Activos · {{ count($etiquetas) }}</h1>
    <div class="spacer"></div>
    <a href="{{ route('activos.index') }}" class="btn secondary">← Volver</a>
    <button class="btn" onclick="window.print()">🖨️ Imprimir</button>
  </div>

  @if (count($etiquetas) === 0)
    <p class="empty">No hay activos para generar etiquetas.</p>
  @else
    <div class="sheet">
      @foreach ($etiquetas as $i => $e)
        <div class="label">
          <div class="qr" id="qr-{{ $i }}"></div>
          <div class="info">
            <div class="org">OTI · UNDC</div>
            <div class="cod-int">{{ $e['codigo_interno'] }}</div>
            <div class="modelo">{{ $e['modelo'] ?: '—' }}</div>
            <div class="barcode"><svg id="bar-{{ $i }}"></svg></div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  <script>
    window.etiquetas = @json($etiquetas);
  </script>
</body>

</html>
