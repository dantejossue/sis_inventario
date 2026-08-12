<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de avances técnicos de un mantenimiento. Cada avance es un registro
 * nuevo (no sobrescribe los anteriores). El diagnóstico "resumen" se mantiene en
 * mantenimientos.diagnostico, pero el detalle cronológico vive aquí.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimiento_avances', function (Blueprint $table) {
            $table->integer('id_avance')->autoIncrement();
            $table->integer('id_mantenimiento');

            $table->text('diagnostico')->nullable();
            $table->text('actividad_realizada')->nullable();
            $table->text('observacion')->nullable();
            $table->decimal('costo', 12, 2)->nullable();

            $table->integer('registrado_por')->nullable();

            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_mantenimiento')
                ->references('id_mantenimiento')->on('mantenimientos')->cascadeOnDelete();
            $table->foreign('registrado_por')
                ->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('id_mantenimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_avances');
    }
};
