<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Simplifica el ciclo de mantenimiento a 4 estados de proceso
 * (REGISTRADO, EN_ATENCION, FINALIZADO, CANCELADO) e introduce una columna
 * independiente `resultado_atencion` (OPERATIVO | RECOMENDADO_BAJA).
 *
 * Migración de datos SEGURA (no borra tablas ni datos):
 *   - Deriva `resultado_atencion` desde los estados históricos ANTES de remapear.
 *   - Remapea los estados antiguos a los nuevos.
 *   - Sincroniza `recomienda_baja` con `resultado_atencion`.
 *   - Estrecha el enum al conjunto final.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Columna de resultado técnico, independiente del estado de proceso.
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->enum('resultado_atencion', ['OPERATIVO', 'RECOMENDADO_BAJA'])
                ->nullable()
                ->after('resultado');
        });

        // 2) Derivar resultado_atencion desde los estados históricos (antes de remapear estado).
        DB::table('mantenimientos')->where('estado', 'ATENDIDO')
            ->update(['resultado_atencion' => 'OPERATIVO']);
        DB::table('mantenimientos')->where('estado', 'SIN_REPARACION')
            ->update(['resultado_atencion' => 'RECOMENDADO_BAJA']);
        DB::table('mantenimientos')->where('estado', 'RECOMENDADO_BAJA')
            ->update(['resultado_atencion' => 'RECOMENDADO_BAJA']);
        // CERRADO: se determina con recomienda_baja.
        DB::table('mantenimientos')->where('estado', 'CERRADO')->where('recomienda_baja', true)
            ->update(['resultado_atencion' => 'RECOMENDADO_BAJA']);
        DB::table('mantenimientos')->where('estado', 'CERRADO')->where('recomienda_baja', false)
            ->update(['resultado_atencion' => 'OPERATIVO']);

        // 3) Ampliar el enum a un superconjunto (viejos + nuevos) para poder actualizar sin perder filas.
        DB::statement("ALTER TABLE mantenimientos MODIFY estado ENUM(
            'SOLICITADO','EN_REVISION','EN_MANTENIMIENTO','DERIVADO_PROVEEDOR',
            'ATENDIDO','SIN_REPARACION','RECOMENDADO_BAJA','CERRADO','CANCELADO',
            'REGISTRADO','EN_ATENCION','FINALIZADO'
        ) NOT NULL DEFAULT 'SOLICITADO'");

        // 4) Remapear estados históricos → nuevos.
        DB::table('mantenimientos')->where('estado', 'SOLICITADO')
            ->update(['estado' => 'REGISTRADO']);
        DB::table('mantenimientos')->whereIn('estado', ['EN_REVISION', 'EN_MANTENIMIENTO', 'DERIVADO_PROVEEDOR'])
            ->update(['estado' => 'EN_ATENCION']);
        DB::table('mantenimientos')->whereIn('estado', ['ATENDIDO', 'SIN_REPARACION', 'RECOMENDADO_BAJA', 'CERRADO'])
            ->update(['estado' => 'FINALIZADO']);
        // CANCELADO se conserva sin cambios.

        // 5) Sincronizar recomienda_baja con el resultado técnico.
        DB::table('mantenimientos')->where('resultado_atencion', 'RECOMENDADO_BAJA')
            ->update(['recomienda_baja' => true]);
        DB::table('mantenimientos')->where('resultado_atencion', 'OPERATIVO')
            ->update(['recomienda_baja' => false]);

        // 6) Estrechar el enum al conjunto final de 4 estados.
        DB::statement("ALTER TABLE mantenimientos MODIFY estado ENUM(
            'REGISTRADO','EN_ATENCION','FINALIZADO','CANCELADO'
        ) NOT NULL DEFAULT 'REGISTRADO'");
    }

    public function down(): void
    {
        // Reabrir el enum a un superconjunto para revertir el remapeo.
        DB::statement("ALTER TABLE mantenimientos MODIFY estado ENUM(
            'SOLICITADO','EN_REVISION','EN_MANTENIMIENTO','DERIVADO_PROVEEDOR',
            'ATENDIDO','SIN_REPARACION','RECOMENDADO_BAJA','CERRADO','CANCELADO',
            'REGISTRADO','EN_ATENCION','FINALIZADO'
        ) NOT NULL DEFAULT 'SOLICITADO'");

        // Reversión aproximada (los estados originales finos no se pueden reconstruir con exactitud).
        DB::table('mantenimientos')->where('estado', 'REGISTRADO')
            ->update(['estado' => 'SOLICITADO']);
        DB::table('mantenimientos')->where('estado', 'EN_ATENCION')
            ->update(['estado' => 'EN_MANTENIMIENTO']);
        DB::table('mantenimientos')->where('estado', 'FINALIZADO')->where('recomienda_baja', true)
            ->update(['estado' => 'RECOMENDADO_BAJA']);
        DB::table('mantenimientos')->where('estado', 'FINALIZADO')->where('recomienda_baja', false)
            ->update(['estado' => 'ATENDIDO']);

        // Estrechar al enum original.
        DB::statement("ALTER TABLE mantenimientos MODIFY estado ENUM(
            'SOLICITADO','EN_REVISION','EN_MANTENIMIENTO','DERIVADO_PROVEEDOR',
            'ATENDIDO','SIN_REPARACION','RECOMENDADO_BAJA','CERRADO','CANCELADO'
        ) NOT NULL DEFAULT 'SOLICITADO'");

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropColumn('resultado_atencion');
        });
    }
};
