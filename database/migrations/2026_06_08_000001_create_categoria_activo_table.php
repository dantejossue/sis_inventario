<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_activo', function (Blueprint $table) {
            $table->increments('id_categoria');
            $table->string('nombre', 150)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();
        });

        // Seed data
        $categorias = [
            'LAPTOP', 'DESKTOP', 'MONITOR', 'IMPRESORA', 'TECLADO',
            'MOUSE', 'PROYECTOR', 'SCANNER', 'UPS', 'SERVIDOR',
            'SWITCH', 'ROUTER', 'TABLET', 'CELULAR', 'DISCO_EXTERNO',
        ];

        $now = now();
        foreach ($categorias as $nombre) {
            DB::table('categoria_activo')->insert([
                'nombre'     => $nombre,
                'descripcion' => null,
                'estado'     => 'ACTIVO',
                'creado_en'  => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_activo');
    }
};
