<?php

namespace App\Http\Controllers;

use App\Models\EstadoActivo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EstadoActivoController extends Controller
{
    public function index()
    {
        $estados = EstadoActivo::orderBy('tipo')->orderBy('nombre')->get();

        $condiciones = $estados->where('tipo', 'CONDICION')->values();
        $situaciones = $estados->where('tipo', 'SITUACION')->values();

        return view('content.catalogos.estados.index', compact('condiciones', 'situaciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo'   => 'required|in:CONDICION,SITUACION',
            'descripcion' => 'nullable|string|max:255',
        ], $this->mensajes());

        $codigo = $this->codigoDesde($request->nombre);

        // El identificador único es (tipo, codigo): dos estados del mismo tipo no
        // pueden compartir código.
        if (EstadoActivo::where('tipo', $request->tipo)->where('codigo', $codigo)->exists()) {
            return response()->json([
                'errors' => ['nombre' => ['Ya existe un estado con ese nombre para este tipo.']],
            ], 422);
        }

        $estado = EstadoActivo::create([
            'tipo'        => $request->tipo,
            'codigo'      => $codigo,
            'nombre'      => strtoupper(trim($request->nombre)),
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
            'nombre' => 'required|string|max:100',
            'tipo'   => 'required|in:CONDICION,SITUACION',
            'descripcion' => 'nullable|string|max:255',
        ], $this->mensajes());

        $codigo = $this->codigoDesde($request->nombre);

        $duplicado = EstadoActivo::where('tipo', $request->tipo)
            ->where('codigo', $codigo)
            ->where('id_estado_activo', '!=', $id)
            ->exists();

        if ($duplicado) {
            return response()->json([
                'errors' => ['nombre' => ['Ya existe un estado con ese nombre para este tipo.']],
            ], 422);
        }

        $estado->update([
            'tipo'        => $request->tipo,
            'codigo'      => $codigo,
            'nombre'      => strtoupper(trim($request->nombre)),
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

    /** Código canónico (MAYÚSCULAS con guiones bajos) derivado del nombre. */
    private function codigoDesde(string $nombre): string
    {
        return Str::upper(Str::slug(trim($nombre), '_'));
    }

    private function mensajes(): array
    {
        return [
            'nombre.required' => 'El nombre del estado es obligatorio.',
            'tipo.required'   => 'El tipo de estado es obligatorio.',
            'tipo.in'         => 'El tipo de estado debe ser CONDICION o SITUACION.',
        ];
    }
}
