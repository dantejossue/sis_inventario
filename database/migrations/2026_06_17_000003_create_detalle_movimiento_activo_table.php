<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detalle de un movimiento: por cada activo, su origen y destino (responsable
     * y ubicación), la condición física al salir/retornar y la situación antes/
     * después. Un par (movimiento, activo) es único. Condición y situación son
     * ENUM directos alineados con la tabla activo (brief OTI).
     */
    public function up(): void
    {
        Schema::create('detalle_movimiento_activo', function (Blueprint $table) {
            $table->integer('id_detalle_movimiento')->autoIncrement();

            $table->integer('id_movimiento');
            $table->integer('id_activo');

            $table->integer('id_responsable_origen')->nullable();
            $table->integer('id_responsable_destino')->nullable();
            $table->integer('id_ubicacion_origen')->nullable();
            $table->integer('id_ubicacion_destino')->nullable();

            $table->enum('condicion_salida', ['NUEVO', 'BUENO', 'REGULAR', 'MALO'])->nullable();
            $table->enum('condicion_retorno', ['NUEVO', 'BUENO', 'REGULAR', 'MALO'])->nullable();

            $situaciones = [
                'DISPONIBLE', 'EN_USO', 'EN_PRESTAMO', 'EN_MANTENIMIENTO',
                'EN_PROVEEDOR', 'OBSERVADO', 'DADO_DE_BAJA',
            ];
            $table->enum('situacion_anterior', $situaciones)->nullable();
            $table->enum('situacion_resultante', $situaciones)->nullable();

            $table->enum('resultado', [
                'PENDIENTE', 'APLICADO', 'DEVUELTO', 'DEVUELTO_OBSERVADO', 'OBSERVADO', 'CANCELADO',
            ])->default('PENDIENTE');

            $table->text('observacion_salida')->nullable();
            $table->text('observacion_retorno')->nullable();
            $table->text('observaciones')->nullable();

            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_movimiento')->references('id_movimiento')->on('movimientos')->cascadeOnDelete();
            $table->foreign('id_activo')->references('id_activo')->on('activo')->cascadeOnDelete();
            $table->foreign('id_responsable_origen')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('id_responsable_destino')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('id_ubicacion_origen')->references('id_ubicacion')->on('ubicaciones')->nullOnDelete();
            $table->foreign('id_ubicacion_destino')->references('id_ubicacion')->on('ubicaciones')->nullOnDelete();

            $table->unique(['id_movimiento', 'id_activo'], 'uk_detalle_movimiento_activo');
            $table->index('id_activo');
            $table->index('resultado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_movimiento_activo');
    }
};
