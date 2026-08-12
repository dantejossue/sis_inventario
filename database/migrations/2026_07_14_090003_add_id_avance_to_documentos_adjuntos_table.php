<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración ADITIVA: referencia opcional al avance de mantenimiento al que
 * pertenece una evidencia. Es nullable y solo la usa el módulo de mantenimientos;
 * el resto de módulos (activos, movimientos, bajas…) la dejan en NULL sin verse
 * afectados. No altera columnas existentes ni datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_adjuntos', function (Blueprint $table) {
            $table->integer('id_avance')->nullable()->after('entidad_id');

            $table->foreign('id_avance')
                ->references('id_avance')->on('mantenimiento_avances')->nullOnDelete();

            $table->index('id_avance');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_adjuntos', function (Blueprint $table) {
            $table->dropForeign(['id_avance']);
            $table->dropIndex(['id_avance']);
            $table->dropColumn('id_avance');
        });
    }
};
