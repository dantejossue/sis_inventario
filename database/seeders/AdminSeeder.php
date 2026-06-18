<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Colaborador base del administrador ─────────────────────
        $idColaborador = DB::table('colaboradores')->insertGetId([
            'nro_documento'    => '00000000',
            'per_nombre'       => 'ADMINISTRADOR',
            'per_apepat'       => 'SISTEMA',
            'per_apemat'       => null,
            'cargo'            => 'ADMINISTRADOR DE SISTEMAS',
            'tipo_colaborador' => 'ADMINISTRATIVO',
            'estado'           => 'ACTIVO',
        ]);

        // ── 2. Cuenta de usuario administrador ────────────────────────
        $idRolAdmin = DB::table('roles')
            ->where('nombre', 'ADMINISTRADOR')
            ->value('id_rol');

        DB::table('usuarios')->insert([
            'id_colaborador' => $idColaborador,
            'id_rol'         => $idRolAdmin,
            'nombre_usuario' => 'admin',
            'contrasena'     => Hash::make('Admin1234*'),
            'estado'         => 'ACTIVO',
        ]);
    }
}
