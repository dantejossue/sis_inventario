<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cabecera de movimientos. Agrupa uno o varios activos (ver
     * detalle_movimiento_activo). El origen/destino vive POR ACTIVO en el
     * detalle, no en la cabecera. El folio legible es codigo_movimiento (obs #3).
     */
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->integer('id_movimiento')->autoIncrement();

            $table->string('codigo_movimiento', 50)->unique('uk_movimiento_codigo');

            $table->enum('tipo', [
                'ASIGNACION', 'TRANSFERENCIA', 'ORDEN_SALIDA', 'REINGRESO',
                'DESPLAZAMIENTO_INTERNO', 'PRESTAMO_TEMPORAL', 'DEVOLUCION_INTERNA', 'REGULARIZACION',
            ]);

            $table->text('motivo')->nullable();

            $table->enum('estado', [
                'REGISTRADO', 'PENDIENTE_TRAMITE', 'EN_TRAMITE',
                'AUTORIZADO', 'EJECUTADO', 'RECHAZADO', 'CANCELADO',
            ])->default('REGISTRADO');

            $table->integer('registrado_por')->nullable();
            $table->integer('validado_oti_por')->nullable();
            $table->integer('validado_patrimonio_por')->nullable();

            $table->dateTime('fecha_registro')->useCurrent();
            $table->dateTime('fecha_movimiento')->nullable();

            $table->boolean('requiere_tramite')->default(false);

            $table->enum('estado_siga', ['NO_APLICA', 'PENDIENTE_ACTUALIZACION', 'REGISTRADO', 'OBSERVADO'])->default('NO_APLICA');
            $table->dateTime('fecha_registro_siga')->nullable();
            $table->string('observacion_siga', 255)->nullable();

            $table->text('observaciones')->nullable();

            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('registrado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();
            $table->foreign('validado_oti_por')->references('id_usuario')->on('usuarios')->nullOnDelete();
            $table->foreign('validado_patrimonio_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('tipo');
            $table->index('estado');
            $table->index('estado_siga');
            $table->index('fecha_movimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
