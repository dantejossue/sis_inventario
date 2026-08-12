<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoTecnico;
use App\Models\AuditoriaCambio;
use App\Models\Colaborador;
use App\Models\Modelo;
use App\Models\Ubicacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Importación masiva de activos desde una plantilla Excel: descarga de la
 * plantilla (con catálogo de referencia) y procesamiento del archivo subido.
 * Cada fila se valida y guarda de forma independiente: una fila inválida se
 * reporta pero no bloquea a las demás.
 */
class ActivoImportController extends Controller
{
    private const COLUMNAS = [
        'CODIGO_PATRIMONIAL', 'CODIGO_INTERNO', 'NUMERO_SERIE', 'MARCA', 'MODELO', 'CONDICION',
        'RESPONSABLE_DNI', 'UBICACION_CODIGO',
        'DESCRIPCION', 'FECHA_ADQUISICION', 'FECHA_ASIGNACION', 'VALOR_COMPRA', 'PROVEEDOR',
        'GARANTIA_INICIO', 'GARANTIA_FIN', 'OBSERVACIONES',
        'CODIGO_SIGA', 'NUMERO_PECOSA', 'NUMERO_ORDEN_COMPRA', 'FECHA_ALTA_SIGA',
        'PROCESADOR', 'MEMORIA_RAM', 'ALMACENAMIENTO', 'TIPO_ALMACENAMIENTO', 'SISTEMA_OPERATIVO',
        'DIRECCION_MAC', 'DIRECCION_IP', 'NOMBRE_EQUIPO', 'DOMINIO', 'LICENCIA_OFFICE', 'ANTIVIRUS',
        'ACCESORIOS', 'OBSERVACIONES_TECNICAS',
    ];

    private const COLUMNAS_OBLIGATORIAS = [
        'CODIGO_PATRIMONIAL', 'CODIGO_INTERNO', 'NUMERO_SERIE', 'MARCA', 'MODELO', 'CONDICION',
        'RESPONSABLE_DNI', 'UBICACION_CODIGO',
    ];

    private const COLUMNAS_FECHA = [
        'FECHA_ADQUISICION', 'FECHA_ASIGNACION', 'GARANTIA_INICIO', 'GARANTIA_FIN', 'FECHA_ALTA_SIGA',
    ];

    private const TIPOS_ALMACENAMIENTO = ['HDD', 'SSD', 'NVME', 'EMMC', 'MIXTO', 'OTRO'];

    private const MAX_FILAS_VALIDACION = 500;

    // ──────────────────────────────────────────────────────────────────
    //  Descarga de la plantilla
    // ──────────────────────────────────────────────────────────────────

    public function plantilla()
    {
        $spreadsheet = new Spreadsheet();

        $this->hojaPlantilla($spreadsheet->getActiveSheet());
        $this->hojaInstrucciones($spreadsheet->createSheet());
        $spreadsheet->setActiveSheetIndex(0);

        $nombre = 'plantilla_importacion_activos_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            IOFactory::createWriter($spreadsheet, 'Xlsx')->save('php://output');
        }, $nombre, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function hojaPlantilla(Worksheet $sheet): void
    {
        $sheet->setTitle('Plantilla');
        $sheet->fromArray(self::COLUMNAS, null, 'A1');
        $sheet->fromArray($this->filaEjemplo(), null, 'A2');
        $sheet->freezePane('A2');

        $colLetra = fn (string $nombre) => Coordinate::stringFromColumnIndex(array_search($nombre, self::COLUMNAS, true) + 1);
        $ultimaCol = Coordinate::stringFromColumnIndex(count(self::COLUMNAS));

        $sheet->getStyle("A1:{$ultimaCol}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$ultimaCol}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E2F3');
        $sheet->getStyle("A2:{$ultimaCol}2")->getFont()->setItalic(true);

        foreach (self::COLUMNAS_OBLIGATORIAS as $col) {
            $sheet->getStyle($colLetra($col) . '1')->getFont()->getColor()->setRGB('9C0006');
        }

        for ($i = 1; $i <= count(self::COLUMNAS); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(22);
        }

        $this->agregarValidacionLista($sheet, $colLetra('CONDICION'), Activo::CONDICIONES);
        $this->agregarValidacionLista($sheet, $colLetra('TIPO_ALMACENAMIENTO'), self::TIPOS_ALMACENAMIENTO);
    }

    private function agregarValidacionLista(Worksheet $sheet, string $columna, array $valores): void
    {
        $formula = '"' . implode(',', $valores) . '"';

        for ($fila = 2; $fila <= self::MAX_FILAS_VALIDACION; $fila++) {
            $dv = $sheet->getCell("{$columna}{$fila}")->getDataValidation();
            $dv->setType(DataValidation::TYPE_LIST);
            $dv->setErrorStyle(DataValidation::STYLE_STOP);
            $dv->setAllowBlank(true);
            $dv->setShowDropDown(true);
            $dv->setShowErrorMessage(true);
            $dv->setErrorTitle('Valor no válido');
            $dv->setError('Selecciona uno de los valores de la lista.');
            $dv->setFormula1($formula);
        }
    }

    /** Fila 2 de ejemplo, con datos reales del catálogo cuando existen. */
    private function filaEjemplo(): array
    {
        $modelo = Modelo::with('marca')->where('estado', 'ACTIVO')->orderBy('id_modelo')->first();
        $colaborador = Colaborador::where('estado', 'ACTIVO')->orderBy('id_colaborador')->first();
        $ubicacion = Ubicacion::where('estado', 'ACTIVO')->orderBy('id_ubicacion')->get()
            ->first(fn (Ubicacion $u) => $this->esNodoHoja($u->id_ubicacion) && $u->codigo);

        $valores = [
            'CODIGO_PATRIMONIAL' => '1234-2026',
            'CODIGO_INTERNO' => 'TI-EJEMPLO-001',
            'NUMERO_SERIE' => 'SN-EJEMPLO-001',
            'MARCA' => $modelo?->marca?->nombre ?? 'HP',
            'MODELO' => $modelo?->nombre ?? 'ProBook 450 G8',
            'CONDICION' => 'BUENO',
            'RESPONSABLE_DNI' => $colaborador?->nro_documento ?? '00000000',
            'UBICACION_CODIGO' => $ubicacion?->codigo ?? '(ver hoja Instrucciones)',
            'DESCRIPCION' => 'Fila de ejemplo: bórrala antes de importar',
            'FECHA_ADQUISICION' => now()->format('d/m/Y'),
            'FECHA_ASIGNACION' => '',
            'VALOR_COMPRA' => '2500.00',
            'PROVEEDOR' => '',
            'GARANTIA_INICIO' => '',
            'GARANTIA_FIN' => '',
            'OBSERVACIONES' => '',
            'CODIGO_SIGA' => '',
            'NUMERO_PECOSA' => '',
            'NUMERO_ORDEN_COMPRA' => '',
            'FECHA_ALTA_SIGA' => '',
            'PROCESADOR' => '',
            'MEMORIA_RAM' => '',
            'ALMACENAMIENTO' => '',
            'TIPO_ALMACENAMIENTO' => '',
            'SISTEMA_OPERATIVO' => '',
            'DIRECCION_MAC' => '',
            'DIRECCION_IP' => '',
            'NOMBRE_EQUIPO' => '',
            'DOMINIO' => '',
            'LICENCIA_OFFICE' => '',
            'ANTIVIRUS' => '',
            'ACCESORIOS' => '',
            'OBSERVACIONES_TECNICAS' => '',
        ];

        return array_map(fn ($c) => $valores[$c] ?? '', self::COLUMNAS);
    }

    private function hojaInstrucciones(Worksheet $sheet): void
    {
        $sheet->setTitle('Instrucciones');
        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(32);
        $sheet->getColumnDimension('C')->setWidth(28);

        $fila = 1;
        $sheet->setCellValue("A{$fila}", 'Instrucciones para completar la plantilla de importación de activos');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(13);
        $fila += 2;

        $sheet->setCellValue("A{$fila}", 'Columnas obligatorias: ' . implode(', ', self::COLUMNAS_OBLIGATORIAS));
        $fila += 1;
        $sheet->setCellValue("A{$fila}", 'Formato de fechas: DD/MM/AAAA (ejemplo: 15/03/2026).');
        $fila += 1;
        $sheet->setCellValue("A{$fila}", 'MARCA y MODELO deben coincidir exactamente con el catálogo (lista más abajo).');
        $fila += 1;
        $sheet->setCellValue("A{$fila}", 'RESPONSABLE_DNI es el número de documento de un colaborador activo del sistema.');
        $fila += 1;
        $sheet->setCellValue("A{$fila}", 'UBICACION_CODIGO debe ser el código de un ambiente final (sin sub-ubicaciones), listado más abajo.');
        $fila += 1;
        $sheet->setCellValue("A{$fila}", 'Los campos de Ficha Técnica solo se guardan si la categoría del modelo la requiere (equipos de cómputo).');
        $fila += 2;

        $sheet->setCellValue("A{$fila}", 'CONDICION — valores válidos');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
        $fila += 1;
        foreach (Activo::CONDICIONES as $c) {
            $sheet->setCellValue("A{$fila}", $c);
            $fila += 1;
        }
        $fila += 1;

        $sheet->setCellValue("A{$fila}", 'TIPO_ALMACENAMIENTO — valores válidos');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
        $fila += 1;
        foreach (self::TIPOS_ALMACENAMIENTO as $t) {
            $sheet->setCellValue("A{$fila}", $t);
            $fila += 1;
        }
        $fila += 1;

        $sheet->setCellValue("A{$fila}", 'MARCA / MODELO disponibles');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
        $fila += 1;
        $sheet->fromArray(['MARCA', 'MODELO'], null, "A{$fila}");
        $sheet->getStyle("A{$fila}:B{$fila}")->getFont()->setBold(true);
        $fila += 1;
        foreach (Modelo::with('marca')->where('estado', 'ACTIVO')->orderBy('id_marca')->orderBy('nombre')->get() as $m) {
            $sheet->setCellValue("A{$fila}", $m->marca->nombre);
            $sheet->setCellValue("B{$fila}", $m->nombre);
            $fila += 1;
        }
        $fila += 1;

        $sheet->setCellValue("A{$fila}", 'Ubicaciones disponibles (código — ruta completa — sede)');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
        $fila += 1;
        $sheet->fromArray(['CODIGO', 'RUTA', 'SEDE'], null, "A{$fila}");
        $sheet->getStyle("A{$fila}:C{$fila}")->getFont()->setBold(true);
        $fila += 1;

        $ubicacionesPorId = Ubicacion::get(['id_ubicacion', 'id_ubicacion_padre', 'nombre'])->keyBy('id_ubicacion');
        $hojasUbicacion = Ubicacion::with('sede')->where('estado', 'ACTIVO')
            ->orderBy('id_sede')->orderBy('nombre')->get()
            ->filter(fn (Ubicacion $u) => $this->esNodoHoja($u->id_ubicacion));

        foreach ($hojasUbicacion as $u) {
            $sheet->setCellValue("A{$fila}", $u->codigo ?: '(sin código: asígnale uno en el catálogo para poder usarla)');
            $sheet->setCellValue("B{$fila}", ActivoController::rutaUbicacion($u, $ubicacionesPorId));
            $sheet->setCellValue("C{$fila}", $u->sede?->nombre_sede);
            $fila += 1;
        }

        $fila += 1;
        $sheet->setCellValue("A{$fila}", 'Colaboradores disponibles (DNI — nombre)');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
        $fila += 1;
        $sheet->fromArray(['DNI', 'NOMBRE'], null, "A{$fila}");
        $sheet->getStyle("A{$fila}:B{$fila}")->getFont()->setBold(true);
        $fila += 1;
        foreach (Colaborador::where('estado', 'ACTIVO')->orderBy('per_apepat')->orderBy('per_nombre')->get() as $c) {
            $sheet->setCellValue("A{$fila}", $c->nro_documento);
            $sheet->setCellValue("B{$fila}", $c->nombre_completo);
            $fila += 1;
        }
    }

    // ──────────────────────────────────────────────────────────────────
    //  Procesamiento del archivo subido
    // ──────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'archivo.required' => 'Debes seleccionar un archivo Excel.',
            'archivo.mimes' => 'El archivo debe ser Excel (.xlsx o .xls).',
            'archivo.max' => 'El archivo no puede superar los 5 MB.',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('archivo')->getRealPath());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo leer el archivo. Verifica que sea un Excel válido y no esté dañado.',
            ], 422);
        }

        $sheet = $spreadsheet->getSheetByName('Plantilla') ?? $spreadsheet->getActiveSheet();
        // formatData=false: las fechas llegan como número serie de Excel, no como texto formateado.
        $filas = $sheet->toArray(null, true, false, false);

        $encabezados = array_map(fn ($h) => mb_strtoupper(trim((string) $h)), array_shift($filas) ?? []);
        $mapa = array_flip(array_filter($encabezados, fn ($h) => $h !== ''));

        $faltantes = array_diff(self::COLUMNAS_OBLIGATORIAS, array_keys($mapa));
        if ($faltantes) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan columnas obligatorias en el archivo: ' . implode(', ', $faltantes) . '. Descarga la plantilla nuevamente y no cambies los encabezados.',
            ], 422);
        }

        $creados = [];
        $errores = [];
        $vistos = ['patrimonial' => [], 'interno' => [], 'serie' => []];

        $numeroFila = 1; // fila 1 = encabezados
        foreach ($filas as $fila) {
            $numeroFila++;

            $obtener = fn (string $col) => isset($mapa[$col]) ? trim((string) ($fila[$mapa[$col]] ?? '')) : '';
            $datos = [];
            foreach (self::COLUMNAS as $col) {
                $datos[$col] = $obtener($col);
            }

            if (implode('', $datos) === '') {
                continue; // fila completamente vacía
            }

            [$campos, $error] = $this->validarYResolverFila($datos);

            if (! $error) {
                $cp = $campos['activo']['codigo_patrimonial'];
                $ci = $campos['activo']['codigo_interno'];
                $ns = $campos['activo']['numero_serie'];

                if (isset($vistos['patrimonial'][$cp])) {
                    $error = "Código patrimonial duplicado en el archivo (ya aparece en la fila {$vistos['patrimonial'][$cp]}).";
                } elseif (isset($vistos['interno'][$ci])) {
                    $error = "Código interno duplicado en el archivo (ya aparece en la fila {$vistos['interno'][$ci]}).";
                } elseif (isset($vistos['serie'][$ns])) {
                    $error = "Número de serie duplicado en el archivo (ya aparece en la fila {$vistos['serie'][$ns]}).";
                } elseif (Activo::where('codigo_patrimonial', $cp)->exists()) {
                    $error = 'Ya existe un activo registrado con ese código patrimonial.';
                } elseif (Activo::where('codigo_interno', $ci)->exists()) {
                    $error = 'Ya existe un activo registrado con ese código interno.';
                } elseif (Activo::where('numero_serie', $ns)->exists()) {
                    $error = 'Ya existe un activo registrado con ese número de serie.';
                }
            }

            if ($error) {
                $errores[] = ['fila' => $numeroFila, 'motivo' => $error];
                continue;
            }

            $vistos['patrimonial'][$campos['activo']['codigo_patrimonial']] = $numeroFila;
            $vistos['interno'][$campos['activo']['codigo_interno']] = $numeroFila;
            $vistos['serie'][$campos['activo']['numero_serie']] = $numeroFila;

            $activo = DB::transaction(function () use ($campos) {
                $activo = Activo::create($campos['activo']);

                if ($campos['tecnico'] !== null) {
                    ActivoTecnico::updateOrCreate(['id_activo' => $activo->id_activo], $campos['tecnico']);
                }

                AuditoriaCambio::registrar('ACTIVO', $activo->id_activo, 'CREAR', null, [
                    'codigo_interno' => $activo->codigo_interno,
                    'situacion' => $activo->situacion_actual,
                    'condicion' => $activo->condicion_actual,
                    'origen' => 'EXCEL',
                ]);

                return $activo;
            });

            $creados[] = ['fila' => $numeroFila, 'codigo_interno' => $activo->codigo_interno];
        }

        return response()->json([
            'success' => true,
            'resumen' => [
                'total' => count($creados) + count($errores),
                'creados' => count($creados),
                'con_errores' => count($errores),
            ],
            'detalle_creados' => $creados,
            'errores' => $errores,
        ]);
    }

    /**
     * Valida y resuelve una fila normalizada del Excel a los arrays listos
     * para Activo::create()/ActivoTecnico. Devuelve [campos, null] si es
     * válida o [null, mensaje] si no.
     */
    private function validarYResolverFila(array $d): array
    {
        foreach (self::COLUMNAS_OBLIGATORIAS as $col) {
            if ($d[$col] === '') {
                return [null, "Falta el campo obligatorio {$col}."];
            }
        }

        $condicion = mb_strtoupper($d['CONDICION']);
        if (! in_array($condicion, Activo::CONDICIONES, true)) {
            return [null, "CONDICION '{$d['CONDICION']}' no es válida. Usa: " . implode(', ', Activo::CONDICIONES) . '.'];
        }

        if ($d['TIPO_ALMACENAMIENTO'] !== '' && ! in_array(mb_strtoupper($d['TIPO_ALMACENAMIENTO']), self::TIPOS_ALMACENAMIENTO, true)) {
            return [null, "TIPO_ALMACENAMIENTO '{$d['TIPO_ALMACENAMIENTO']}' no es válido. Usa: " . implode(', ', self::TIPOS_ALMACENAMIENTO) . '.'];
        }

        // MARCA + MODELO: 0 → no existe, >1 → ambiguo (mismo nombre en dos categorías); nunca asumir el primero.
        $modelos = Modelo::with('categoriaActivo')
            ->where('estado', 'ACTIVO')
            ->whereRaw('UPPER(TRIM(nombre)) = ?', [mb_strtoupper(trim($d['MODELO']))])
            ->whereHas('marca', fn ($q) => $q->whereRaw('UPPER(TRIM(nombre)) = ?', [mb_strtoupper(trim($d['MARCA']))]))
            ->get();

        if ($modelos->isEmpty()) {
            return [null, "No se encontró el modelo '{$d['MARCA']} / {$d['MODELO']}' en el catálogo. Revisa la hoja Instrucciones."];
        }
        if ($modelos->count() > 1) {
            return [null, "MARCA/MODELO '{$d['MARCA']} / {$d['MODELO']}' es ambiguo ({$modelos->count()} coincidencias en el catálogo)."];
        }
        $modelo = $modelos->first();

        $responsable = Colaborador::where('nro_documento', $d['RESPONSABLE_DNI'])->where('estado', 'ACTIVO')->first();
        if (! $responsable) {
            return [null, "No se encontró un colaborador activo con DNI '{$d['RESPONSABLE_DNI']}'."];
        }

        // UBICACION_CODIGO: mismo criterio 0/1/>1 que MARCA+MODELO (el código no es único en el catálogo).
        $ubicaciones = Ubicacion::where('estado', 'ACTIVO')
            ->whereRaw('UPPER(TRIM(codigo)) = ?', [mb_strtoupper(trim($d['UBICACION_CODIGO']))])
            ->get();
        if ($ubicaciones->isEmpty()) {
            return [null, "No se encontró la ubicación con código '{$d['UBICACION_CODIGO']}'."];
        }
        if ($ubicaciones->count() > 1) {
            return [null, "El código de ubicación '{$d['UBICACION_CODIGO']}' es ambiguo ({$ubicaciones->count()} coincidencias); hay ubicaciones con el mismo código en el catálogo."];
        }
        $ubicacion = $ubicaciones->first();
        if (! $this->esNodoHoja($ubicacion->id_ubicacion)) {
            return [null, "La ubicación '{$ubicacion->nombre}' no es un ambiente final (tiene sub-ubicaciones); usa el código del último nivel."];
        }

        $fechas = [];
        foreach (self::COLUMNAS_FECHA as $col) {
            [$valor, $errorFecha] = $this->parsearFecha($d[$col]);
            if ($errorFecha) {
                return [null, "{$col}: {$errorFecha}"];
            }
            $fechas[$col] = $valor;
        }
        if ($fechas['GARANTIA_INICIO'] && $fechas['GARANTIA_FIN'] && $fechas['GARANTIA_FIN']->lt($fechas['GARANTIA_INICIO'])) {
            return [null, 'GARANTIA_FIN no puede ser anterior a GARANTIA_INICIO.'];
        }

        $valorCompra = null;
        if ($d['VALOR_COMPRA'] !== '') {
            $normalizado = str_replace(',', '.', $d['VALOR_COMPRA']);
            if (! is_numeric($normalizado) || (float) $normalizado < 0) {
                return [null, "VALOR_COMPRA '{$d['VALOR_COMPRA']}' no es un número válido."];
            }
            $valorCompra = (float) $normalizado;
        }

        $situacion = $responsable ? 'EN_USO' : 'DISPONIBLE';

        $campos = [
            'activo' => [
                'id_modelo' => $modelo->id_modelo,
                'id_categoria' => $modelo->id_categoria,
                'condicion_actual' => $condicion,
                'situacion_actual' => $situacion,
                'id_responsable_actual' => $responsable->id_colaborador,
                'id_ubicacion_actual' => $ubicacion->id_ubicacion,
                'codigo_interno' => mb_strtoupper(trim($d['CODIGO_INTERNO'])),
                'codigo_patrimonial' => mb_strtoupper(trim($d['CODIGO_PATRIMONIAL'])),
                'numero_serie' => trim($d['NUMERO_SERIE']),
                'descripcion' => $d['DESCRIPCION'] !== '' ? $d['DESCRIPCION'] : null,
                'fecha_adquisicion' => $fechas['FECHA_ADQUISICION'],
                'fecha_asignacion' => $fechas['FECHA_ASIGNACION'],
                'valor_compra' => $valorCompra,
                'proveedor' => $d['PROVEEDOR'] !== '' ? mb_strtoupper(trim($d['PROVEEDOR'])) : null,
                'garantia_inicio' => $fechas['GARANTIA_INICIO'],
                'garantia_fin' => $fechas['GARANTIA_FIN'],
                'observaciones' => $d['OBSERVACIONES'] !== '' ? $d['OBSERVACIONES'] : null,
                'qr_token' => (string) Str::uuid(),
                'origen_registro' => 'EXCEL',
                'codigo_siga' => $d['CODIGO_SIGA'] !== '' ? mb_strtoupper(trim($d['CODIGO_SIGA'])) : null,
                'numero_pecosa' => $d['NUMERO_PECOSA'] !== '' ? mb_strtoupper(trim($d['NUMERO_PECOSA'])) : null,
                'numero_orden_compra' => $d['NUMERO_ORDEN_COMPRA'] !== '' ? mb_strtoupper(trim($d['NUMERO_ORDEN_COMPRA'])) : null,
                'fecha_alta_siga' => $fechas['FECHA_ALTA_SIGA'],
                'estado_siga' => 'NO_APLICA',
                'creado_por' => Auth::id(),
            ],
            'tecnico' => null,
        ];

        if ($modelo->categoriaActivo?->requiere_ficha_tecnica) {
            $campos['tecnico'] = [
                'procesador' => $d['PROCESADOR'] ?: null,
                'memoria_ram' => $d['MEMORIA_RAM'] ?: null,
                'almacenamiento' => $d['ALMACENAMIENTO'] ?: null,
                'tipo_almacenamiento' => $d['TIPO_ALMACENAMIENTO'] ? mb_strtoupper($d['TIPO_ALMACENAMIENTO']) : null,
                'sistema_operativo' => $d['SISTEMA_OPERATIVO'] ?: null,
                'direccion_mac' => $d['DIRECCION_MAC'] ?: null,
                'direccion_ip' => $d['DIRECCION_IP'] ?: null,
                'nombre_equipo' => $d['NOMBRE_EQUIPO'] ?: null,
                'dominio' => $d['DOMINIO'] ?: null,
                'licencia_office' => $d['LICENCIA_OFFICE'] ?: null,
                'antivirus' => $d['ANTIVIRUS'] ?: null,
                'accesorios' => $d['ACCESORIOS'] ?: null,
                'observaciones_tecnicas' => $d['OBSERVACIONES_TECNICAS'] ?: null,
            ];
        }

        return [$campos, null];
    }

    /**
     * Interpreta una celda de fecha: si Excel la guardó como fecha real llega
     * como número serie; si es texto se exige DD/MM/AAAA explícito (PHP trata
     * "/" como formato m/d/Y de EE.UU. y daría fechas erróneas con Carbon::parse).
     *
     * @return array{0: ?Carbon, 1: ?string}
     */
    private function parsearFecha($valor): array
    {
        $valor = is_string($valor) ? trim($valor) : $valor;
        if ($valor === '' || $valor === null) {
            return [null, null];
        }

        if (is_numeric($valor)) {
            try {
                return [Carbon::instance(ExcelDate::excelToDateTimeObject((float) $valor))->startOfDay(), null];
            } catch (\Throwable $e) {
                return [null, "'{$valor}' no es una fecha válida."];
            }
        }

        try {
            $fecha = Carbon::createFromFormat('d/m/Y', (string) $valor);
        } catch (\Throwable $e) {
            $fecha = false;
        }

        if (! $fecha) {
            return [null, "'{$valor}' no es una fecha válida. Usa el formato DD/MM/AAAA."];
        }

        return [$fecha->startOfDay(), null];
    }

    /** Nodo hoja = ninguna ubicación ACTIVA lo tiene como padre (espejo de ActivoController::reglaUbicacionHoja). */
    private function esNodoHoja(int $idUbicacion): bool
    {
        return ! Ubicacion::where('id_ubicacion_padre', $idUbicacion)->where('estado', 'ACTIVO')->exists();
    }
}
