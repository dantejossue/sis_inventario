<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_activo', function (Blueprint $table) {
            $table->increments('id_estado_activo');
            $table->string('nombre', 100);
            $table->enum('tipo_estado', ['CONDICION', 'SITUACION']);
            $table->string('descripcion', 255)->nullable();
            $table->string('estado', 20)->default('ACTIVO');

            $table->unique(['nombre', 'tipo_estado']);
        });

        // Seed: CONDICION
        $condiciones = ['BUENO', 'REGULAR', 'MALO', 'OBSOLETO'];
        foreach ($condiciones as $nombre) {
            DB::table('estado_activo')->insert([
                'nombre'      => $nombre,
                'tipo_estado' => 'CONDICION',
                'descripcion' => null,
                'estado'      => 'ACTIVO',
            ]);
        }

        // Seed: SITUACION
        $situaciones = ['OPERATIVO', 'EN_MANTENIMIENTO', 'EN_PRESTAMO', 'ASIGNADO', 'EN_ALMACEN', 'DADO_DE_BAJA'];
        foreach ($situaciones as $nombre) {
            DB::table('estado_activo')->insert([
                'nombre'      => $nombre,
                'tipo_estado' => 'SITUACION',
                'descripcion' => null,
                'estado'      => 'ACTIVO',
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_activo');
    }
};
