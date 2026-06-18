<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activo', function (Blueprint $table) {
            // El PK de sede_dependencia es INT con signo; alineamos el tipo de la
            // columna (estaba como unsignedInteger) para poder crear la FK, ya que
            // MySQL exige que ambos extremos coincidan exactamente.
            $table->integer('id_ubicacion_actual')->nullable()->change();

            $table->foreign('id_ubicacion_actual', 'fk_activo_ubicacion')
                ->references('id_sede_dependencia')->on('sede_dependencia')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activo', function (Blueprint $table) {
            $table->dropForeign('fk_activo_ubicacion');
            $table->unsignedInteger('id_ubicacion_actual')->nullable()->change();
        });
    }
};
