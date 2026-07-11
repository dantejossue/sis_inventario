<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoTecnico;
use App\Models\BajaActivo;
use App\Models\CategoriaActivo;
use App\Models\Colaborador;
use App\Models\DetalleMovimientoActivo;
use App\Models\EstadoActivo;
use App\Models\Mantenimiento;
use App\Models\Modelo;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ActivoController extends Controller
{
    public function index()
    {
        $ubicacionesPorId = Ubicacion::get(['id_ubicacion', 'id_ubicacion_padre', 'nombre'])
            ->keyBy('id_ubicacion');

        $activos = Activo::with('modelo.marca', 'modelo.categoriaActivo', 'condicion', 'situacion', 'ubicacion.sede', 'responsable', 'categoria', 'activoTecnico')
            ->get()
            ->map(fn($a) => static::formatActivo($a, $ubicacionesPorId))
            ->values();

        $colaboradores = Colaborador::where('estado', 'ACTIVO')
            ->orderBy('per_apepat')
            ->get(['id_colaborador', 'per_nombre', 'per_apepat', 'per_apemat', 'cargo']);

        $ubicaciones = Ubicacion::with('sede:id_sede,nombre_sede')
            ->where('estado', 'ACTIVO')
            ->orderBy('id_sede')
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get(['id_ubicacion', 'id_sede', 'nombre', 'tipo', 'codigo']);

        return view('content.activos.index', compact('activos', 'colaboradores', 'ubicaciones'));
    }

    private function catalogos(): array
    {
        return [
            'modelos' => Modelo::with('marca', 'categoriaActivo')
                ->where('estado', 'ACTIVO')
                ->orderBy('nombre')
                ->get()
                ->map(fn($m) => [
                    'id_modelo'        => $m->id_modelo,
                    'nombre'           => $m->nombre,
                    'marca_nombre'     => $m->marca->nombre,
                    'categoria_nombre' => $m->categoriaActivo?->nombre ?? null,
                    'id_categoria'     => $m->id_categoria,
                    'requiere_ficha'   => (bool) ($m->categoriaActivo?->requiere_ficha_tecnica),
                ]),
            'condiciones' => EstadoActivo::where('tipo', 'CONDICION')
                ->where('estado', 'ACTIVO')
                ->get(['id_estado_activo', 'nombre']),
            'situaciones' => EstadoActivo::where('tipo', 'SITUACION')
                ->where('estado', 'ACTIVO')
                ->get(['id_estado_activo', 'nombre']),
            'colaboradores' => Colaborador::where('estado', 'ACTIVO')
                ->orderBy('per_apepat')
                ->get(),
            'ubicaciones' => Ubicacion::with('sede')
                ->where('estado', 'ACTIVO')
                ->orderBy('id_sede')
                ->orderBy('tipo')
                ->orderBy('nombre')
                ->get(),
        ];
    }

    public function create()
    {
        [
            'modelos' => $modelos,
            'condiciones' => $condiciones,
            'situaciones' => $situaciones,
            'colaboradores' => $colaboradores,
            'ubicaciones' => $ubicaciones
        ] = $this->catalogos();

        return view('content.activos.create', compact('modelos', 'condiciones', 'situaciones', 'colaboradores', 'ubicaciones') + ['activo' => null]);
    }

    public function edit(int $id)
    {
        $activo = Activo::with('activoTecnico')->findOrFail($id);

        [
            'modelos' => $modelos,
            'condiciones' => $condiciones,
            'situaciones' => $situaciones,
            'colaboradores' => $colaboradores,
            'ubicaciones' => $ubicaciones
        ] = $this->catalogos();

        return view('content.activos.edit', compact('activo', 'modelos', 'condiciones', 'situaciones', 'colaboradores', 'ubicaciones'));
    }

    /**
     * Ficha completa del activo: datos generales, técnicos, SIGA,
     * movimientos, documentos y trazabilidad derivada.
     */
    public function show(int $id)
    {
        $activo = Activo::with([
            'modelo.marca', 'modelo.categoriaActivo', 'categoria',
            'condicion', 'situacion',
            'responsable.sedeDependencia.dependencia', 'responsable.sedeDependencia.sede',
            'ubicacion.sede', 'activoTecnico',
            'patrimonialSiga', 'importacionSiga',
            'creadoPor.colaborador', 'actualizadoPor.colaborador',
            'documentos.subidoPor.colaborador',
        ])->findOrFail($id);

        // Garantiza que el activo tenga token QR para la etiqueta de la ficha.
        $activo->ensureQrToken();

        $ubicacionesPorId = Ubicacion::get(['id_ubicacion', 'id_ubicacion_padre', 'nombre'])
            ->keyBy('id_ubicacion');
        $rutaUbicacion = static::rutaUbicacion($activo->ubicacion, $ubicacionesPorId);

        // Historial de movimientos del activo (más reciente primero).
        $movimientos = DetalleMovimientoActivo::with([
            'movimiento.registradoPor.colaborador',
            'responsableOrigen', 'responsableDestino',
            'ubicacionOrigen', 'ubicacionDestino',
        ])
            ->where('id_activo', $id)
            ->get()
            ->sortByDesc(fn($d) => $d->movimiento?->fecha_registro)
            ->values();

        $mantenimientos = Mantenimiento::with('tecnicoResponsable')
            ->where('id_activo', $id)
            ->orderByDesc('id_mantenimiento')
            ->get();

        $bajas = BajaActivo::where('id_activo', $id)
            ->orderByDesc('id_baja')
            ->get();

        $eventos = $this->lineaDeTiempo($activo, $movimientos, $mantenimientos, $bajas);

        return view('content.activos.ver', compact(
            'activo', 'rutaUbicacion', 'movimientos', 'mantenimientos', 'bajas', 'eventos'
        ));
    }

    /**
     * Trazabilidad derivada de los hechos registrados del activo
     * (registro/importación, movimientos, documentos, última edición),
     * ordenada del más reciente al más antiguo.
     */
    private function lineaDeTiempo(Activo $activo, $movimientos, $mantenimientos = null, $bajas = null): \Illuminate\Support\Collection
    {
        $nombreUsuario = fn($u) => $u?->colaborador?->nombre_completo ?: ($u?->nombre_usuario ?? 'Sistema');
        $eventos = collect();

        $origenes = [
            'IMPORTADO_SIGA' => 'Activo importado desde el padrón SIGA',
            'EXCEL'          => 'Activo cargado desde Excel',
            'MANUAL'         => 'Activo registrado manualmente',
            'REGULARIZACION' => 'Activo registrado por regularización',
        ];
        $detalleRegistro = 'Registrado por ' . $nombreUsuario($activo->creadoPor);
        if ($activo->origen_registro === 'IMPORTADO_SIGA' && $activo->importacionSiga) {
            $detalleRegistro .= ' · Archivo: ' . $activo->importacionSiga->nombre_archivo;
        }
        $eventos->push([
            'fecha'   => $activo->creado_en,
            'titulo'  => $origenes[$activo->origen_registro] ?? 'Activo registrado',
            'detalle' => $detalleRegistro,
            'icono'   => $activo->origen_registro === 'IMPORTADO_SIGA' ? 'bx-upload' : 'bx-plus-circle',
            'color'   => 'primary',
        ]);

        foreach ($movimientos as $det) {
            $mov = $det->movimiento;
            if (! $mov) {
                continue;
            }
            $origen  = $det->responsableOrigen?->nombre_completo ?: ($det->ubicacionOrigen?->nombre ?: 'Almacén');
            $destino = $det->responsableDestino?->nombre_completo ?: ($det->ubicacionDestino?->nombre ?: '—');
            $eventos->push([
                'fecha'   => $mov->fecha_registro,
                'titulo'  => 'Movimiento ' . ($mov->codigo_movimiento ?: '#' . $mov->id_movimiento)
                    . ' · ' . ucfirst(strtolower(str_replace('_', ' ', $mov->tipo))),
                'detalle' => "De {$origen} hacia {$destino}. Registrado por " . $nombreUsuario($mov->registradoPor),
                'icono'   => 'bx-transfer-alt',
                'color'   => 'warning',
            ]);
        }

        foreach ($mantenimientos ?? [] as $mant) {
            $eventos->push([
                'fecha'   => $mant->creado_en,
                'titulo'  => "Mantenimiento {$mant->codigo} · " . ucfirst(strtolower(str_replace('_', ' ', $mant->tipo_mantenimiento))),
                'detalle' => \Illuminate\Support\Str::limit($mant->descripcion, 120)
                    . ' · Estado: ' . ucfirst(strtolower(str_replace('_', ' ', $mant->estado))),
                'icono'   => 'bx-wrench',
                'color'   => 'danger',
            ]);
        }

        foreach ($bajas ?? [] as $baja) {
            $eventos->push([
                'fecha'   => $baja->creado_en,
                'titulo'  => "Propuesta de baja {$baja->codigo} · " . ucfirst(strtolower(str_replace('_', ' ', $baja->causal_baja))),
                'detalle' => \Illuminate\Support\Str::limit($baja->motivo, 120)
                    . ' · Estado: ' . ucfirst(strtolower(str_replace('_', ' ', $baja->estado))),
                'icono'   => 'bx-down-arrow-circle',
                'color'   => 'secondary',
            ]);
            if ($baja->fecha_baja) {
                $eventos->push([
                    'fecha'   => $baja->fecha_baja,
                    'titulo'  => "Baja ejecutada ({$baja->codigo})",
                    'detalle' => 'El activo quedó DADO DE BAJA formalmente.',
                    'icono'   => 'bx-x-circle',
                    'color'   => 'danger',
                ]);
            }
        }

        foreach ($activo->documentos as $doc) {
            $eventos->push([
                'fecha'   => $doc->creado_en,
                'titulo'  => 'Documento adjuntado: ' . $doc->tipo_documento,
                'detalle' => ($doc->nombre_original ?: basename((string) $doc->archivo))
                    . ' · Subido por ' . $nombreUsuario($doc->subidoPor),
                'icono'   => 'bx-file',
                'color'   => 'info',
            ]);
        }

        if ($activo->actualizado_en && $activo->actualizado_en != $activo->creado_en) {
            $eventos->push([
                'fecha'   => $activo->actualizado_en,
                'titulo'  => 'Datos del activo actualizados',
                'detalle' => 'Última modificación por ' . $nombreUsuario($activo->actualizadoPor),
                'icono'   => 'bx-edit',
                'color'   => 'success',
            ]);
        }

        return $eventos
            ->filter(fn($e) => $e['fecha'])
            ->map(function ($e) {
                $e['fecha'] = $e['fecha'] instanceof \Carbon\Carbon ? $e['fecha'] : \Carbon\Carbon::parse($e['fecha']);
                return $e;
            })
            ->sortByDesc('fecha')
            ->values();
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_modelo'           => 'required|integer|exists:modelo,id_modelo',
            'id_condicion_actual' => 'required|integer|exists:estado_activo,id_estado_activo',
            'codigo_interno'      => 'required|string|max:50|unique:activo,codigo_interno',
            'codigo_patrimonial'  => 'required|string|max:100|unique:activo,codigo_patrimonial',
            'numero_serie'        => 'nullable|string|max:150|unique:activo,numero_serie',
            'descripcion'         => 'nullable|string|max:255',
            'imagen'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'fecha_adquisicion'   => 'nullable|date',
            'valor_compra'        => 'nullable|numeric|min:0',
            'proveedor'           => 'nullable|string|max:150',
            'garantia_inicio'     => 'nullable|date',
            'garantia_fin'        => 'nullable|date|after_or_equal:garantia_inicio',
            'observaciones'       => 'nullable|string',
            'id_responsable_actual' => 'nullable|integer|exists:colaboradores,id_colaborador',
            'id_ubicacion_actual'   => ['nullable', 'integer', 'exists:ubicaciones,id_ubicacion', $this->reglaUbicacionHoja()],
        ] + $this->reglasTecnicas(), [
            'id_modelo.required'           => 'Debes seleccionar un modelo.',
            'id_condicion_actual.required' => 'La condición es obligatoria.',
            'codigo_interno.required'      => 'El código interno es obligatorio.',
            'codigo_interno.unique'        => 'Ya existe un activo con ese código interno.',
            'codigo_patrimonial.required'  => 'El código patrimonial es obligatorio.',
            'codigo_patrimonial.unique'    => 'Ya existe un activo con ese código patrimonial.',
            'numero_serie.unique'          => 'Ese número de serie ya está registrado.',
            'imagen.image'                 => 'El archivo debe ser una imagen.',
            'imagen.max'                   => 'La imagen no puede superar los 2 MB.',
            'garantia_fin.after_or_equal'  => 'La fecha de fin de garantía debe ser igual o posterior al inicio.',
        ]);

        $modelo = Modelo::findOrFail($request->id_modelo);

        // La situación es derivada del ciclo de vida: si nace con colaborador
        // queda EN_USO; si no, EN_ALMACEN (listo para asignar luego).
        $situacionInicial = $request->id_responsable_actual ? 'EN_USO' : 'EN_ALMACEN';

        $activo = Activo::create([
            'id_modelo'             => $request->id_modelo,
            'id_categoria'          => $modelo->id_categoria,
            'id_condicion_actual'   => $request->id_condicion_actual,
            'id_situacion_actual'   => $this->situacionId($situacionInicial),
            'id_responsable_actual' => $request->id_responsable_actual ?: null,
            'id_ubicacion_actual'   => $request->id_ubicacion_actual ?: null,
            'codigo_interno'        => strtoupper(trim($request->codigo_interno)),
            'codigo_patrimonial'    => strtoupper(trim($request->codigo_patrimonial)),
            'numero_serie'          => $request->numero_serie ? trim($request->numero_serie) : null,
            'descripcion'           => $request->descripcion ? trim($request->descripcion) : null,
            'imagen'                => $request->hasFile('imagen')
                ? $request->file('imagen')->store('activos', 'public')
                : null,
            'fecha_adquisicion'     => $request->fecha_adquisicion ?: null,
            'valor_compra'          => $request->valor_compra ?: null,
            'proveedor'             => $request->proveedor ? strtoupper(trim($request->proveedor)) : null,
            'garantia_inicio'       => $request->garantia_inicio ?: null,
            'garantia_fin'          => $request->garantia_fin ?: null,
            'observaciones'         => $request->observaciones ? trim($request->observaciones) : null,
            'qr_token'              => (string) Str::uuid(),
            'creado_por'            => Auth::id(),
        ]);

        $this->guardarFichaTecnica($activo, $modelo, $request);

        return redirect()->route('activos.index')->with('success', 'Activo registrado correctamente.');
    }

    public function update(Request $request, int $id)
    {
        $activo = Activo::findOrFail($id);

        $request->validate([
            'id_modelo'           => 'required|integer|exists:modelo,id_modelo',
            'id_condicion_actual' => 'required|integer|exists:estado_activo,id_estado_activo',
            'codigo_interno'      => "required|string|max:50|unique:activo,codigo_interno,{$id},id_activo",
            'codigo_patrimonial'  => "required|string|max:100|unique:activo,codigo_patrimonial,{$id},id_activo",
            'numero_serie'        => "nullable|string|max:150|unique:activo,numero_serie,{$id},id_activo",
            'descripcion'         => 'nullable|string|max:255',
            'imagen'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'fecha_adquisicion'   => 'nullable|date',
            'valor_compra'        => 'nullable|numeric|min:0',
            'proveedor'           => 'nullable|string|max:150',
            'garantia_inicio'     => 'nullable|date',
            'garantia_fin'        => 'nullable|date|after_or_equal:garantia_inicio',
            'observaciones'       => 'nullable|string',
            'id_responsable_actual' => 'nullable|integer|exists:colaboradores,id_colaborador',
            'id_ubicacion_actual'   => ['nullable', 'integer', 'exists:ubicaciones,id_ubicacion', $this->reglaUbicacionHoja()],
        ] + $this->reglasTecnicas(), [
            'id_modelo.required'           => 'Debes seleccionar un modelo.',
            'id_condicion_actual.required' => 'La condición es obligatoria.',
            'codigo_interno.required'      => 'El código interno es obligatorio.',
            'codigo_interno.unique'        => 'Ya existe un activo con ese código interno.',
            'codigo_patrimonial.required'  => 'El código patrimonial es obligatorio.',
            'codigo_patrimonial.unique'    => 'Ya existe un activo con ese código patrimonial.',
            'numero_serie.unique'          => 'Ese número de serie ya está registrado.',
            'imagen.image'                 => 'El archivo debe ser una imagen.',
            'imagen.max'                   => 'La imagen no puede superar los 2 MB.',
            'garantia_fin.after_or_equal'  => 'La fecha de fin de garantía debe ser igual o posterior al inicio.',
        ]);

        $modelo = Modelo::findOrFail($request->id_modelo);

        $imagen = $activo->imagen;
        if ($request->hasFile('imagen')) {
            if ($imagen) {
                Storage::disk('public')->delete($imagen);
            }
            $imagen = $request->file('imagen')->store('activos', 'public');
        }

        $activo->update([
            'id_modelo'             => $request->id_modelo,
            'id_categoria'          => $modelo->id_categoria,
            'id_condicion_actual'   => $request->id_condicion_actual,
            // La situación NO se edita aquí: la gestionan los movimientos.
            'id_responsable_actual' => $request->id_responsable_actual ?: null,
            'id_ubicacion_actual'   => $request->id_ubicacion_actual ?: null,
            'codigo_interno'        => strtoupper(trim($request->codigo_interno)),
            'codigo_patrimonial'    => strtoupper(trim($request->codigo_patrimonial)),
            'numero_serie'          => $request->numero_serie ? trim($request->numero_serie) : null,
            'descripcion'           => $request->descripcion ? trim($request->descripcion) : null,
            'imagen'                => $imagen,
            'fecha_adquisicion'     => $request->fecha_adquisicion ?: null,
            'valor_compra'          => $request->valor_compra ?: null,
            'proveedor'             => $request->proveedor ? strtoupper(trim($request->proveedor)) : null,
            'garantia_inicio'       => $request->garantia_inicio ?: null,
            'garantia_fin'          => $request->garantia_fin ?: null,
            'observaciones'         => $request->observaciones ? trim($request->observaciones) : null,
            'actualizado_por'       => Auth::id(),
        ]);

        $this->guardarFichaTecnica($activo, $modelo, $request);

        return redirect()->route('activos.index')->with('success', 'Activo actualizado correctamente.');
    }

    public function destroy(int $id)
    {
        $activo = Activo::findOrFail($id);

        // Borrado LÓGICO (SoftDeletes): no se elimina el archivo de imagen para
        // poder restaurar el activo íntegro más adelante.
        $activo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activo eliminado correctamente.',
        ]);
    }

    /**
     * Regla de validación: la ubicación elegida debe ser un nodo HOJA, es decir,
     * el último nivel del árbol (un ambiente final sin sub-ubicaciones activas).
     * Refuerza en el servidor lo que el treeview ya restringe en el cliente.
     */
    private function reglaUbicacionHoja(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) {
            if ($value && Ubicacion::where('id_ubicacion_padre', $value)->where('estado', 'ACTIVO')->exists()) {
                $fail('Debes seleccionar el último nivel de la ubicación (un ambiente final, sin sub-ubicaciones).');
            }
        };
    }

    // ──────────────────────────────────────────────────────────────────
    //  Ficha técnica TI (activo_tecnico)
    // ──────────────────────────────────────────────────────────────────

    /** Columnas de la ficha técnica, capturadas en el form con prefijo tec_. */
    private const CAMPOS_TECNICOS = [
        'procesador',
        'memoria_ram',
        'almacenamiento',
        'tipo_almacenamiento',
        'sistema_operativo',
        'direccion_mac',
        'direccion_ip',
        'nombre_equipo',
        'dominio',
        'licencia_office',
        'antivirus',
        'accesorios',
        'observaciones_tecnicas',
        'estado_operativo',
    ];

    /** Reglas de validación de la ficha técnica (todas opcionales). */
    private function reglasTecnicas(): array
    {
        return [
            'tec_procesador'             => 'nullable|string|max:150',
            'tec_memoria_ram'            => 'nullable|string|max:60',
            'tec_almacenamiento'         => 'nullable|string|max:100',
            'tec_tipo_almacenamiento'    => 'nullable|in:HDD,SSD,NVME,EMMC,OTRO',
            'tec_sistema_operativo'      => 'nullable|string|max:100',
            'tec_direccion_mac'          => 'nullable|string|max:60',
            'tec_direccion_ip'           => 'nullable|string|max:60',
            'tec_nombre_equipo'          => 'nullable|string|max:100',
            'tec_dominio'                => 'nullable|string|max:100',
            'tec_licencia_office'        => 'nullable|string|max:100',
            'tec_antivirus'              => 'nullable|string|max:100',
            'tec_accesorios'             => 'nullable|string|max:255',
            'tec_observaciones_tecnicas' => 'nullable|string|max:1000',
            'tec_estado_operativo'       => 'nullable|in:OPERATIVO,INOPERATIVO,EN_REVISION,EN_MANTENIMIENTO,PENDIENTE_BAJA,DADO_DE_BAJA',
        ];
    }

    /**
     * Crea/actualiza la ficha técnica si la categoría del modelo la requiere; si
     * la categoría NO la requiere (p. ej. cambió a una no tecnológica), elimina
     * la ficha existente para mantener coherencia.
     */
    private function guardarFichaTecnica(Activo $activo, Modelo $modelo, Request $request): void
    {
        $requiere = (bool) CategoriaActivo::where('id_categoria', $modelo->id_categoria)
            ->value('requiere_ficha_tecnica');

        if (! $requiere) {
            ActivoTecnico::where('id_activo', $activo->id_activo)->delete();
            return;
        }

        $datos = [];
        foreach (self::CAMPOS_TECNICOS as $campo) {
            $valor = $request->input("tec_{$campo}");
            $datos[$campo] = is_string($valor) && trim($valor) !== '' ? trim($valor) : null;
        }
        $datos['estado_operativo'] = $datos['estado_operativo'] ?: 'OPERATIVO';

        ActivoTecnico::updateOrCreate(['id_activo' => $activo->id_activo], $datos);
    }

    /**
     * Resuelve el id de una situación (estado_activo tipo SITUACION) por su código.
     */
    private function situacionId(string $codigo): int
    {
        return (int) EstadoActivo::where('tipo', 'SITUACION')
            ->where('codigo', $codigo)
            ->value('id_estado_activo');
    }

    /**
     * Resuelve un QR escaneado: ubica el activo por su token y abre su ficha.
     * La ruta vive dentro del grupo autenticado, así que solo usuarios
     * autorizados (almacén / encargados) pueden ver el detalle.
     */
    public function qrShow(string $token)
    {
        $activo = Activo::where('qr_token', $token)->firstOrFail();

        return redirect()->route('activos.index', ['ver' => $activo->id_activo]);
    }

    /**
     * Vista imprimible de etiquetas (QR + código de barras) para uno o varios
     * activos. Recibe ?ids=1,2,3; sin parámetro genera etiquetas de todos.
     */
    public function etiquetas(Request $request)
    {
        $query = Activo::with('modelo.marca')->orderBy('codigo_interno');

        if ($request->filled('ids')) {
            $ids = collect(explode(',', $request->ids))
                ->map(fn($id) => (int) trim($id))
                ->filter()
                ->all();
            $query->whereIn('id_activo', $ids);
        }

        $etiquetas = $query->get()->map(function (Activo $a) {
            return [
                'codigo_interno'     => $a->codigo_interno,
                'codigo_patrimonial' => $a->codigo_patrimonial,
                'modelo'             => trim(($a->modelo?->marca?->nombre ?? '') . ' ' . ($a->modelo?->nombre ?? '')),
                'qr_url'             => route('activos.qr', $a->ensureQrToken()),
            ];
        })->values();

        return view('content.activos.etiquetas', compact('etiquetas'));
    }

    /**
     * Ruta jerárquica completa de la ubicación física (Edificio › Piso › Oficina…),
     * reconstruida subiendo por la cadena de padres a partir del mapa precargado.
     */
    public static function rutaUbicacion(?Ubicacion $ubic, $ubicacionesPorId = null): ?string
    {
        if (! $ubic) {
            return null;
        }

        $cursor = $ubicacionesPorId?->get($ubic->id_ubicacion);
        if (! $cursor) {
            return $ubic->nombre;
        }

        $cadena = [];
        $guard  = 0;
        while ($cursor && $guard++ < 20) {
            array_unshift($cadena, $cursor->nombre);
            $cursor = $cursor->id_ubicacion_padre ? $ubicacionesPorId->get($cursor->id_ubicacion_padre) : null;
        }

        return implode(' › ', $cadena);
    }

    public static function formatActivo(Activo $a, $ubicacionesPorId = null): array
    {
        $responsable = $a->responsable;
        $nombreResponsable = $responsable
            ? trim("{$responsable->per_apepat} " . ($responsable->per_apemat ? "{$responsable->per_apemat}, " : ', ') . "{$responsable->per_nombre}")
            : null;

        $ubic = $a->ubicacion;
        $rutaUbicacion = static::rutaUbicacion($ubic, $ubicacionesPorId);

        return [
            'id_activo'             => $a->id_activo,
            'id_modelo'             => $a->id_modelo,
            'id_condicion_actual'   => $a->id_condicion_actual,
            'id_situacion_actual'   => $a->id_situacion_actual,
            'id_responsable_actual' => $a->id_responsable_actual,
            'codigo_interno'        => $a->codigo_interno,
            'codigo_patrimonial'    => $a->codigo_patrimonial,
            'numero_serie'          => $a->numero_serie,
            'descripcion'           => $a->descripcion,
            'imagen'                => $a->imagen,
            'imagen_url'            => $a->imagen ? asset('storage/' . $a->imagen) : null,
            'qr_token'              => $a->qr_token,
            'qr_url'                => $a->qr_token ? route('activos.qr', $a->qr_token) : null,
            'fecha_adquisicion'     => $a->fecha_adquisicion,
            'valor_compra'          => $a->valor_compra,
            'proveedor'             => $a->proveedor,
            'garantia_inicio'       => $a->garantia_inicio,
            'garantia_fin'          => $a->garantia_fin,
            'observaciones'         => $a->observaciones,
            'modelo_nombre'         => $a->modelo?->nombre ?? '—',
            'marca_nombre'          => $a->modelo?->marca?->nombre ?? '—',
            'categoria_nombre'      => $a->modelo?->categoriaActivo?->nombre ?? '—',
            'condicion_nombre'      => $a->condicion?->nombre ?? '—',
            'situacion_nombre'      => $a->situacion?->nombre ?? '—',
            'sede_nombre'           => $ubic?->sede?->nombre_sede ?? '—',
            'sede_direccion'        => $ubic?->sede?->ubicacion,
            'id_ubicacion'          => $a->id_ubicacion_actual,
            'ubicacion_nombre'      => $ubic?->nombre ?? '—',
            'ubicacion_tipo'        => $ubic?->tipo,
            'ubicacion_codigo'      => $ubic?->codigo,
            'ubicacion_descripcion' => $ubic?->descripcion,
            'ubicacion_ruta'        => $rutaUbicacion,
            'responsable_nombre'    => $nombreResponsable,
            'requiere_ficha'        => (bool) ($a->modelo?->categoriaActivo?->requiere_ficha_tecnica),
            'tecnico'               => ($t = $a->activoTecnico) ? [
                'procesador'             => $t->procesador,
                'memoria_ram'            => $t->memoria_ram,
                'almacenamiento'         => $t->almacenamiento,
                'tipo_almacenamiento'    => $t->tipo_almacenamiento,
                'sistema_operativo'      => $t->sistema_operativo,
                'direccion_mac'          => $t->direccion_mac,
                'direccion_ip'           => $t->direccion_ip,
                'nombre_equipo'          => $t->nombre_equipo,
                'dominio'                => $t->dominio,
                'licencia_office'        => $t->licencia_office,
                'antivirus'              => $t->antivirus,
                'accesorios'             => $t->accesorios,
                'observaciones_tecnicas' => $t->observaciones_tecnicas,
                'estado_operativo'       => $t->estado_operativo,
            ] : null,
        ];
    }
}
