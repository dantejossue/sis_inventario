<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->enum(
                'modalidad_atencion',
                ['INTERNA_OTI', 'GARANTIA_PROVEEDOR']
            )
                ->default('INTERNA_OTI')
                ->after('tipo_mantenimiento');
        });
    }

    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropColumn('modalidad_atencion');
        });
    }
};
