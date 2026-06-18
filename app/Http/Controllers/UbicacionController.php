<?php

namespace App\Http\Controllers;

use App\Models\Sede;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UbicacionController extends Controller
{
    private const TIPOS = ['PABELLON', 'PISO', 'OFICINA', 'ALMACEN', 'AULA', 'LABORATORIO', 'OTRO'];

    public function index()
    {
        $sedes = Sede::where('estado', 'ACTIVO')
            ->orderBy('nombre_sede')
            ->get(['id_sede', 'nombre_sede']);

        $todas = $this->cargarTodas();

        $ubicaciones = $todas
            ->map(fn($u) => $this->formato($u, $todas))
            ->sortBy('ruta')
            ->values();

        return view('content.ubicaciones.index', compact('ubicaciones', 'sedes'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        $ubic = Ubicacion::create($data + ['estado' => 'ACTIVO']);

        return response()->json([
            'success' => true,
            'message' => 'Ubicación registrada correctamente.',
            'data'    => $this->filaJson($ubic->id_ubicacion),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $ubic = Ubicacion::findOrFail($id);

        $data = $this->validar($request, $ubic);

        $ubic->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Ubicación actualizada correctamente.',
            'data'    => $this->filaJson($ubic->id_ubicacion),
        ]);
    }

    public function toggleEstado(int $id)
    {
        $ubic = Ubicacion::findOrFail($id);

        $ubic->estado = $ubic->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $ubic->save();

        return response()->json([
            'success'      => true,
            'message'      => 'Estado de la ubicación actualizado.',
            'nuevo_estado' => $ubic->estado,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    /** Trae todas las ubicaciones con sede y conteo de activos, indexadas por id. */
    private function cargarTodas()
    {
        return Ubicacion::with('sede:id_sede,nombre_sede')
            ->withCount('activos')
            ->get()
            ->keyBy('id_ubicacion');
    }

    /**
     * Convierte una ubicación en la fila que consume el DataTable, incluyendo
     * la ruta jerárquica completa (Sede › Edificio › Piso › Ambiente).
     */
    private function formato(Ubicacion $u, $porId): array
    {
        $cadena  = [];
        $cursor  = $u;
        $guard   = 0;
        while ($cursor && $guard++ < 20) {
            array_unshift($cadena, $cursor->nombre);
            $cursor = $cursor->id_ubicacion_padre ? $porId->get($cursor->id_ubicacion_padre) : null;
        }

        $sedeNombre = $u->sede?->nombre_sede ?? '—';

        return [
            'id_ubicacion'       => $u->id_ubicacion,
            'id_sede'            => $u->id_sede,
            'sede_nombre'        => $sedeNombre,
            'id_ubicacion_padre' => $u->id_ubicacion_padre,
            'padre_nombre'       => $u->id_ubicacion_padre
                ? ($porId->get($u->id_ubicacion_padre)?->nombre ?? '—')
                : null,
            'nombre'             => $u->nombre,
            'tipo'               => $u->tipo,
            'codigo'             => $u->codigo,
            'descripcion'        => $u->descripcion,
            'estado'             => $u->estado,
            'activos_count'      => $u->activos_count,
            'nivel'              => count($cadena) - 1,
            'ruta'               => $sedeNombre . ' › ' . implode(' › ', $cadena),
        ];
    }

    /** Reconstruye una sola fila formateada (tras crear/editar). */
    private function filaJson(int $id): array
    {
        $todas = $this->cargarTodas();

        return $this->formato($todas->get($id), $todas);
    }

    /**
     * Valida la entrada y devuelve los campos listos para guardar.
     * Incluye reglas propias del árbol: el padre debe ser de la misma sede y no
     * se permiten ciclos (un nodo no puede colgar de sí mismo ni de un descendiente).
     */
    private function validar(Request $request, ?Ubicacion $actual = null): array
    {
        $request->validate([
            'id_sede'            => 'required|integer|exists:sedes,id_sede',
            'id_ubicacion_padre' => 'nullable|integer|exists:ubicaciones,id_ubicacion',
            'nombre'             => 'required|string|max:150',
            'tipo'               => 'required|in:' . implode(',', self::TIPOS),
            'codigo'             => 'nullable|string|max:50',
            'descripcion'        => 'nullable|string|max:255',
        ], [
            'id_sede.required' => 'La sede es obligatoria.',
            'nombre.required'  => 'El nombre de la ubicación es obligatorio.',
            'tipo.required'    => 'El tipo es obligatorio.',
            'tipo.in'          => 'Tipo de ubicación no válido.',
        ]);

        $idPadre = $request->id_ubicacion_padre ?: null;

        if ($idPadre) {
            $padre = Ubicacion::findOrFail($idPadre);

            if ((int) $padre->id_sede !== (int) $request->id_sede) {
                throw ValidationException::withMessages([
                    'id_ubicacion_padre' => 'La ubicación superior debe pertenecer a la misma sede.',
                ]);
            }

            if ($actual && $this->generaCiclo($actual->id_ubicacion, $idPadre)) {
                throw ValidationException::withMessages([
                    'id_ubicacion_padre' => 'No puedes mover la ubicación dentro de sí misma o de un descendiente.',
                ]);
            }
        }

        return [
            'id_sede'            => $request->id_sede,
            'id_ubicacion_padre' => $idPadre,
            'nombre'             => strtoupper(trim($request->nombre)),
            'tipo'               => $request->tipo,
            'codigo'             => $request->codigo ? strtoupper(trim($request->codigo)) : null,
            'descripcion'        => $request->descripcion ? trim($request->descripcion) : null,
        ];
    }

    /** ¿Asignar $idPadre al nodo $idNodo crearía un ciclo? */
    private function generaCiclo(int $idNodo, int $idPadre): bool
    {
        if ($idNodo === $idPadre) {
            return true;
        }

        // Sube por la cadena de ancestros del padre propuesto: si aparece el nodo, hay ciclo.
        $cursor = Ubicacion::find($idPadre);
        $guard  = 0;
        while ($cursor && $guard++ < 50) {
            if ((int) $cursor->id_ubicacion === $idNodo) {
                return true;
            }
            $cursor = $cursor->id_ubicacion_padre ? Ubicacion::find($cursor->id_ubicacion_padre) : null;
        }

        return false;
    }
}
