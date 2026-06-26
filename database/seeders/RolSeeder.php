<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            // ── Roles del modelo objetivo (TO BE) ─────────────────────
            ['nombre' => 'ADMINISTRADOR', 'descripcion' => 'Control total del sistema: usuarios, configuración y auditoría.'],
            ['nombre' => 'OTI', 'descripcion' => 'Oficina de Tecnologías: ficha técnica, mantenimientos y validación TI de movimientos.'],
            ['nombre' => 'PATRIMONIO', 'descripcion' => 'Control patrimonial: SIGA, bajas, saneamiento y validación patrimonial.'],
            ['nombre' => 'ALMACEN', 'descripcion' => 'Ingresos, salidas, asignaciones, préstamos y transferencias de activos.'],
            ['nombre' => 'JEFE_AREA', 'descripcion' => 'Jefatura de área: aprueba/visa movimientos y entregas de su dependencia.'],
            ['nombre' => 'COLABORADOR', 'descripcion' => 'Usuario final: consulta sus activos asignados y reporta incidencias.'],
            ['nombre' => 'INVENTARIO', 'descripcion' => 'Toma de inventario físico y verificación en campo.'],
            // ── Roles conservados (decisión del usuario) ──────────────
            ['nombre' => 'SERVICIOS_GENERALES', 'descripcion' => 'Acceso de solo lectura a reportes, inventario, KPIs y auditorías.'],
            ['nombre' => 'PROVEEDOR', 'descripcion' => 'Portal limitado: garantías, órdenes de compra y entregas pendientes.'],
        ];

        $now = now();
        DB::table('roles')->insert(array_map(fn($r) => $r + [
            'estado'    => 'ACTIVO',
            'creado_en' => $now,
        ], $roles));
    }
}
