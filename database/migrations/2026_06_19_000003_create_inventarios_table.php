<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->integer('id_inventario')->autoIncrement();

            $table->string('nombre', 150);
            $table->enum('tipo', ['ANUAL', 'EXTRAORDINARIO', 'POR_SEDE', 'POR_DEPENDENCIA', 'POR_RESPONSABLE', 'TI'])->default('ANUAL');

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->enum('estado', ['PLANIFICADO', 'EN_CAMPO', 'EN_GABINETE', 'CERRADO', 'CANCELADO'])->default('PLANIFICADO');

            $table->integer('responsable')->nullable();
            $table->text('observaciones')->nullable();

            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('responsable')->references('id_usuario')->on('usuarios')->nullOnDelete();
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
