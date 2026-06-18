<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modelo', function (Blueprint $table) {
            $table->increments('id_modelo');
            $table->unsignedInteger('id_marca');
            $table->unsignedInteger('id_categoria')->nullable();
            $table->string('nombre', 150);
            $table->string('descripcion', 255)->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_marca')->references('id_marca')->on('marca');
            $table->foreign('id_categoria')->references('id_categoria')->on('categoria_activo');

            $table->unique(['id_marca', 'id_categoria', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modelo');
    }
};
