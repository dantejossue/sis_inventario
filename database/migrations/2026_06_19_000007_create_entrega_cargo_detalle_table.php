<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrega_cargo_detalle', function (Blueprint $table) {
            $table->integer('id_entrega_cargo_detalle')->autoIncrement();
            $table->integer('id_entrega_cargo');
            $table->integer('id_activo');

            $table->enum('estado_revision', [
                'CONFORME', 'OBSERVADO', 'NO_ENTREGADO', 'TRANSFERIDO', 'DEVUELTO', 'PENDIENTE_SANEAMIENTO',
            ])->default('CONFORME');

            $table->integer('id_movimiento_generado')->nullable();
            $table->string('observaciones', 255)->nullable();

            $table->dateTime('creado_en')->useCurrent();

            $table->foreign('id_entrega_cargo')->references('id_entrega_cargo')->on('entregas_cargo')->cascadeOnDelete();
            $table->foreign('id_activo')->references('id_activo')->on('activo')->cascadeOnDelete();
            $table->foreign('id_movimiento_generado')->references('id_movimiento')->on('movimientos')->nullOnDelete();

            $table->index('id_entrega_cargo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrega_cargo_detalle');
    }
};
