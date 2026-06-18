<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detalle de un movimiento: qué activos abarca y su condición de
     * salida/entrada. Un par (movimiento, activo) es único.
     */
    public function up(): void
    {
        Schema::create('detalle_movimiento_activo', function (Blueprint $table) {
            $table->integer('id_detalle_movimiento')->autoIncrement();

            $table->integer('id_movimiento');
            $table->integer('id_activo');
            $table->unsignedInteger('condicion_salida_id')->nullable();
            $table->unsignedInteger('condicion_entrada_id')->nullable();
            $table->string('observaciones', 255)->nullable();

            $table->dateTime('creado_en')->useCurrent();

            $table->foreign('id_movimiento')->references('id_movimiento')->on('movimientos')->cascadeOnDelete();
            $table->foreign('id_activo')->references('id_activo')->on('activo')->cascadeOnDelete();
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
