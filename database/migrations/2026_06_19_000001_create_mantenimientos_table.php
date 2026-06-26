<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->integer('id_mantenimiento')->autoIncrement();
            $table->integer('id_activo');

            $table->integer('solicitado_por')->nullable();
            $table->integer('registrado_por')->nullable();
            $table->integer('tecnico_responsable')->nullable();

            $table->enum('tipo_mantenimiento', ['PREVENTIVO', 'CORRECTIVO', 'GARANTIA', 'REVISION_TECNICA'])->default('CORRECTIVO');
            $table->enum('origen_reporte', ['USUARIO', 'OTI', 'USG', 'INVENTARIO', 'OTRO'])->default('USUARIO');
            $table->enum('prioridad', ['BAJA', 'MEDIA', 'ALTA', 'CRITICA'])->default('MEDIA');

            $table->text('descripcion')->nullable();
            $table->text('diagnostico')->nullable();
            $table->string('proveedor', 150)->nullable();
            $table->decimal('costo', 12, 2)->nullable();

            $table->date('fecha_reporte')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->text('resultado')->nullable();
            $table->boolean('recomienda_baja')->default(false);

            $table->enum('estado', [
                'SOLICITADO', 'EN_REVISION', 'EN_MANTENIMIENTO', 'DERIVADO_PROVEEDOR',
                'ATENDIDO', 'SIN_REPARACION', 'RECOMENDADO_BAJA', 'CERRADO', 'CANCELADO',
            ])->default('SOLICITADO');

            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_activo')->references('id_activo')->on('activo')->cascadeOnDelete();
            $table->foreign('solicitado_por')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('registrado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();
            $table->foreign('tecnico_responsable')->references('id_colaborador')->on('colaboradores')->nullOnDelete();

            $table->index('id_activo');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
