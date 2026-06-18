<?php

namespace App\Http\Controllers;

use App\Models\Colaborador;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $roles        = Rol::where('estado', 'ACTIVO')->get();
        $colaboradores = Colaborador::where('estado', 'ACTIVO')->get();
        $usuarios     = User::with([
            'colaborador.sedeDependencia.dependencia',
            'rol'
        ])->get();

        return view('content.usuarios.index', compact('roles', 'colaboradores', 'usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_colaborador' => 'required|exists:colaboradores,id_colaborador|unique:usuarios,id_colaborador',
            'id_rol'         => 'required|exists:roles,id_rol',
            'nombre_usuario' => 'required|string|max:50|unique:usuarios,nombre_usuario',
            'contrasena'     => 'required|string|min:6',
        ], [
            'id_colaborador.unique'   => 'Este colaborador ya tiene un usuario asignado.',
            'nombre_usuario.unique'   => 'El nombre de usuario ya está en uso.',
            'nombre_usuario.required' => 'Debe ingresar un nombre de usuario.',
            'id_colaborador.required' => 'Debe seleccionar un colaborador.',
            'contrasena.required'     => 'Debe ingresar una contraseña.',
            'contrasena.min'          => 'La contraseña debe tener mínimo 6 caracteres.',
            'id_rol.required'         => 'Debe seleccionar un rol.',
        ]);

        $usuario = User::create([
            'id_colaborador' => $request->id_colaborador,
            'id_rol'         => $request->id_rol,
            'nombre_usuario' => $request->nombre_usuario,
            'contrasena'     => Hash::make($request->contrasena),
            'estado'         => 'ACTIVO',
        ]);

        $usuario->load(['colaborador.sedeDependencia.dependencia', 'rol']);

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado correctamente.',
            'data'    => $usuario,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $usuario = User::where('id_usuario', $id)->firstOrFail();

        $request->validate([
            'id_rol'         => 'required|exists:roles,id_rol',
            'nombre_usuario' => "required|string|max:50|unique:usuarios,nombre_usuario,{$usuario->id_usuario},id_usuario",
        ], [
            'nombre_usuario.unique'   => 'El nombre de usuario ya está en uso.',
            'nombre_usuario.required' => 'Debe ingresar un nombre de usuario.',
            'id_rol.required'         => 'Debe seleccionar un rol.',
        ]);

        $usuario->update([
            'id_rol'         => $request->id_rol,
            'nombre_usuario' => $request->nombre_usuario,
        ]);

        $usuario->load(['colaborador.sedeDependencia.dependencia', 'rol']);

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente.',
            'data'    => $usuario,
        ]);
    }

    public function changePassword(Request $request, int $id)
    {
        $usuario = User::where('id_usuario', $id)->firstOrFail();

        $request->validate([
            'nueva_contrasena' => 'required|string|min:6|confirmed',
        ], [
            'nueva_contrasena.required'  => 'Debe ingresar la nueva contraseña.',
            'nueva_contrasena.min'       => 'La contraseña debe tener mínimo 6 caracteres.',
            'nueva_contrasena.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario->update([
            'contrasena' => Hash::make($request->nueva_contrasena),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }

    public function toggleEstado(int $id)
    {
        $usuario = User::where('id_usuario', $id)->firstOrFail();

        if ($usuario->id_usuario === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puede desactivar su propia cuenta.',
            ], 422);
        }

        // Impedir dejar el sistema sin ningún administrador activo
        if ($usuario->estado === 'ACTIVO' && $usuario->esAdministrador()) {
            $adminsActivos = User::whereHas('rol', fn($q) => $q->where('nombre', 'ADMINISTRADOR'))
                ->where('estado', 'ACTIVO')
                ->count();

            if ($adminsActivos <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede desactivar al único administrador activo del sistema.',
                ], 422);
            }
        }

        $usuario->estado = ($usuario->estado === 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $usuario->save();

        return response()->json([
            'success'      => true,
            'message'      => 'El estado del usuario ha sido actualizado.',
            'nuevo_estado' => $usuario->estado,
        ]);
    }
}
