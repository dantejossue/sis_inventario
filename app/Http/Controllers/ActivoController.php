<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\Colaborador;
use App\Models\EstadoActivo;
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

        $activos = Activo::with('modelo.marca', 'modelo.categoriaActivo', 'condicion', 'situacion', 'ubicacion.sede', 'responsable')
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
        $activo = Activo::findOrFail($id);

        [
            'modelos' => $modelos,
            'condiciones' => $condiciones,
            'situaciones' => $situaciones,
            'colaboradores' => $colaboradores,
            'ubicaciones' => $ubicaciones
        ] = $this->catalogos();

        return view('content.activos.edit', compact('activo', 'modelos', 'condiciones', 'situaciones', 'colaboradores', 'ubicaciones'));
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
        ], [
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

        Activo::create([
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
        ], [
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

    public static function formatActivo(Activo $a, $ubicacionesPorId = null): array
    {
        $responsable = $a->responsable;
        $nombreResponsable = $responsable
            ? trim("{$responsable->per_apepat} " . ($responsable->per_apemat ? "{$responsable->per_apemat}, " : ', ') . "{$responsable->per_nombre}")
            : null;

        // Ruta jerárquica completa de la ubicación física (Edificio › Piso › Oficina…),
        // reconstruida subiendo por la cadena de padres a partir del mapa precargado.
        $ubic = $a->ubicacion;
        $rutaUbicacion = null;
        if ($ubic) {
            $cursor = $ubicacionesPorId?->get($ubic->id_ubicacion);
            if ($cursor) {
                $cadena = [];
                $guard  = 0;
                while ($cursor && $guard++ < 20) {
                    array_unshift($cadena, $cursor->nombre);
                    $cursor = $cursor->id_ubicacion_padre ? $ubicacionesPorId->get($cursor->id_ubicacion_padre) : null;
                }
                $rutaUbicacion = implode(' › ', $cadena);
            } else {
                $rutaUbicacion = $ubic->nombre;
            }
        }

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
        ];
    }
}
