<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos de trazabilidad para el flujo simplificado de bajas:
 *   - ejecutado_por / rechazado_por: usuario que ejecuta o rechaza la propuesta.
 *   - motivo_rechazo / fecha_rechazo: sustento y fecha del rechazo.
 * La fecha de ejecución sigue usando la columna existente `fecha_baja`
 * (no se duplica). Migración ADITIVA: no altera columnas ni datos existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bajas_activo', function (Blueprint $table) {
            $table->integer('ejecutado_por')->nullable()->after('validado_por');
            $table->integer('rechazado_por')->nullable()->after('ejecutado_por');
            $table->date('fecha_rechazo')->nullable()->after('fecha_baja');
            $table->text('motivo_rechazo')->nullable()->after('observaciones');

            $table->foreign('ejecutado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();
            $table->foreign('rechazado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bajas_activo', function (Blueprint $table) {
            $table->dropForeign(['ejecutado_por']);
            $table->dropForeign(['rechazado_por']);
            $table->dropColumn(['ejecutado_por', 'rechazado_por', 'fecha_rechazo', 'motivo_rechazo']);
        });
    }
};
