<?php

namespace App\Http\Controllers;

use App\Models\CategoriaActivo;
use App\Models\Marca;
use App\Models\Modelo;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas     = Marca::withCount('modelos')->get();
        $categorias = CategoriaActivo::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        $modelos = Modelo::with('marca', 'categoriaActivo')->get()->map(function ($m) {
            $arr = $m->toArray();
            $arr['marca_nombre']    = $m->marca->nombre;
            $arr['categoria_nombre'] = $m->categoriaActivo?->nombre ?? '—';
            return $arr;
        })->values();

        return view('content.catalogos.marcas.index', compact('marcas', 'categorias', 'modelos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:marca,nombre',
        ], [
            'nombre.required' => 'El nombre de la marca es obligatorio.',
            'nombre.unique'   => 'Ya existe una marca con ese nombre.',
        ]);

        $marca = Marca::create([
            'nombre' => strtoupper(trim($request->nombre)),
            'estado' => 'ACTIVO',
        ]);

        $marca->modelos_count = 0;

        return response()->json([
            'success' => true,
            'message' => 'Marca registrada correctamente.',
            'data'    => $marca,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $marca = Marca::findOrFail($id);

        $request->validate([
            'nombre' => "required|string|max:100|unique:marca,nombre,{$id},id_marca",
        ], [
            'nombre.required' => 'El nombre de la marca es obligatorio.',
            'nombre.unique'   => 'Ya existe una marca con ese nombre.',
        ]);

        $marca->update([
            'nombre' => strtoupper(trim($request->nombre)),
        ]);

        $marca->loadCount('modelos');

        return response()->json([
            'success' => true,
            'message' => 'Marca actualizada correctamente.',
            'data'    => $marca,
        ]);
    }

    public function toggleEstado(int $id)
    {
        $marca = Marca::findOrFail($id);

        $marca->estado = $marca->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $marca->save();

        return response()->json([
            'success'      => true,
            'message'      => 'Estado de la marca actualizado.',
            'nuevo_estado' => $marca->estado,
        ]);
    }

    // ─── Modelos ────────────────────────────────────────────────────────────────

    public function storeModelo(Request $request)
    {
        $request->validate([
            'id_marca'    => 'required|integer|exists:marca,id_marca',
            'id_categoria' => 'nullable|integer|exists:categoria_activo,id_categoria',
            'nombre'      => [
                'required', 'string', 'max:150',
                \Illuminate\Validation\Rule::unique('modelo')->where(function ($query) use ($request) {
                    return $query
                        ->where('id_marca', $request->id_marca)
                        ->where('id_categoria', $request->id_categoria ?? null);
                }),
            ],
            'descripcion' => 'nullable|string|max:255',
        ], [
            'id_marca.required' => 'Debes seleccionar una marca.',
            'id_marca.exists'   => 'La marca seleccionada no es válida.',
            'nombre.required'   => 'El nombre del modelo es obligatorio.',
            'nombre.unique'     => 'Ya existe un modelo con ese nombre para esta marca y categoría.',
        ]);

        $modelo = Modelo::create([
            'id_marca'    => $request->id_marca,
            'id_categoria' => $request->id_categoria ?: null,
            'nombre'      => strtoupper(trim($request->nombre)),
            'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
            'estado'      => 'ACTIVO',
        ]);

        $modelo->load('marca', 'categoriaActivo');

        $data                   = $modelo->toArray();
        $data['marca_nombre']   = $modelo->marca->nombre;
        $data['categoria_nombre'] = $modelo->categoriaActivo?->nombre ?? '—';

        return response()->json([
            'success' => true,
            'message' => 'Modelo registrado correctamente.',
            'data'    => $data,
        ]);
    }

    public function updateModelo(Request $request, int $id)
    {
        $modelo = Modelo::findOrFail($id);

        $request->validate([
            'id_marca'    => 'required|integer|exists:marca,id_marca',
            'id_categoria' => 'nullable|integer|exists:categoria_activo,id_categoria',
            'nombre'      => [
                'required', 'string', 'max:150',
                \Illuminate\Validation\Rule::unique('modelo')->where(function ($query) use ($request) {
                    return $query
                        ->where('id_marca', $request->id_marca)
                        ->where('id_categoria', $request->id_categoria ?? null);
                })->ignore($id, 'id_modelo'),
            ],
            'descripcion' => 'nullable|string|max:255',
        ], [
            'id_marca.required' => 'Debes seleccionar una marca.',
            'id_marca.exists'   => 'La marca seleccionada no es válida.',
            'nombre.required'   => 'El nombre del modelo es obligatorio.',
            'nombre.unique'     => 'Ya existe un modelo con ese nombre para esta marca y categoría.',
        ]);

        $modelo->update([
            'id_marca'    => $request->id_marca,
            'id_categoria' => $request->id_categoria ?: null,
            'nombre'      => strtoupper(trim($request->nombre)),
            'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
        ]);

        $modelo->load('marca', 'categoriaActivo');

        $data                   = $modelo->toArray();
        $data['marca_nombre']   = $modelo->marca->nombre;
        $data['categoria_nombre'] = $modelo->categoriaActivo?->nombre ?? '—';

        return response()->json([
            'success' => true,
            'message' => 'Modelo actualizado correctamente.',
            'data'    => $data,
        ]);
    }

    public function toggleEstadoModelo(int $id)
    {
        $modelo = Modelo::findOrFail($id);

        $modelo->estado = $modelo->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $modelo->save();

        return response()->json([
            'success'      => true,
            'message'      => 'Estado del modelo actualizado.',
            'nuevo_estado' => $modelo->estado,
        ]);
    }
}
