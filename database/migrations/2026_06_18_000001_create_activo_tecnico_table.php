<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ficha técnica TI de un activo (relación 1-a-1). Solo aplica a categorías
     * con requiere_ficha_tecnica = true (cómputo / red).
     */
    public function up(): void
    {
        Schema::create('activo_tecnico', function (Blueprint $table) {
            $table->integer('id_activo_tecnico')->autoIncrement();
            $table->integer('id_activo');

            $table->string('procesador', 150)->nullable();
            $table->string('memoria_ram', 60)->nullable();
            $table->string('almacenamiento', 100)->nullable();
            $table->enum('tipo_almacenamiento', ['HDD', 'SSD', 'NVME', 'EMMC', 'OTRO'])->nullable();
            $table->string('sistema_operativo', 100)->nullable();
            $table->string('direccion_mac', 60)->nullable();
            $table->string('direccion_ip', 60)->nullable();
            $table->string('nombre_equipo', 100)->nullable();
            $table->string('dominio', 100)->nullable();
            $table->string('licencia_office', 100)->nullable();
            $table->string('antivirus', 100)->nullable();
            $table->string('accesorios', 255)->nullable();
            $table->text('observaciones_tecnicas')->nullable();

            $table->enum('estado_operativo', [
                'OPERATIVO', 'INOPERATIVO', 'EN_REVISION',
                'EN_MANTENIMIENTO', 'PENDIENTE_BAJA', 'DADO_DE_BAJA',
            ])->default('OPERATIVO');

            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->unique('id_activo', 'uk_activo_tecnico_activo');
            $table->foreign('id_activo')->references('id_activo')->on('activo')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo_tecnico');
    }
};
