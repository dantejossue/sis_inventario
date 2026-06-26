<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bajas_activo', function (Blueprint $table) {
            $table->integer('id_baja')->autoIncrement();
            $table->integer('id_activo');
            $table->integer('id_mantenimiento_origen')->nullable();

            $table->integer('solicitado_por')->nullable();
            $table->integer('evaluado_por')->nullable();
            $table->integer('aprobado_por')->nullable();

            $table->enum('causal_baja', [
                'DANO', 'OBSOLESCENCIA_TECNICA', 'RAEE', 'MANTENIMIENTO_ONEROSO', 'SIN_REPARACION',
                'SANEAMIENTO_FALTANTE', 'SUSTRACCION', 'GARANTIA', 'OTRO',
            ]);
            $table->text('motivo')->nullable();
            $table->text('diagnostico_tecnico')->nullable();

            $table->enum('estado', ['SOLICITADA', 'EN_EVALUACION', 'RECOMENDADA', 'APROBADA', 'RECHAZADA', 'EJECUTADA'])->default('SOLICITADA');
            $table->enum('estado_siga', ['NO_APLICA', 'PENDIENTE_ACTUALIZACION', 'REGISTRADO', 'OBSERVADO'])->default('NO_APLICA');

            $table->date('fecha_solicitud')->nullable();
            $table->date('fecha_evaluacion')->nullable();
            $table->date('fecha_aprobacion')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->text('observaciones')->nullable();

            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_activo')->references('id_activo')->on('activo')->cascadeOnDelete();
            $table->foreign('id_mantenimiento_origen')->references('id_mantenimiento')->on('mantenimientos')->nullOnDelete();
            $table->foreign('solicitado_por')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('evaluado_por')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('aprobado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('id_activo');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bajas_activo');
    }
};
