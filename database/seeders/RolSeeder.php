<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre'      => 'ADMINISTRADOR',
                'descripcion' => 'Control total del sistema: usuarios, configuración y auditoría.',
                'estado'      => 'ACTIVO',
            ],
            [
                'nombre'      => 'ALMACEN',
                'descripcion' => 'Registro de ingresos, salidas, asignaciones, préstamos y transferencias de activos.',
                'estado'      => 'ACTIVO',
            ],
            [
                'nombre'      => 'SERVICIOS_GENERALES',
                'descripcion' => 'Acceso de solo lectura a reportes, inventario, KPIs y auditorías.',
                'estado'      => 'ACTIVO',
            ],
            [
                'nombre'      => 'PROVEEDOR',
                'descripcion' => 'Portal limitado: garantías, órdenes de compra y entregas pendientes.',
                'estado'      => 'ACTIVO',
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}
