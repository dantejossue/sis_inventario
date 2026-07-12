<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaCambio;
use Illuminate\Http\Request;

/**
 * Visor de la traza de cambios sensibles (brief §19). Solo lectura.
 */
class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $entidades = AuditoriaCambio::query()->distinct()->orderBy('entidad_tipo')->pluck('entidad_tipo');

        $registros = AuditoriaCambio::with('usuario.colaborador')
            ->when($request->filled('entidad'), fn($q) => $q->where('entidad_tipo', $request->entidad))
            ->when($request->filled('accion'), fn($q) => $q->where('accion', $request->accion))
            ->when($request->filled('desde'), fn($q) => $q->whereDate('creado_en', '>=', $request->desde))
            ->orderByDesc('id_auditoria')
            ->paginate(50)
            ->withQueryString();

        return view('content.auditoria.index', compact('registros', 'entidades'));
    }
}
