<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cabecera de una corrida de importación (SIGA / Excel / manual). Cada activo
     * importado referencia su importación de origen vía activo.id_importacion.
     */
    public function up(): void
    {
        Schema::create('importaciones_siga', function (Blueprint $table) {
            $table->integer('id_importacion')->autoIncrement();

            $table->string('nombre_archivo', 255)->nullable();
            $table->enum('tipo_importacion', ['SIGA', 'EXCEL', 'MANUAL'])->default('SIGA');

            $table->integer('total_registros')->default(0);
            $table->integer('registros_correctos')->default(0);
            $table->integer('registros_observados')->default(0);

            $table->enum('estado', ['PROCESANDO', 'COMPLETADO', 'COMPLETADO_CON_OBSERVACIONES', 'ERROR'])->default('PROCESANDO');

            $table->integer('importado_por')->nullable();
            $table->dateTime('creado_en')->useCurrent();

            $table->foreign('importado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importaciones_siga');
    }
};
