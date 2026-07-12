<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cabecera de movimientos internos OTI. Solo PRESTAMO, TRANSFERENCIA y
     * REGULARIZACION. La devolución NO es un tipo: se registra dentro del
     * préstamo (fecha_devolucion_* / estado_devolucion). El origen/destino vive
     * POR ACTIVO en detalle_movimiento_activo. Folio legible: codigo_movimiento.
     */
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->integer('id_movimiento')->autoIncrement();

            $table->string('codigo_movimiento', 50)->unique('uk_movimiento_codigo');

            $table->enum('tipo', ['PRESTAMO', 'TRANSFERENCIA', 'REGULARIZACION']);

            $table->enum('estado', ['BORRADOR', 'EJECUTADO', 'OBSERVADO', 'CANCELADO'])->default('BORRADOR');

            $table->dateTime('fecha_registro')->useCurrent();
            $table->dateTime('fecha_movimiento')->nullable();

            // ── Devolución (solo aplica a PRESTAMO) ───────────────────
            $table->date('fecha_devolucion_estimada')->nullable();
            $table->date('fecha_devolucion_real')->nullable();
            $table->enum('estado_devolucion', [
                'NO_APLICA', 'PENDIENTE_DEVOLUCION', 'DEVUELTO', 'DEVUELTO_OBSERVADO', 'VENCIDO',
            ])->default('NO_APLICA');

            $table->text('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('observacion_devolucion')->nullable();

            // ── Referencia documentaria ───────────────────────────────
            $table->boolean('requiere_tramite')->default(false);
            $table->string('numero_tramite', 100)->nullable();
            $table->string('documento_referencia', 150)->nullable();

            // ── Trazabilidad de ejecución/cancelación ─────────────────
            $table->integer('registrado_por')->nullable();
            $table->integer('ejecutado_por')->nullable();
            $table->integer('cancelado_por')->nullable();
            $table->dateTime('fecha_ejecucion')->nullable();
            $table->dateTime('fecha_cancelacion')->nullable();
            $table->text('motivo_cancelacion')->nullable();

            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('registrado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();
            $table->foreign('ejecutado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();
            $table->foreign('cancelado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('tipo');
            $table->index('estado');
            $table->index('estado_devolucion');
            $table->index('fecha_movimiento');
            $table->index('numero_tramite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
