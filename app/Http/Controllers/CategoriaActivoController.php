<?php

namespace App\Http\Controllers;

use App\Models\CategoriaActivo;
use Illuminate\Http\Request;

class CategoriaActivoController extends Controller
{
    public function index()
    {
        $categorias = CategoriaActivo::withCount('modelos')->get();

        return view('content.catalogos.categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:150|unique:categoria_activo,nombre',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'Ya existe una categoría con ese nombre.',
        ]);

        $categoria = CategoriaActivo::create([
            'nombre'      => strtoupper(trim($request->nombre)),
            'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
            'estado'      => 'ACTIVO',
        ]);

        $categoria->modelos_count = 0;

        return response()->json([
            'success' => true,
            'message' => 'Categoría registrada correctamente.',
            'data'    => $categoria,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $categoria = CategoriaActivo::findOrFail($id);

        $request->validate([
            'nombre'      => "required|string|max:150|unique:categoria_activo,nombre,{$id},id_categoria",
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'Ya existe una categoría con ese nombre.',
        ]);

        $categoria->update([
            'nombre'      => strtoupper(trim($request->nombre)),
            'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
        ]);

        $categoria->loadCount('modelos');

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada correctamente.',
            'data'    => $categoria,
        ]);
    }

    public function toggleEstado(int $id)
    {
        $categoria = CategoriaActivo::findOrFail($id);

        $categoria->estado = $categoria->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $categoria->save();

        return response()->json([
            'success'      => true,
            'message'      => 'Estado de la categoría actualizado.',
            'nuevo_estado' => $categoria->estado,
        ]);
    }
}
