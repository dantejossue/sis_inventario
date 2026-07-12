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

            // OTI registra, evalúa y valida internamente (no "solicita"/"aprueba"
            // como área externa). registrado_por = usuario; evaluado/validado = colaborador OTI.
            $table->integer('registrado_por')->nullable();
            $table->integer('evaluado_por')->nullable();
            $table->integer('validado_por')->nullable();

            $table->enum('causal_baja', [
                'DANO', 'OBSOLESCENCIA_TECNICA', 'MANTENIMIENTO_ONEROSO', 'SIN_REPARACION',
                'RAEE', 'SUSTRACCION', 'OTRO',
            ]);
            $table->enum('clasificacion_final', [
                'RAEE', 'CHATARRA', 'OBSOLETO', 'SIN_REPARACION', 'NO_DETERMINADO', 'OTRO',
            ])->nullable();

            $table->text('motivo')->nullable();
            $table->text('diagnostico_tecnico')->nullable();

            $table->string('numero_informe_tecnico', 100)->nullable();
            $table->string('numero_documento_validacion', 100)->nullable();
            $table->decimal('valor_referencial', 12, 2)->nullable();

            $table->enum('estado', [
                'REGISTRADA', 'EN_EVALUACION', 'RECOMENDADA', 'VALIDADA', 'RECHAZADA', 'EJECUTADA',
            ])->default('REGISTRADA');

            $table->date('fecha_registro')->nullable();
            $table->date('fecha_evaluacion')->nullable();
            $table->date('fecha_validacion')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->text('observaciones')->nullable();

            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_activo')->references('id_activo')->on('activo')->cascadeOnDelete();
            $table->foreign('id_mantenimiento_origen')->references('id_mantenimiento')->on('mantenimientos')->nullOnDelete();
            $table->foreign('registrado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();
            $table->foreign('evaluado_por')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('validado_por')->references('id_colaborador')->on('colaboradores')->nullOnDelete();

            $table->index('id_activo');
            $table->index('id_mantenimiento_origen');
            $table->index('estado');
            $table->index('causal_baja');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bajas_activo');
    }
};
