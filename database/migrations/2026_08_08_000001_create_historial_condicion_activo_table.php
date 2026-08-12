<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial de la condición física del activo (NUEVO/BUENO/REGULAR/MALO).
     * A diferencia de la situación (que se deriva de movimientos/mantenimientos/
     * bajas), la condición cambia también por edición directa y como efecto de
     * otros flujos sin dejar traza propia. Esta tabla registra cada transición,
     * con el origen y (opcionalmente) el hecho que la causó.
     */
    public function up(): void
    {
        Schema::create('historial_condicion_activo', function (Blueprint $table) {
            $table->bigIncrements('id_historial');

            $table->integer('id_activo');

            $condiciones = ['NUEVO', 'BUENO', 'REGULAR', 'MALO'];
            // Null solo en el alta (no hay condición previa).
            $table->enum('condicion_anterior', $condiciones)->nullable();
            $table->enum('condicion_nueva', $condiciones);

            $table->enum('origen', [
                'REGISTRO', 'EDICION_MANUAL', 'DEVOLUCION', 'MANTENIMIENTO',
                'BAJA', 'INVENTARIO', 'REGULARIZACION', 'OTRO',
            ])->default('OTRO');

            // Referencia polimórfica opcional al hecho que originó el cambio
            // (p. ej. MOVIMIENTO/123, MANTENIMIENTO/45, BAJA/7).
            $table->string('entidad_origen_tipo', 40)->nullable();
            $table->integer('entidad_origen_id')->nullable();

            $table->text('motivo')->nullable();

            $table->integer('registrado_por')->nullable();

            $table->dateTime('creado_en')->useCurrent();

            $table->foreign('id_activo')->references('id_activo')->on('activo')->cascadeOnDelete();
            $table->foreign('registrado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('id_activo');
            $table->index('origen');
            $table->index('creado_en');
            $table->index(['entidad_origen_tipo', 'entidad_origen_id'], 'idx_hist_cond_entidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_condicion_activo');
    }
};
