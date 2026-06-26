<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saneamientos', function (Blueprint $table) {
            $table->integer('id_saneamiento')->autoIncrement();
            $table->integer('id_activo')->nullable();

            $table->enum('tipo', [
                'SOBRANTE', 'FALTANTE', 'UBICACION_INCONSISTENTE', 'RESPONSABLE_INCONSISTENTE',
                'SIN_ETIQUETA', 'DOCUMENTACION_INCOMPLETA', 'OTRO',
            ]);
            $table->text('descripcion')->nullable();

            $table->integer('ubicacion_detectada')->nullable();
            $table->integer('responsable_detectado')->nullable();
            $table->integer('iniciado_por')->nullable();

            $table->enum('estado', ['ABIERTO', 'EN_REVISION', 'REGULARIZADO', 'DERIVADO_A_BAJA', 'CERRADO'])->default('ABIERTO');
            $table->enum('estado_siga', ['NO_APLICA', 'PENDIENTE_ACTUALIZACION', 'REGISTRADO', 'OBSERVADO'])->default('NO_APLICA');

            $table->text('resultado')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_cierre')->nullable();

            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_activo')->references('id_activo')->on('activo')->nullOnDelete();
            $table->foreign('ubicacion_detectada')->references('id_ubicacion')->on('ubicaciones')->nullOnDelete();
            $table->foreign('responsable_detectado')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('iniciado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saneamientos');
    }
};
