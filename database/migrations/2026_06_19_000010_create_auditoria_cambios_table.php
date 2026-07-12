<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trazabilidad genérica de cambios sensibles (registro/edición de activo,
     * cambio de ubicación/responsable/condición/situación, ejecución de
     * movimientos, devoluciones, mantenimiento, baja, sincronización OCS).
     * Referencia polimórfica: (entidad_tipo, entidad_id).
     */
    public function up(): void
    {
        Schema::create('auditoria_cambios', function (Blueprint $table) {
            $table->bigIncrements('id_auditoria');

            $table->string('entidad_tipo', 80);
            $table->integer('entidad_id');

            $table->enum('accion', [
                'CREAR', 'ACTUALIZAR', 'ELIMINAR', 'EJECUTAR',
                'CANCELAR', 'CERRAR', 'SINCRONIZAR', 'OTRO',
            ])->default('OTRO');

            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->text('motivo')->nullable();

            $table->integer('id_usuario')->nullable();
            $table->string('ip', 60)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->dateTime('creado_en')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index(['entidad_tipo', 'entidad_id'], 'idx_auditoria_entidad');
            $table->index('id_usuario');
            $table->index('creado_en');
            $table->index('accion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_cambios');
    }
};
