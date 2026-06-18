<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->integer('id_permiso')->autoIncrement();
            $table->string('codigo', 100)->unique('uk_permiso_codigo');
            $table->string('nombre', 150);
            $table->string('descripcion', 255)->nullable();
        });

        DB::table('permisos')->insert([
            // Activos TI
            ['codigo' => 'activos.ver',              'nombre' => 'Ver activos TI'],
            ['codigo' => 'activos.crear',            'nombre' => 'Registrar activo'],
            ['codigo' => 'activos.editar',           'nombre' => 'Editar activo'],
            ['codigo' => 'activos.dar_baja',         'nombre' => 'Dar de baja activo'],
            // Catálogos
            ['codigo' => 'catalogos.ver',            'nombre' => 'Ver catálogos'],
            ['codigo' => 'catalogos.gestionar',      'nombre' => 'Gestionar catálogos'],
            // Movimientos
            ['codigo' => 'movimientos.ver',          'nombre' => 'Ver movimientos'],
            ['codigo' => 'movimientos.gestionar',    'nombre' => 'Registrar movimiento'],
            // Mantenimientos
            ['codigo' => 'mantenimientos.ver',       'nombre' => 'Ver mantenimientos'],
            ['codigo' => 'mantenimientos.gestionar', 'nombre' => 'Registrar mantenimiento'],
            // Colaboradores
            ['codigo' => 'colaboradores.ver',        'nombre' => 'Ver colaboradores'],
            ['codigo' => 'colaboradores.gestionar',  'nombre' => 'Gestionar colaboradores'],
            // Usuarios
            ['codigo' => 'usuarios.ver',             'nombre' => 'Ver usuarios'],
            ['codigo' => 'usuarios.gestionar',       'nombre' => 'Gestionar usuarios'],
            // Roles
            ['codigo' => 'roles.ver',                'nombre' => 'Ver roles y permisos'],
            ['codigo' => 'roles.gestionar',          'nombre' => 'Gestionar roles y permisos'],
            // Auditoría
            ['codigo' => 'auditoria.ver',            'nombre' => 'Ver auditoría de cambios'],
            // Reportes
            ['codigo' => 'reportes.ver',             'nombre' => 'Ver reportes y KPIs'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};
