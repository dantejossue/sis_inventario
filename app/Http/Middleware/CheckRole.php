<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Uso: Route::middleware('role:ADMINISTRADOR')
     *      Route::middleware('role:ADMINISTRADOR,ALMACEN')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (!$usuario || !$usuario->rol) {
            abort(403, 'No tiene un rol asignado.');
        }

        if (!in_array($usuario->rol->nombre, $roles)) {
            abort(403, 'No tiene permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
