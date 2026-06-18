<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sede_dependencia', function (Blueprint $table) {
            $table->integer('id_sede_dependencia')->autoIncrement();
            $table->integer('id_sede');
            $table->integer('id_dependencia');
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            // Definición de las Llaves Foráneas (Relaciones)
            $table->foreign('id_sede')->references('id_sede')->on('sedes');
            $table->foreign('id_dependencia')->references('id_dependencia')->on('dependencias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sede_dependencia');
    }
};
