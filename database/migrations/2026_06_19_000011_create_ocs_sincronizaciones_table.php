<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log de cada proceso de sincronización con OCS Inventory. No reemplaza la
     * ficha técnica (activo_tecnico); solo registra el resultado del proceso.
     */
    public function up(): void
    {
        Schema::create('ocs_sincronizaciones', function (Blueprint $table) {
            $table->integer('id_sincronizacion')->autoIncrement();

            $table->dateTime('fecha_sincronizacion')->useCurrent();

            $table->integer('total_detectados')->default(0);
            $table->integer('total_vinculados')->default(0);
            $table->integer('total_actualizados')->default(0);
            $table->integer('total_con_diferencias')->default(0);
            $table->integer('total_errores')->default(0);

            $table->enum('estado', [
                'INICIADO', 'COMPLETADO', 'COMPLETADO_CON_OBSERVACIONES', 'ERROR',
            ])->default('INICIADO');

            $table->integer('ejecutado_por')->nullable();
            $table->text('observaciones')->nullable();

            $table->dateTime('creado_en')->useCurrent();

            $table->foreign('ejecutado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('fecha_sincronizacion');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocs_sincronizaciones');
    }
};
