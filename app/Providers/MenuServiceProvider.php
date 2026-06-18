<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('layouts/sections/menu/verticalMenu', function ($view) {
            $allMenu    = json_decode(file_get_contents(base_path('resources/menu/verticalMenu.json')));
            $rolActual  = optional(optional(Auth::user())->rol)->nombre;

            $filtrado   = $this->filtrarPorRol($allMenu->menu, $rolActual);

            $menuData   = [(object)['menu' => $filtrado]];
            $view->with('menuData', $menuData);
        });
    }

    private function filtrarPorRol(array $items, ?string $rol): array
    {
        $visibles = [];

        foreach ($items as $item) {
            // Si tiene restricción de roles, verificar
            if (isset($item->roles) && $rol !== null && !in_array($rol, $item->roles)) {
                continue;
            }

            $visibles[] = $item;
        }

        // Eliminar headers que quedaron sin ítems debajo
        return $this->limpiarHeaders($visibles);
    }

    private function limpiarHeaders(array $items): array
    {
        $resultado = [];
        $totalItems = count($items);

        for ($i = 0; $i < $totalItems; $i++) {
            $item = $items[$i];

            if (!isset($item->menuHeader)) {
                $resultado[] = $item;
                continue;
            }

            // Solo agregar el header si hay al menos un ítem (no-header) después
            $hayContenido = false;
            for ($j = $i + 1; $j < $totalItems; $j++) {
                if (!isset($items[$j]->menuHeader)) {
                    $hayContenido = true;
                    break;
                }
                break; // siguiente ítem es otro header → este header queda vacío
            }

            if ($hayContenido) {
                $resultado[] = $item;
            }
        }

        return $resultado;
    }
}
