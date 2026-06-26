<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detalle por fila de una importación: el dato crudo (datos_raw) y el
     * resultado de su validación. Si la fila generó un activo, lo referencia.
     */
    public function up(): void
    {
        Schema::create('importacion_siga_detalle', function (Blueprint $table) {
            $table->integer('id_importacion_detalle')->autoIncrement();
            $table->integer('id_importacion');
            $table->integer('id_activo')->nullable();

            $table->integer('fila_excel')->nullable();
            $table->string('codigo_patrimonial', 100)->nullable();
            $table->string('numero_serie', 150)->nullable();
            $table->string('denominacion', 255)->nullable();

            $table->enum('estado', ['CORRECTO', 'OBSERVADO', 'DUPLICADO', 'ERROR'])->default('CORRECTO');
            $table->string('mensaje', 255)->nullable();
            $table->json('datos_raw')->nullable();

            $table->dateTime('creado_en')->useCurrent();

            $table->foreign('id_importacion')->references('id_importacion')->on('importaciones_siga')->cascadeOnDelete();
            $table->foreign('id_activo')->references('id_activo')->on('activo')->nullOnDelete();

            $table->index('id_importacion');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importacion_siga_detalle');
    }
};
