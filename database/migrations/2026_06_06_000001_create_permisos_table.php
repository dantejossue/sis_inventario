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
            $table->string('modulo', 60);
            $table->string('accion', 60);
            $table->string('nombre', 150);
            $table->string('descripcion', 255)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->unique(['modulo', 'accion'], 'uk_permiso_modulo_accion');
        });

        // Catálogo de permisos por módulo/acción. La grilla de permisos se arma
        // agrupando por 'modulo'.
        $permisos = [
            // [modulo, accion, nombre]
            ['activos', 'ver', 'Ver activos TI'],
            ['activos', 'crear', 'Registrar activo'],
            ['activos', 'editar', 'Editar activo'],
            ['activos', 'eliminar', 'Eliminar activo'],
            ['activos', 'dar_baja', 'Dar de baja activo'],
            ['catalogos', 'ver', 'Ver catálogos'],
            ['catalogos', 'gestionar', 'Gestionar catálogos'],
            ['ubicaciones', 'ver', 'Ver ubicaciones'],
            ['ubicaciones', 'gestionar', 'Gestionar ubicaciones'],
            ['movimientos', 'ver', 'Ver movimientos'],
            ['movimientos', 'gestionar', 'Registrar movimiento'],
            ['movimientos', 'validar', 'Validar / autorizar movimiento'],
            ['mantenimientos', 'ver', 'Ver mantenimientos'],
            ['mantenimientos', 'gestionar', 'Registrar mantenimiento'],
            ['bajas', 'ver', 'Ver bajas'],
            ['bajas', 'gestionar', 'Gestionar bajas'],
            ['inventarios', 'ver', 'Ver inventarios'],
            ['inventarios', 'gestionar', 'Gestionar inventarios'],
            ['saneamientos', 'ver', 'Ver saneamientos'],
            ['saneamientos', 'gestionar', 'Gestionar saneamientos'],
            ['entregas_cargo', 'ver', 'Ver entregas de cargo'],
            ['entregas_cargo', 'gestionar', 'Gestionar entregas de cargo'],
            ['importaciones', 'ver', 'Ver importaciones SIGA'],
            ['importaciones', 'gestionar', 'Ejecutar importaciones SIGA'],
            ['colaboradores', 'ver', 'Ver colaboradores'],
            ['colaboradores', 'gestionar', 'Gestionar colaboradores'],
            ['usuarios', 'ver', 'Ver usuarios'],
            ['usuarios', 'gestionar', 'Gestionar usuarios'],
            ['roles', 'ver', 'Ver roles y permisos'],
            ['roles', 'gestionar', 'Gestionar roles y permisos'],
            ['auditoria', 'ver', 'Ver auditoría de cambios'],
            ['reportes', 'ver', 'Ver reportes y KPIs'],
        ];

        $now = now();
        DB::table('permisos')->insert(array_map(fn($p) => [
            'modulo'    => $p[0],
            'accion'    => $p[1],
            'nombre'    => $p[2],
            'estado'    => 'ACTIVO',
            'creado_en' => $now,
        ], $permisos));
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};
