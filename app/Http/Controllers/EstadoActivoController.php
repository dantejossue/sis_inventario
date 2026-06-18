<?php

namespace App\Http\Controllers;

use App\Models\EstadoActivo;
use Illuminate\Http\Request;

class EstadoActivoController extends Controller
{
    public function index()
    {
        $estados = EstadoActivo::orderBy('tipo_estado')->orderBy('nombre')->get();

        $condiciones = $estados->where('tipo_estado', 'CONDICION')->values();
        $situaciones = $estados->where('tipo_estado', 'SITUACION')->values();

        return view('content.catalogos.estados.index', compact('condiciones', 'situaciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => [
                'required', 'string', 'max:100',
                \Illuminate\Validation\Rule::unique('estado_activo')->where(function ($query) use ($request) {
                    return $query->where('tipo_estado', $request->tipo_estado);
                }),
            ],
            'tipo_estado' => 'required|in:CONDICION,SITUACION',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required'    => 'El nombre del estado es obligatorio.',
            'nombre.unique'      => 'Ya existe un estado con ese nombre para este tipo.',
            'tipo_estado.required' => 'El tipo de estado es obligatorio.',
            'tipo_estado.in'     => 'El tipo de estado debe ser CONDICION o SITUACION.',
        ]);

        $estado = EstadoActivo::create([
            'nombre'      => strtoupper(trim($request->nombre)),
            'tipo_estado' => $request->tipo_estado,
            'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
            'estado'      => 'ACTIVO',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado registrado correctamente.',
            'data'    => $estado,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $estado = EstadoActivo::findOrFail($id);

        $request->validate([
            'nombre' => [
                'required', 'string', 'max:100',
                \Illuminate\Validation\Rule::unique('estado_activo')->where(function ($query) use ($request) {
                    return $query->where('tipo_estado', $request->tipo_estado);
                })->ignore($id, 'id_estado_activo'),
            ],
            'tipo_estado' => 'required|in:CONDICION,SITUACION',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required'    => 'El nombre del estado es obligatorio.',
            'nombre.unique'      => 'Ya existe un estado con ese nombre para este tipo.',
            'tipo_estado.required' => 'El tipo de estado es obligatorio.',
        ]);

        $estado->update([
            'nombre'      => strtoupper(trim($request->nombre)),
            'tipo_estado' => $request->tipo_estado,
            'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente.',
            'data'    => $estado,
        ]);
    }

    public function toggleEstado(int $id)
    {
        $estado = EstadoActivo::findOrFail($id);

        $estado->estado = $estado->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $estado->save();

        return response()->json([
            'success'      => true,
            'message'      => 'Estado actualizado.',
            'nuevo_estado' => $estado->estado,
        ]);
    }
}
