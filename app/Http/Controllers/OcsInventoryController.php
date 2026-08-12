<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Services\OcsInventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Throwable;

class OcsInventoryController extends Controller
{
  /**
   * Muestra el layout independiente de consulta OCS.
   */
  public function show(Activo $activo): View
  {
    return view(
      'content.activos.ocs',
      compact('activo')
    );
  }

  /**
   * Laravel consulta la API externa y devuelve el JSON
   * al JavaScript de la página.
   */
  public function datos(
    Activo $activo,
    OcsInventoryService $ocs
  ): JsonResponse {
    $codigoPatrimonial = trim(
      (string) $activo->codigo_patrimonial
    );

    if ($codigoPatrimonial === '') {
      return response()->json([
        'success' => false,
        'message' =>
        'El activo no tiene un código patrimonial registrado.',
      ], 422);
    }

    try {
      $datos = $ocs->obtenerPorCodigoPatrimonial(
        $codigoPatrimonial
      );

      return response()->json([
        'success' => true,
        'message' =>
        'Información obtenida desde OCS Inventory.',
        'data' => $datos,
      ]);
    } catch (Throwable $exception) {
      report($exception);

      return response()->json([
        'success' => false,
        'message' => $exception->getMessage(),
      ], 503);
    }
  }
}
