<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::all();

        return view('content.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100|unique:roles,nombre',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique'   => 'Ya existe un rol con ese nombre.',
        ]);

        $rol = Rol::create([
            'nombre'      => strtoupper(trim($request->nombre)),
            'descripcion' => $request->descripcion ? strtoupper(trim($request->descripcion)) : null,
            'estado'      => 'ACTIVO',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rol registrado correctamente.',
            'data'    => $rol,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $rol = Rol::findOrFail($id);

        $request->validate([
            'nombre'      => "required|string|max:100|unique:roles,nombre,{$id},id_rol",
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique'   => 'Ya existe un rol con ese nombre.',
        ]);

        $rol->update([
            'nombre'      => strtoupper(trim($request->nombre)),
            'descripcion' => $request->descripcion ? strtoupper(trim($request->descripcion)) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado correctamente.',
            'data'    => $rol,
        ]);
    }

    public function toggleEstado(int $id)
    {
        $rol = Rol::findOrFail($id);

        if ($rol->estado === 'ACTIVO') {
            $tieneUsuariosActivos = User::where('id_rol', $id)->where('estado', 'ACTIVO')->exists();

            if ($tieneUsuariosActivos) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede desactivar un rol que tiene usuarios activos asignados.',
                ], 422);
            }
        }

        $rol->estado = $rol->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $rol->save();

        return response()->json([
            'success'      => true,
            'message'      => 'Estado del rol actualizado.',
            'nuevo_estado' => $rol->estado,
        ]);
    }
}
