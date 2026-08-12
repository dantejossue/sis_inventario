<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropColumn([
                'prioridad',
                'origen_reporte',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->enum(
                'prioridad',
                ['BAJA', 'MEDIA', 'ALTA', 'CRITICA']
            )->default('MEDIA');

            $table->enum(
                'origen_reporte',
                ['USUARIO', 'OTI', 'USG', 'INVENTARIO', 'OTRO']
            )->default('OTI');
        });
    }
};
