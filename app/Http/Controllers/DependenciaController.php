<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use App\Models\Sede_Dependencia;
use Illuminate\Http\Request;

class DependenciaController extends Controller
{
    public function index()
    {
        $dependencias = Dependencia::withCount(['sedeDependencias as sedes_count' => function ($q) {
            $q->where('estado', 'ACTIVO');
        }])->get();

        return view('content.dependencias.index', compact('dependencias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_dependencia' => 'required|string|max:150|unique:dependencias,nombre_dependencia',
            'descripcion'        => 'nullable|string|max:255',
        ], [
            'nombre_dependencia.required' => 'El nombre de la dependencia es obligatorio.',
            'nombre_dependencia.unique'   => 'Ya existe una dependencia con ese nombre.',
        ]);

        $dependencia = Dependencia::create([
            'nombre_dependencia' => strtoupper(trim($request->nombre_dependencia)),
            'descripcion'        => $request->descripcion,
            'estado'             => 'ACTIVO',
        ]);

        $dependencia->sedes_count = 0;

        return response()->json([
            'success' => true,
            'message' => 'Dependencia registrada correctamente.',
            'data'    => $dependencia,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $dependencia = Dependencia::findOrFail($id);

        $request->validate([
            'nombre_dependencia' => "required|string|max:150|unique:dependencias,nombre_dependencia,{$id},id_dependencia",
            'descripcion'        => 'nullable|string|max:255',
        ], [
            'nombre_dependencia.required' => 'El nombre de la dependencia es obligatorio.',
            'nombre_dependencia.unique'   => 'Ya existe una dependencia con ese nombre.',
        ]);

        $dependencia->update([
            'nombre_dependencia' => strtoupper(trim($request->nombre_dependencia)),
            'descripcion'        => $request->descripcion,
        ]);

        $dependencia->loadCount(['sedeDependencias as sedes_count' => function ($q) {
            $q->where('estado', 'ACTIVO');
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Dependencia actualizada correctamente.',
            'data'    => $dependencia,
        ]);
    }

    public function toggleEstado(int $id)
    {
        $dependencia = Dependencia::findOrFail($id);

        $dependencia->estado = $dependencia->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $dependencia->save();

        return response()->json([
            'success'      => true,
            'message'      => 'Estado de la dependencia actualizado.',
            'nuevo_estado' => $dependencia->estado,
        ]);
    }
}
