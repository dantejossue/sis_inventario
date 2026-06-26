<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    /**
     * Evita que el navegador guarde en caché las páginas protegidas.
     *
     * Sin esto, al presionar el botón "Atrás" tras cerrar sesión, el
     * navegador muestra una copia cacheada de la última página (p. ej. el
     * panel administrativo del usuario anterior) sin consultar al servidor.
     * Estas cabeceras obligan al navegador a volver a pedir la página,
     * con lo que el middleware 'auth' lo redirige al login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // headers->set() funciona en cualquier respuesta Symfony (incluidas las
        // descargas StreamedResponse/BinaryFileResponse), a diferencia del helper
        // ->header() que solo existe en Illuminate\Http\Response.
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
