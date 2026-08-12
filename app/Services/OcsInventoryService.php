<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OcsInventoryService
{
  /**
   * Consulta un dispositivo OCS usando el TAG,
   * que en este sistema corresponde al código patrimonial.
   */
  public function obtenerPorCodigoPatrimonial(string $codigo): array
  {
    $baseUrl = rtrim(
      (string) config('services.ocs.url'),
      '/'
    );

    if ($baseUrl === '') {
      throw new RuntimeException(
        'La URL de OCS Inventory no está configurada.'
      );
    }

    try {
      $response = Http::acceptJson()
        ->timeout(config('services.ocs.timeout', 15))
        ->get(
          $baseUrl
            . '/api/activos/'
            . rawurlencode($codigo)
        );
    } catch (ConnectionException $exception) {
      throw new RuntimeException(
        'No se pudo conectar con el servidor OCS Inventory.',
        previous: $exception
      );
    }

    if ($response->status() === 404) {
      throw new RuntimeException(
        "No se encontró un equipo en OCS con el TAG {$codigo}."
      );
    }

    if (! $response->successful()) {
      throw new RuntimeException(
        'OCS Inventory respondió con el código HTTP '
          . $response->status()
          . '.'
      );
    }

    $datos = $response->json();

    if (! is_array($datos)) {
      throw new RuntimeException(
        'OCS Inventory devolvió una respuesta no válida.'
      );
    }

    return $datos;
  }
}
