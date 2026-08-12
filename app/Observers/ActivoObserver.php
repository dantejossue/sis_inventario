<?php

namespace App\Observers;

use App\Models\Activo;
use App\Models\HistorialCondicionActivo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Escribe el historial de condición física del activo. Es la ÚNICA vía de
 * escritura del historial: los flujos solo marcan el contexto (origen, hecho
 * relacionado, motivo) con Activo::marcarOrigenCondicion() antes de guardar, y
 * aquí se registra una fila por transición. Si un cambio llega sin contexto
 * (edición suelta), se registra igual con origen OTRO como red de seguridad.
 *
 * Toda escritura es best-effort: si falla, se loguea pero NUNCA interrumpe la
 * operación de negocio sobre el activo.
 */
class ActivoObserver
{
    /** Condición inicial del activo al crearse (sin condición anterior). */
    public function created(Activo $activo): void
    {
        $ctx = $activo->consumirContextoCondicion();
        $this->registrar($activo, null, $activo->condicion_actual, $ctx['origen'] ?? 'REGISTRO', $ctx);
    }

    /** Cada transición real de la condición física. */
    public function updated(Activo $activo): void
    {
        // Consume el contexto siempre para no arrastrarlo a un guardado futuro.
        $ctx = $activo->consumirContextoCondicion();

        if (! $activo->wasChanged('condicion_actual')) {
            return;
        }

        $this->registrar(
            $activo,
            $activo->getOriginal('condicion_actual'),
            $activo->condicion_actual,
            $ctx['origen'] ?? 'OTRO',
            $ctx,
        );
    }

    private function registrar(Activo $activo, ?string $anterior, ?string $nueva, string $origen, ?array $ctx): void
    {
        if (! $nueva) {
            return; // sin condición nueva no hay nada que historiar
        }

        try {
            HistorialCondicionActivo::create([
                'id_activo'           => $activo->id_activo,
                'condicion_anterior'  => $anterior,
                'condicion_nueva'     => $nueva,
                'origen'              => $origen,
                'entidad_origen_tipo' => $ctx['entidad_tipo'] ?? null,
                'entidad_origen_id'   => $ctx['entidad_id'] ?? null,
                'motivo'              => $ctx['motivo'] ?? null,
                'registrado_por'      => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar historial de condición: ' . $e->getMessage(), [
                'id_activo' => $activo->id_activo ?? null,
                'origen'    => $origen,
            ]);
        }
    }
}
