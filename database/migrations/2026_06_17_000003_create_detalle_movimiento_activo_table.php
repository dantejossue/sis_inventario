<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detalle de un movimiento: por cada activo, su origen y destino (responsable
     * y ubicación) y la condición física al salir/entrar. Un par (movimiento,
     * activo) es único (obs #2). Condición salida/entrada conservada (obs #4).
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

            $table->unsignedInteger('condicion_salida_id')->nullable();
            $table->unsignedInteger('condicion_entrada_id')->nullable();

            $table->enum('estado_revision', ['PENDIENTE', 'CONFORME', 'OBSERVADO'])->default('PENDIENTE');
            $table->string('observaciones', 255)->nullable();

            $table->dateTime('creado_en')->useCurrent();

            $table->foreign('id_movimiento')->references('id_movimiento')->on('movimientos')->cascadeOnDelete();
            $table->foreign('id_activo')->references('id_activo')->on('activo')->cascadeOnDelete();
            $table->foreign('id_responsable_origen')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('id_responsable_destino')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('id_ubicacion_origen')->references('id_ubicacion')->on('ubicaciones')->nullOnDelete();
            $table->foreign('id_ubicacion_destino')->references('id_ubicacion')->on('ubicaciones')->nullOnDelete();
            $table->foreign('condicion_salida_id')->references('id_estado_activo')->on('estado_activo')->nullOnDelete();
            $table->foreign('condicion_entrada_id')->references('id_estado_activo')->on('estado_activo')->nullOnDelete();

            $table->unique(['id_movimiento', 'id_activo'], 'uk_detalle_movimiento_activo');
            $table->index('id_activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_movimiento_activo');
    }
};
