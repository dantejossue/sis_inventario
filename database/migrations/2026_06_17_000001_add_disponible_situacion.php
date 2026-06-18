<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Agrega la situación DISPONIBLE: estado inicial de un activo recién
     * registrado que aún no tiene colaborador asignado (listo para ASIGNAR).
     */
    public function up(): void
    {
        DB::table('estado_activo')->updateOrInsert(
            ['nombre' => 'DISPONIBLE', 'tipo_estado' => 'SITUACION'],
            ['descripcion' => 'Activo disponible para asignar', 'estado' => 'ACTIVO']
        );
    }

    public function down(): void
    {
        DB::table('estado_activo')
            ->where('nombre', 'DISPONIBLE')
            ->where('tipo_estado', 'SITUACION')
            ->delete();
    }
};
