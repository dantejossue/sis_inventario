<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entregas_cargo', function (Blueprint $table) {
            $table->integer('id_entrega_cargo')->autoIncrement();

            $table->integer('id_colaborador_saliente');
            $table->integer('id_colaborador_entrante')->nullable();

            $table->enum('motivo', ['CESE', 'CAMBIO_AREA', 'VACACIONES', 'LICENCIA', 'ROTACION', 'OTRO'])->default('OTRO');
            $table->enum('estado', ['ABIERTA', 'EN_REVISION', 'OBSERVADA', 'CERRADA', 'CANCELADA'])->default('ABIERTA');

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_cierre')->nullable();

            $table->integer('registrado_por')->nullable();
            $table->text('observaciones')->nullable();

            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_colaborador_saliente')->references('id_colaborador')->on('colaboradores')->restrictOnDelete();
            $table->foreign('id_colaborador_entrante')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('registrado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entregas_cargo');
    }
};
