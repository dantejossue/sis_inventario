<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_detalle', function (Blueprint $table) {
            $table->integer('id_inventario_detalle')->autoIncrement();
            $table->integer('id_inventario');
            $table->integer('id_activo')->nullable();

            $table->string('codigo_patrimonial', 100)->nullable();

            $table->enum('estado_verificacion', [
                'ENCONTRADO', 'FALTANTE', 'SOBRANTE', 'UBICACION_INCORRECTA',
                'RESPONSABLE_INCORRECTO', 'SIN_ETIQUETA', 'OBSERVADO',
            ])->default('ENCONTRADO');

            $table->integer('id_ubicacion_registrada')->nullable();
            $table->integer('id_ubicacion_encontrada')->nullable();
            $table->integer('id_responsable_registrado')->nullable();
            $table->integer('id_responsable_encontrado')->nullable();

            $table->string('condicion_encontrada', 60)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('foto', 255)->nullable();

            $table->enum('estado_siga', ['NO_APLICA', 'PENDIENTE_ACTUALIZACION', 'REGISTRADO', 'OBSERVADO'])->default('NO_APLICA');
            $table->integer('verificado_por')->nullable();
            $table->dateTime('fecha_verificacion')->nullable();

            $table->foreign('id_inventario')->references('id_inventario')->on('inventarios')->cascadeOnDelete();
            $table->foreign('id_activo')->references('id_activo')->on('activo')->nullOnDelete();
            $table->foreign('id_ubicacion_registrada')->references('id_ubicacion')->on('ubicaciones')->nullOnDelete();
            $table->foreign('id_ubicacion_encontrada')->references('id_ubicacion')->on('ubicaciones')->nullOnDelete();
            $table->foreign('id_responsable_registrado')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('id_responsable_encontrado')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('verificado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('id_inventario');
            $table->index('estado_verificacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_detalle');
    }
};
