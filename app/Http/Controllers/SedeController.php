<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use App\Models\Sede;
use App\Models\Sede_Dependencia;
use Illuminate\Http\Request;

class SedeController extends Controller
{
    public function index()
    {
        $sedes = Sede::withCount(['sedeDependencias as dependencias_count' => function ($q) {
            $q->where('estado', 'ACTIVO');
        }])->get();

        $dependencias = Dependencia::orderBy('nombre_dependencia')->get();

        return view('content.sedes.index', compact('sedes', 'dependencias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_sede' => 'required|string|max:150|unique:sedes,nombre_sede',
            'ubicacion'   => 'nullable|string|max:255',
        ], [
            'nombre_sede.required' => 'El nombre del campus es obligatorio.',
            'nombre_sede.unique'   => 'Ya existe una sede con ese nombre.',
        ]);

        $sede = Sede::create([
            'nombre_sede' => strtoupper(trim($request->nombre_sede)),
            'ubicacion'   => $request->ubicacion ? trim($request->ubicacion) : null,
            'estado'      => 'ACTIVO',
        ]);

        $sede->dependencias_count = 0;

        return response()->json([
            'success' => true,
            'message' => 'Sede registrada correctamente.',
            'data'    => $sede,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $sede = Sede::findOrFail($id);

        $request->validate([
            'nombre_sede' => "required|string|max:150|unique:sedes,nombre_sede,{$id},id_sede",
            'ubicacion'   => 'nullable|string|max:255',
        ], [
            'nombre_sede.required' => 'El nombre del campus es obligatorio.',
            'nombre_sede.unique'   => 'Ya existe una sede con ese nombre.',
        ]);

        $sede->update([
            'nombre_sede' => strtoupper(trim($request->nombre_sede)),
            'ubicacion'   => $request->ubicacion ? trim($request->ubicacion) : null,
        ]);

        $sede->loadCount(['sedeDependencias as dependencias_count' => function ($q) {
            $q->where('estado', 'ACTIVO');
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Sede actualizada correctamente.',
            'data'    => $sede,
        ]);
    }

    public function toggleEstado(int $id)
    {
        $sede = Sede::findOrFail($id);

        $sede->estado = $sede->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $sede->save();

        return response()->json([
            'success'      => true,
            'message'      => 'Estado de la sede actualizado.',
            'nuevo_estado' => $sede->estado,
        ]);
    }

    public function getDependencias(int $id)
    {
        Sede::findOrFail($id);

        $asignadas = Sede_Dependencia::where('id_sede', $id)
            ->where('estado', 'ACTIVO')
            ->pluck('id_dependencia');

        return response()->json(['success' => true, 'asignadas' => $asignadas]);
    }

    public function syncDependencias(Request $request, int $id)
    {
        Sede::findOrFail($id);

        $request->validate([
            'dependencias'   => 'nullable|array',
            'dependencias.*' => 'integer|exists:dependencias,id_dependencia',
        ]);

        $ids = $request->input('dependencias', []);

        // Activar o crear los seleccionados
        foreach ($ids as $idDep) {
            Sede_Dependencia::updateOrCreate(
                ['id_sede' => $id, 'id_dependencia' => $idDep],
                ['estado' => 'ACTIVO']
            );
        }

        // Desactivar los no seleccionados (sin eliminar para preservar FKs de colaboradores)
        Sede_Dependencia::where('id_sede', $id)
            ->when(!empty($ids), fn($q) => $q->whereNotIn('id_dependencia', $ids))
            ->update(['estado' => 'INACTIVO']);

        $count = Sede_Dependencia::where('id_sede', $id)->where('estado', 'ACTIVO')->count();

        return response()->json([
            'success'           => true,
            'message'           => 'Dependencias actualizadas correctamente.',
            'dependencias_count' => $count,
        ]);
    }
}
