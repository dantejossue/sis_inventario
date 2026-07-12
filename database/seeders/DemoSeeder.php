<?php

namespace Database\Seeders;

use App\Models\Activo;
use App\Models\ActivoTecnico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Datos de arranque para EJERCITAR el frontend (no es data de producción):
 * una sede, la dependencia OTI, ubicaciones, colaboradores, modelos y una
 * muestra de activos en distintas situaciones/condiciones. Idempotente: si ya
 * existe el activo TI-0001 no vuelve a sembrar.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Activo::where('codigo_interno', 'TI-0001')->exists()) {
            $this->command?->warn('DemoSeeder: los datos de ejemplo ya existen. Nada que hacer.');
            return;
        }

        $adminId = DB::table('usuarios')->where('nombre_usuario', 'admin')->value('id_usuario');
        $now = now();

        // ── Sede + dependencia OTI + vínculo ──────────────────────────
        $idSede = DB::table('sedes')->insertGetId([
            'nombre_sede' => 'Sede Central UNDC', 'ubicacion' => 'Av. Central s/n', 'estado' => 'ACTIVO', 'creado_en' => $now,
        ]);
        $idDep = DB::table('dependencias')->insertGetId([
            'nombre_dependencia' => 'Oficina de Tecnologías de la Información',
            'descripcion' => 'OTI', 'estado' => 'ACTIVO', 'creado_en' => $now,
        ]);
        $idSD = DB::table('sede_dependencia')->insertGetId([
            'id_sede' => $idSede, 'id_dependencia' => $idDep, 'estado' => 'ACTIVO', 'creado_en' => $now,
        ]);

        // ── Ubicaciones (árbol: Edificio › Piso › ambientes hoja) ─────
        $idEdif = DB::table('ubicaciones')->insertGetId([
            'id_sede' => $idSede, 'id_ubicacion_padre' => null, 'nombre' => 'Pabellón Administrativo',
            'tipo' => 'EDIFICIO', 'codigo' => 'PAB-ADM', 'estado' => 'ACTIVO', 'creado_en' => $now,
        ]);
        $idPiso = DB::table('ubicaciones')->insertGetId([
            'id_sede' => $idSede, 'id_ubicacion_padre' => $idEdif, 'nombre' => 'Piso 2',
            'tipo' => 'PISO', 'codigo' => 'P2', 'estado' => 'ACTIVO', 'creado_en' => $now,
        ]);
        $ubicOti = DB::table('ubicaciones')->insertGetId([
            'id_sede' => $idSede, 'id_ubicacion_padre' => $idPiso, 'nombre' => 'Oficina OTI',
            'tipo' => 'OFICINA', 'codigo' => 'OTI-201', 'estado' => 'ACTIVO', 'creado_en' => $now,
        ]);
        $ubicAlmacen = DB::table('ubicaciones')->insertGetId([
            'id_sede' => $idSede, 'id_ubicacion_padre' => $idPiso, 'nombre' => 'Almacén TI',
            'tipo' => 'ALMACEN', 'codigo' => 'ALM-TI', 'estado' => 'ACTIVO', 'creado_en' => $now,
        ]);
        $ubicLab = DB::table('ubicaciones')->insertGetId([
            'id_sede' => $idSede, 'id_ubicacion_padre' => $idPiso, 'nombre' => 'Laboratorio de Cómputo',
            'tipo' => 'LABORATORIO', 'codigo' => 'LAB-1', 'estado' => 'ACTIVO', 'creado_en' => $now,
        ]);

        // ── Colaboradores OTI ─────────────────────────────────────────
        $colabs = [];
        foreach ([
            ['45871236', 'Juan', 'Pérez', 'Quispe', 'Jefe de OTI', 'ADMINISTRATIVO'],
            ['41258963', 'María', 'Gómez', 'Torres', 'Soporte Técnico', 'TECNICO'],
            ['70123456', 'Luis', 'Ramírez', 'Vega', 'Analista de Redes', 'TECNICO'],
        ] as [$doc, $nom, $ap, $am, $cargo, $tipo]) {
            $colabs[] = DB::table('colaboradores')->insertGetId([
                'nro_documento' => $doc, 'per_nombre' => $nom, 'per_apepat' => $ap, 'per_apemat' => $am,
                'cargo' => $cargo, 'tipo_colaborador' => $tipo, 'id_sede_dependencia' => $idSD,
                'estado' => 'ACTIVO', 'creado_en' => $now,
            ]);
        }

        // ── Cuentas de usuario del personal OTI ───────────────────────
        // Necesarias para atribuir movimientos al responsable y al jefe de OTI.
        $rolJefe = DB::table('roles')->where('nombre', 'JEFE_AREA')->value('id_rol')
            ?? DB::table('roles')->where('nombre', 'ADMINISTRADOR')->value('id_rol');
        $rolColab = DB::table('roles')->where('nombre', 'COLABORADOR')->value('id_rol') ?? $rolJefe;
        foreach ([[$colabs[0], 'jperez', $rolJefe], [$colabs[1], 'mgomez', $rolColab], [$colabs[2], 'lramirez', $rolColab]] as [$idc, $user, $rol]) {
            DB::table('usuarios')->insert([
                'id_colaborador' => $idc, 'id_rol' => $rol, 'nombre_usuario' => $user,
                'contrasena' => Hash::make('Demo1234*'), 'estado' => 'ACTIVO', 'creado_en' => $now,
            ]);
        }

        // ── Modelos (marca/categoría existentes) ──────────────────────
        $mkModelo = fn($idMarca, $idCat, $nombre) => DB::table('modelo')->insertGetId([
            'id_marca' => $idMarca, 'id_categoria' => $idCat, 'nombre' => $nombre, 'estado' => 'ACTIVO', 'creado_en' => $now,
        ]);
        $mLaptop  = $mkModelo(1, 1, 'ProBook 450 G8');       // HP / LAPTOP
        $mCpu     = $mkModelo(2, 2, 'OptiPlex 3080');        // DELL / CPU
        $mSwitch  = $mkModelo(9, 4, 'Catalyst 2960');        // CISCO / SWITCH
        $mMonitor = $mkModelo(5, 7, 'S24R650');              // SAMSUNG / MONITOR
        $mImpre   = $mkModelo(7, 8, 'EcoTank L3250');        // EPSON / IMPRESORA
        $mServer  = $mkModelo(2, 3, 'PowerEdge R740');       // DELL / SERVIDOR

        // ── Activos de muestra ────────────────────────────────────────
        $i = 0;
        $mk = function (array $d) use ($adminId, &$i) {
            $i++;
            return Activo::create(array_merge([
                'codigo_interno'    => sprintf('TI-%04d', $i),
                'codigo_patrimonial' => sprintf('740800%04d', $i),
                'qr_token'          => (string) Str::uuid(),
                'origen_registro'   => 'MANUAL',
                'creado_por'        => $adminId,
            ], $d));
        };

        $a1 = $mk(['id_modelo' => $mLaptop, 'id_categoria' => 1, 'numero_serie' => 'SN-LAP-001',
            'descripcion' => 'Laptop Jefatura OTI', 'condicion_actual' => 'BUENO', 'situacion_actual' => 'EN_USO',
            'id_responsable_actual' => $colabs[0], 'id_ubicacion_actual' => $ubicOti,
            'fecha_adquisicion' => '2024-03-15', 'valor_compra' => 3500, 'proveedor' => 'IMPORTACIONES SAC',
            'garantia_inicio' => '2024-03-15', 'garantia_fin' => now()->addMonths(8)->toDateString(),
            'codigo_siga' => '74080001', 'numero_pecosa' => 'PECOSA-2024-018', 'numero_orden_compra' => 'OC-2024-102',
            'fecha_alta_siga' => '2024-03-20', 'estado_siga' => 'REGISTRADO']);
        ActivoTecnico::create(['id_activo' => $a1->id_activo, 'procesador' => 'Intel Core i5-1135G7',
            'memoria_ram' => '16 GB', 'almacenamiento' => '512 GB', 'tipo_almacenamiento' => 'SSD',
            'sistema_operativo' => 'Windows 11 Pro', 'nombre_equipo' => 'OTI-LAP01', 'estado_operativo' => 'OPERATIVO']);

        $mk(['id_modelo' => $mCpu, 'id_categoria' => 2, 'numero_serie' => 'SN-CPU-002',
            'descripcion' => 'CPU de escritorio', 'condicion_actual' => 'NUEVO', 'situacion_actual' => 'DISPONIBLE',
            'id_ubicacion_actual' => $ubicAlmacen, 'fecha_adquisicion' => '2025-01-10', 'valor_compra' => 2200]);

        $a3 = $mk(['id_modelo' => $mSwitch, 'id_categoria' => 4, 'numero_serie' => 'SN-SW-003',
            'descripcion' => 'Switch de acceso 24 puertos', 'condicion_actual' => 'BUENO', 'situacion_actual' => 'EN_USO',
            'id_responsable_actual' => $colabs[2], 'id_ubicacion_actual' => $ubicOti,
            'fecha_adquisicion' => '2023-08-01', 'valor_compra' => 1800]);
        ActivoTecnico::create(['id_activo' => $a3->id_activo, 'direccion_ip' => '10.0.0.2',
            'direccion_mac' => 'AA:BB:CC:00:11:22', 'nombre_equipo' => 'SW-OTI-01', 'estado_operativo' => 'OPERATIVO']);

        $mk(['id_modelo' => $mMonitor, 'id_categoria' => 7, 'numero_serie' => 'SN-MON-004',
            'descripcion' => 'Monitor 24"', 'condicion_actual' => 'REGULAR', 'situacion_actual' => 'DISPONIBLE',
            'id_ubicacion_actual' => $ubicAlmacen]);

        $mk(['id_modelo' => $mImpre, 'id_categoria' => 8, 'numero_serie' => 'SN-IMP-005',
            'descripcion' => 'Impresora multifuncional', 'condicion_actual' => 'REGULAR', 'situacion_actual' => 'EN_MANTENIMIENTO',
            'id_responsable_actual' => $colabs[1], 'id_ubicacion_actual' => $ubicLab]);

        $a6 = $mk(['id_modelo' => $mServer, 'id_categoria' => 3, 'numero_serie' => 'SN-SRV-006',
            'descripcion' => 'Servidor de aplicaciones', 'condicion_actual' => 'BUENO', 'situacion_actual' => 'EN_USO',
            'id_responsable_actual' => $colabs[0], 'id_ubicacion_actual' => $ubicOti,
            'fecha_adquisicion' => '2022-06-20', 'valor_compra' => 15000,
            'garantia_inicio' => '2022-06-20', 'garantia_fin' => now()->addDays(45)->toDateString()]);
        ActivoTecnico::create(['id_activo' => $a6->id_activo, 'procesador' => 'Intel Xeon Silver 4210',
            'memoria_ram' => '64 GB', 'almacenamiento' => '2 TB', 'tipo_almacenamiento' => 'MIXTO',
            'sistema_operativo' => 'Windows Server 2022', 'nombre_equipo' => 'SRV-APP01', 'direccion_ip' => '10.0.0.10',
            'estado_operativo' => 'OPERATIVO']);

        $mk(['id_modelo' => $mLaptop, 'id_categoria' => 1, 'numero_serie' => 'SN-LAP-007',
            'descripcion' => 'Laptop observada', 'condicion_actual' => 'MALO', 'situacion_actual' => 'OBSERVADO',
            'id_ubicacion_actual' => $ubicAlmacen, 'fecha_adquisicion' => '2019-05-01', 'valor_compra' => 3000]);

        $this->command?->info("DemoSeeder: sembrados sede/OTI, {$i} activos, ubicaciones, colaboradores y modelos.");
    }
}
