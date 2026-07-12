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

            // Ícono representativo (clase Boxicons) que identifica visualmente el
            // tipo de activo en cards, listados y ficha.
            $table->string('icono', 50)->nullable();

            // Marca qué categorías despliegan la ficha técnica (activo_tecnico):
            // equipos de cómputo/red sí; periféricos y eléctricos no.
            $table->boolean('requiere_ficha_tecnica')->default(true);

            $table->string('estado', 20)->default('ACTIVO');
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();
        });

        // Seed: [nombre, requiere_ficha_tecnica, icono]
        $categorias = [
            ['LAPTOP', true, 'bx-laptop'],
            ['CPU', true, 'bx-desktop'],
            ['SERVIDOR', true, 'bx-server'],
            ['SWITCH', true, 'bx-network-chart'],
            ['ROUTER', true, 'bx-wifi'],
            ['ACCESS POINT', true, 'bx-broadcast'],
            ['MONITOR', false, 'bx-tv'],
            ['IMPRESORA', false, 'bx-printer'],
            ['PROYECTOR', false, 'bx-video-recording'],
            ['UPS', false, 'bx-plug'],
            ['ESTABILIZADOR', false, 'bx-plug'],
        ];

        $now = now();
        DB::table('categoria_activo')->insert(array_map(fn($c) => [
            'nombre'                 => $c[0],
            'requiere_ficha_tecnica' => $c[1],
            'icono'                  => $c[2],
            'estado'                 => 'ACTIVO',
            'creado_en'              => $now,
        ], $categorias));
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_activo');
    }
};
