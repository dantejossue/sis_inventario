<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración ADITIVA: metadatos del archivo físico en documentos_adjuntos
 * (nombre original, extensión y tamaño) para descargas amigables y validación.
 * No altera columnas existentes ni datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_adjuntos', function (Blueprint $table) {
            $table->string('nombre_original', 255)->nullable()->after('archivo');
            $table->string('extension', 20)->nullable()->after('nombre_original');
            $table->unsignedInteger('tamano_kb')->nullable()->after('extension');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_adjuntos', function (Blueprint $table) {
            $table->dropColumn(['nombre_original', 'extension', 'tamano_kb']);
        });
    }
};
