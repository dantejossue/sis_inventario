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
            $table->enum('tipo', ['CONDICION', 'SITUACION']);
            $table->string('codigo', 40);
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->unique(['tipo', 'codigo'], 'uk_estado_activo_tipo_codigo');
        });

        // CONDICION: estado físico del activo.
        $condiciones = [
            ['BUENO', 'Buen estado'],
            ['REGULAR', 'Estado regular'],
            ['MALO', 'Mal estado'],
            ['RAEE', 'Residuo de aparato eléctrico/electrónico'],
            ['CHATARRA', 'Sin valor de uso ni recuperación'],
        ];

        // SITUACION: estado operativo/logístico (máquina de estados del activo).
        $situaciones = [
            ['EN_USO', 'En uso por un responsable'],
            ['EN_ALMACEN', 'Disponible en almacén'],
            ['EN_MANTENIMIENTO', 'En mantenimiento'],
            ['EN_DESPLAZAMIENTO', 'En tránsito / desplazamiento'],
            ['PENDIENTE_BAJA', 'Pendiente de baja'],
            ['DADO_DE_BAJA', 'Dado de baja (terminal)'],
        ];

        $now = now();
        $filas = [];
        foreach ($condiciones as [$codigo, $desc]) {
            $filas[] = ['tipo' => 'CONDICION', 'codigo' => $codigo, 'nombre' => $codigo, 'descripcion' => $desc, 'estado' => 'ACTIVO', 'creado_en' => $now];
        }
        foreach ($situaciones as [$codigo, $desc]) {
            $filas[] = ['tipo' => 'SITUACION', 'codigo' => $codigo, 'nombre' => $codigo, 'descripcion' => $desc, 'estado' => 'ACTIVO', 'creado_en' => $now];
        }
        DB::table('estado_activo')->insert($filas);
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_activo');
    }
};
