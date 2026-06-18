<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->integer('id_rol');
            $table->integer('id_permiso');

            $table->primary(['id_rol', 'id_permiso']);

            $table->foreign('id_rol')
                ->references('id_rol')->on('roles')
                ->cascadeOnDelete();

            $table->foreign('id_permiso')
                ->references('id_permiso')->on('permisos')
                ->cascadeOnDelete();
        });

        // ADMINISTRADOR recibe todos los permisos
        $adminId   = DB::table('roles')->where('nombre', 'ADMINISTRADOR')->value('id_rol');
        $permisoIds = DB::table('permisos')->pluck('id_permiso');

        if ($adminId) {
            foreach ($permisoIds as $idPermiso) {
                DB::table('rol_permiso')->insert([
                    'id_rol'     => $adminId,
                    'id_permiso' => $idPermiso,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_permiso');
    }
};
