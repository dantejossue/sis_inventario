<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoPatrimonialSiga;
use App\Models\EstadoActivo;
use App\Models\ImportacionSiga;
use App\Models\ImportacionSigaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportacionSigaController extends Controller
{
    /**
     * Columnas de la plantilla de importación SIGA (encabezado de la fila 1).
     * Solo CODIGO_PATRIMONIAL es obligatorio.
     */
    private const COLUMNAS = [
        'CODIGO_PATRIMONIAL', 'SBN', 'DENOMINACION', 'MARCA', 'MODELO', 'NUMERO_SERIE',
        'SEDE', 'UBICACION', 'CENTRO_COSTOS', 'UNIDAD_EJECUTORA', 'PROVEEDOR',
        'FECHA_COMPRA', 'VALOR_ADQUISICION', 'CONDICION', 'ESTADO_CONSERVACION', 'OBSERVACIONES',
    ];

    public function index()
    {
        $importaciones = ImportacionSiga::with('importadoPor')
            ->orderByDesc('id_importacion')
            ->get()
            ->map(fn($i) => [
                'id_importacion'       => $i->id_importacion,
                'nombre_archivo'       => $i->nombre_archivo,
                'tipo'                 => $i->tipo_importacion,
                'total'                => $i->total_registros,
                'correctos'            => $i->registros_correctos,
                'observados'           => $i->registros_observados,
                'estado'               => $i->estado,
                'importado_por'        => $i->importadoPor?->nombre_usuario,
                'fecha'                => $i->creado_en?->format('Y-m-d H:i'),
            ]);

        return view('content.importaciones.index', compact('importaciones'));
    }

    public function show(int $id)
    {
        $importacion = ImportacionSiga::with('importadoPor')->findOrFail($id);
        $detalles = ImportacionSigaDetalle::where('id_importacion', $id)
            ->orderBy('fila_excel')
            ->get()
            ->map(fn($d) => [
                'fila'               => $d->fila_excel,
                'codigo_patrimonial' => $d->codigo_patrimonial,
                'numero_serie'       => $d->numero_serie,
                'denominacion'       => $d->denominacion,
                'estado'             => $d->estado,
                'mensaje'            => $d->mensaje,
            ]);

        return view('content.importaciones.show', compact('importacion', 'detalles'));
    }

    /** Descarga la plantilla .xlsx con los encabezados y una fila de ejemplo. */
    public function plantilla()
    {
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('SIGA');

        foreach (self::COLUMNAS as $i => $col) {
            $letra = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$letra}1", $col);
            $sheet->getStyle("{$letra}1")->getFont()->setBold(true);
            $sheet->getStyle("{$letra}1")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E1F2');
            $sheet->getColumnDimension($letra)->setAutoSize(true);
        }

        // Fila de ejemplo
        $ejemplo = [
            'INV-2024-000123', '74126345', 'COMPUTADORA PORTATIL', 'HP', 'ELITEBOOK 840', '5CD1234ABC',
            'SEDE CENTRAL', 'PABELLON A - PISO 2 - OFICINA OTI', 'OTI', 'UNDC', 'HP PERU SAC',
            '2024-03-15', '3500.00', 'BUENO', 'BUENO', 'Equipo asignado a la OTI',
        ];
        foreach ($ejemplo as $i => $val) {
            $letra = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValueExplicit("{$letra}2", $val, DataType::TYPE_STRING);
        }

        return response()->streamDownload(function () use ($ss) {
            (new Xlsx($ss))->save('php://output');
        }, 'plantilla_importacion_siga.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.mimes'    => 'El archivo debe ser Excel (.xlsx o .xls).',
            'archivo.max'      => 'El archivo no puede superar los 10 MB.',
        ]);

        try {
            $ss = IOFactory::load($request->file('archivo')->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo leer el archivo Excel: ' . $e->getMessage());
        }

        $rows = $ss->getActiveSheet()->toArray(null, true, true, false);
        if (count($rows) < 2) {
            return back()->with('error', 'El archivo no tiene filas de datos.');
        }

        // Mapa encabezado → índice de columna (tolerante a acentos/espacios).
        $headers = array_map(fn($h) => $this->norm((string) $h), $rows[0]);
        $idx = [];
        foreach (self::COLUMNAS as $col) {
            $idx[$col] = array_search($col, $headers, true);
        }
        if ($idx['CODIGO_PATRIMONIAL'] === false) {
            return back()->with('error', 'Falta la columna obligatoria CODIGO_PATRIMONIAL. Usa la plantilla.');
        }

        // Catálogos resueltos una sola vez.
        $situAlmacen = (int) EstadoActivo::where('tipo', 'SITUACION')->where('codigo', 'EN_ALMACEN')->value('id_estado_activo');
        $condMap = EstadoActivo::where('tipo', 'CONDICION')->pluck('id_estado_activo', 'codigo');
        $condDefault = (int) ($condMap['BUENO'] ?? $condMap->first());

        $imp = ImportacionSiga::create([
            'nombre_archivo'       => $request->file('archivo')->getClientOriginalName(),
            'tipo_importacion'     => 'SIGA',
            'total_registros'      => 0,
            'registros_correctos'  => 0,
            'registros_observados' => 0,
            'estado'               => 'PROCESANDO',
            'importado_por'        => Auth::id(),
            'creado_en'            => now(),
        ]);

        $tot = 0; $cor = 0; $obs = 0;
        $vistos = [];

        for ($r = 1; $r < count($rows); $r++) {
            $row = $rows[$r];
            // Saltar filas totalmente vacías.
            if (count(array_filter($row, fn($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $tot++;
            $fila = $r + 1;

            $get = function (string $key) use ($idx, $row) {
                if ($idx[$key] === false || !isset($row[$idx[$key]])) return null;
                $v = trim((string) $row[$idx[$key]]);
                return $v === '' ? null : $v;
            };
            $datosRaw = [];
            foreach (self::COLUMNAS as $k) $datosRaw[$k] = $get($k);

            $cod = $get('CODIGO_PATRIMONIAL');
            if (!$cod) {
                $this->detalle($imp, $fila, null, null, null, 'ERROR', 'Sin código patrimonial.', $datosRaw);
                continue;
            }
            $codU = strtoupper($cod);
            if (isset($vistos[$codU])) {
                $this->detalle($imp, $fila, $cod, $get('NUMERO_SERIE'), $get('DENOMINACION'), 'DUPLICADO', 'Duplicado dentro del archivo.', $datosRaw);
                continue;
            }
            if (Activo::withTrashed()->where('codigo_patrimonial', $codU)->exists()) {
                $this->detalle($imp, $fila, $cod, $get('NUMERO_SERIE'), $get('DENOMINACION'), 'DUPLICADO', 'El código patrimonial ya existe en el sistema.', $datosRaw);
                continue;
            }
            $vistos[$codU] = true;

            try {
                DB::transaction(function () use ($get, $cod, $codU, $datosRaw, $fila, $imp, $situAlmacen, $condMap, $condDefault, &$cor, &$obs) {
                    $denom = $get('DENOMINACION');
                    $marcaModelo = trim(implode(' ', array_filter(['Marca: ' . ($get('MARCA') ?? '—'), 'Modelo: ' . ($get('MODELO') ?? '—')])));
                    $obsTxt = trim($marcaModelo . ($get('OBSERVACIONES') ? ' | ' . $get('OBSERVACIONES') : ''));

                    $activo = Activo::create([
                        'id_modelo'           => null,
                        'id_categoria'        => null,
                        'id_condicion_actual' => $this->condicionId($get('CONDICION') ?: $get('ESTADO_CONSERVACION'), $condMap, $condDefault),
                        'id_situacion_actual' => $situAlmacen,
                        'codigo_interno'      => null,
                        'codigo_patrimonial'  => $codU,
                        'numero_serie'        => $get('NUMERO_SERIE'),
                        'descripcion'         => $denom,
                        'proveedor'           => $get('PROVEEDOR'),
                        'valor_compra'        => $this->num($get('VALOR_ADQUISICION')),
                        'fecha_adquisicion'   => $this->fecha($get('FECHA_COMPRA')),
                        'observaciones'       => $obsTxt ?: null,
                        'origen_registro'     => 'IMPORTADO_SIGA',
                        'estado_validacion'   => 'PENDIENTE_VALIDACION',
                        'estado_siga'         => 'REGISTRADO',
                        'id_importacion'      => $imp->id_importacion,
                        'qr_token'            => (string) Str::uuid(),
                        'creado_por'          => Auth::id(),
                    ]);

                    $estadoDet = $denom ? 'CORRECTO' : 'OBSERVADO';
                    $det = $this->detalle(
                        $imp, $fila, $cod, $get('NUMERO_SERIE'), $denom, $estadoDet,
                        $denom ? null : 'Sin denominación (revisar).', $datosRaw, $activo->id_activo
                    );

                    ActivoPatrimonialSiga::create([
                        'id_activo'                => $activo->id_activo,
                        'id_importacion'           => $imp->id_importacion,
                        'id_importacion_detalle'   => $det->id_importacion_detalle,
                        'sbn'                      => $get('SBN'),
                        'descripcion_siga'         => $denom,
                        'sede_siga'                => $get('SEDE'),
                        'sede_ubicacion_siga'      => $get('UBICACION'),
                        'centro_costos'            => $get('CENTRO_COSTOS'),
                        'unidad_ejecutora'         => $get('UNIDAD_EJECUTORA'),
                        'proveedor_siga'           => $get('PROVEEDOR'),
                        'fecha_compra'             => $this->fecha($get('FECHA_COMPRA')),
                        'valor_adquisicion'        => $this->num($get('VALOR_ADQUISICION')),
                        'condicion_siga'           => $get('CONDICION'),
                        'estado_conservacion_siga' => $get('ESTADO_CONSERVACION'),
                        'observaciones_siga'       => $get('OBSERVACIONES'),
                        'fecha_importacion'        => now(),
                    ]);

                    $estadoDet === 'CORRECTO' ? $cor++ : $obs++;
                });
            } catch (\Throwable $e) {
                $this->detalle($imp, $fila, $cod, $get('NUMERO_SERIE'), $get('DENOMINACION'), 'ERROR', 'Error al guardar: ' . $e->getMessage(), $datosRaw);
            }
        }

        $estadoFinal = ($cor + $obs) === 0
            ? 'ERROR'
            : (($tot - $cor) > 0 ? 'COMPLETADO_CON_OBSERVACIONES' : 'COMPLETADO');

        $imp->update([
            'total_registros'      => $tot,
            'registros_correctos'  => $cor,
            'registros_observados' => $tot - $cor,
            'estado'               => $estadoFinal,
        ]);

        return redirect()->route('importaciones.show', $imp->id_importacion)
            ->with('success', "Importación finalizada: {$cor} correcto(s), " . ($tot - $cor) . ' observado(s)/error(es) de ' . $tot . '.');
    }

    // ──────────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────────

    private function detalle($imp, int $fila, ?string $cod, ?string $serie, ?string $denom, string $estado, ?string $msg, array $raw, ?int $idActivo = null): ImportacionSigaDetalle
    {
        return ImportacionSigaDetalle::create([
            'id_importacion'     => $imp->id_importacion,
            'id_activo'          => $idActivo,
            'fila_excel'         => $fila,
            'codigo_patrimonial' => $cod,
            'numero_serie'       => $serie,
            'denominacion'       => $denom,
            'estado'             => $estado,
            'mensaje'            => $msg,
            'datos_raw'          => $raw,
            'creado_en'          => now(),
        ]);
    }

    /** Normaliza un encabezado: MAYÚSCULAS, sin acentos, separadores → '_'. */
    private function norm(string $h): string
    {
        $h = Str::ascii(trim($h));
        $h = strtoupper($h);
        $h = preg_replace('/[^A-Z0-9]+/', '_', $h);
        return trim($h, '_');
    }

    /** Mapea un texto de condición SIGA a un id de estado_activo (CONDICION). */
    private function condicionId(?string $txt, $condMap, int $default): int
    {
        if (!$txt) return $default;
        $t = strtoupper(Str::ascii(trim($txt)));
        $alias = ['B' => 'BUENO', 'R' => 'REGULAR', 'M' => 'MALO'];
        $t = $alias[$t] ?? $t;
        return (int) ($condMap[$t] ?? $default);
    }

    /** Convierte un valor monetario textual a decimal o null. */
    private function num(?string $v): ?float
    {
        if ($v === null) return null;
        $v = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', $v));
        return is_numeric($v) ? (float) $v : null;
    }

    /** Convierte una fecha (serial Excel o texto) a Y-m-d o null. */
    private function fecha(?string $v): ?string
    {
        if (!$v) return null;
        if (is_numeric($v) && (float) $v > 25569) { // serial Excel (post-1970)
            try {
                return ExcelDate::excelToDateTimeObject((float) $v)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }
        $ts = strtotime($v);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}
