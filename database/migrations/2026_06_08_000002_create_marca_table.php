<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marca', function (Blueprint $table) {
            $table->increments('id_marca');
            $table->string('nombre', 100)->unique();
            $table->string('estado', 20)->default('ACTIVO');
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();
        });

        // Seed data
        $marcas = [
            'HP', 'DELL', 'LENOVO', 'APPLE', 'SAMSUNG',
            'LG', 'EPSON', 'CANON', 'CISCO', 'HUAWEI',
            'ACER', 'ASUS', 'TOSHIBA', 'BROTHER', 'XEROX',
        ];

        $now = now();
        foreach ($marcas as $nombre) {
            DB::table('marca')->insert([
                'nombre'    => $nombre,
                'estado'    => 'ACTIVO',
                'creado_en' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marca');
    }
};
