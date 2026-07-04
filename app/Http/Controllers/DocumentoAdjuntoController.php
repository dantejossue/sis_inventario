<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\DocumentoAdjunto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Documentos adjuntos transversales (actas, guías, fotos, informes…).
 * Se asocian a cualquier entidad del sistema mediante entidad_tipo + entidad_id.
 * Los archivos se guardan en el disco "local" (privado): solo se sirven vía
 * la ruta autenticada de descarga, nunca por URL pública.
 */
class DocumentoAdjuntoController extends Controller
{
    /** Extensiones permitidas para adjuntos. */
    private const EXTENSIONES = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx', 'xls', 'xlsx'];

    public function store(Request $request)
    {
        $request->validate([
            'entidad_tipo'     => ['required', Rule::in(DocumentoAdjunto::ENTIDADES)],
            'entidad_id'       => 'required|integer|min:1',
            'tipo_documento'   => 'required|string|max:100',
            'numero_documento' => 'nullable|string|max:100',
            'fecha_documento'  => 'nullable|date',
            'descripcion'      => 'nullable|string|max:255',
            'archivo'          => 'required|file|mimes:' . implode(',', self::EXTENSIONES) . '|max:5120',
        ], [
            'tipo_documento.required' => 'Indica el tipo de documento.',
            'archivo.required'        => 'Debes adjuntar un archivo.',
            'archivo.mimes'           => 'Formato no permitido. Usa: ' . implode(', ', self::EXTENSIONES) . '.',
            'archivo.max'             => 'El archivo no puede superar los 5 MB.',
        ]);

        // La entidad referida debe existir (por ahora solo ACTIVO tiene ficha).
        if ($request->entidad_tipo === 'ACTIVO' && ! Activo::whereKey($request->entidad_id)->exists()) {
            return back()->withErrors(['entidad_id' => 'El activo indicado no existe.']);
        }

        $file = $request->file('archivo');
        $ruta = $file->store('documentos/' . strtolower($request->entidad_tipo), 'local');

        DocumentoAdjunto::create([
            'entidad_tipo'     => $request->entidad_tipo,
            'entidad_id'       => (int) $request->entidad_id,
            'tipo_documento'   => trim($request->tipo_documento),
            'numero_documento' => $request->numero_documento ? trim($request->numero_documento) : null,
            'fecha_documento'  => $request->fecha_documento ?: null,
            'archivo'          => $ruta,
            'nombre_original'  => $file->getClientOriginalName(),
            'extension'        => strtolower($file->getClientOriginalExtension()),
            'tamano_kb'        => (int) round($file->getSize() / 1024),
            'descripcion'      => $request->descripcion ? trim($request->descripcion) : null,
            'subido_por'       => Auth::id(),
        ]);

        return $this->volverAEntidad($request->entidad_tipo, (int) $request->entidad_id)
            ->with('success', 'Documento adjuntado correctamente.');
    }

    public function download(int $id)
    {
        $doc = DocumentoAdjunto::findOrFail($id);

        if (! $doc->archivo || ! Storage::disk('local')->exists($doc->archivo)) {
            abort(404, 'El archivo ya no está disponible.');
        }

        $nombre = $doc->nombre_original ?: basename($doc->archivo);

        return Storage::disk('local')->download($doc->archivo, $nombre);
    }

    public function destroy(int $id)
    {
        $doc = DocumentoAdjunto::findOrFail($id);

        if ($doc->archivo && Storage::disk('local')->exists($doc->archivo)) {
            Storage::disk('local')->delete($doc->archivo);
        }
        $doc->delete();

        return response()->json([
            'success' => true,
            'message' => 'Documento eliminado correctamente.',
        ]);
    }

    /** Redirige a la ficha de la entidad dueña del documento (tab Documentos). */
    private function volverAEntidad(string $tipo, int $id)
    {
        if ($tipo === 'ACTIVO') {
            return redirect()->route('activos.ver', $id)->withFragment('tab-documentos');
        }

        return redirect()->back();
    }
}
