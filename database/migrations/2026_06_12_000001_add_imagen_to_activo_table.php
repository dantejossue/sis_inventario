<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activo', function (Blueprint $table) {
            $table->string('imagen', 255)->nullable()->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('activo', function (Blueprint $table) {
            $table->dropColumn('imagen');
        });
    }
};
