<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Crea el administrador inicial del sistema de forma segura';

    public function handle(): int
    {
        $this->info('Creación del administrador inicial');
        $this->newLine();

        /*
        |--------------------------------------------------------------------------
        | Verificar rol ADMINISTRADOR
        |--------------------------------------------------------------------------
        */
        $rol = DB::table('roles')
            ->where('nombre', 'ADMINISTRADOR')
            ->first();

        if (!$rol) {
            $this->error(
                'No existe el rol ADMINISTRADOR. Ejecute primero: php artisan db:seed --class=RolSeeder'
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Datos del colaborador
        |--------------------------------------------------------------------------
        */
        $documento = trim(
            $this->ask('Número de documento')
        );

        $nombre = trim(
            $this->ask('Nombres')
        );

        $apellidoPaterno = trim(
            $this->ask('Apellido paterno')
        );

        $apellidoMaterno = trim(
            $this->ask('Apellido materno (opcional)', '')
        );

        $usuario = trim(
            $this->ask('Nombre de usuario')
        );

        /*
        |--------------------------------------------------------------------------
        | Validaciones
        |--------------------------------------------------------------------------
        */
        $validator = Validator::make(
            [
                'documento'         => $documento,
                'nombre'            => $nombre,
                'apellido_paterno'  => $apellidoPaterno,
                'usuario'           => $usuario,
            ],
            [
                'documento'        => ['required', 'string', 'max:20'],
                'nombre'           => ['required', 'string', 'max:100'],
                'apellido_paterno' => ['required', 'string', 'max:100'],
                'usuario'          => ['required', 'string', 'max:100'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Evitar duplicados
        |--------------------------------------------------------------------------
        */
        $usuarioExiste = DB::table('usuarios')
            ->where('nombre_usuario', $usuario)
            ->exists();

        if ($usuarioExiste) {
            $this->error("Ya existe el usuario '{$usuario}'.");

            return self::FAILURE;
        }

        $documentoExiste = DB::table('colaboradores')
            ->where('nro_documento', $documento)
            ->exists();

        if ($documentoExiste) {
            $this->error(
                "Ya existe un colaborador con el documento '{$documento}'."
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Contraseña
        |--------------------------------------------------------------------------
        */
        $password = $this->secret('Contraseña');
        $passwordConfirmacion = $this->secret('Confirmar contraseña');

        if (!$password) {
            $this->error('La contraseña es obligatoria.');

            return self::FAILURE;
        }

        if ($password !== $passwordConfirmacion) {
            $this->error('Las contraseñas no coinciden.');

            return self::FAILURE;
        }

        if (mb_strlen($password) < 12) {
            $this->error(
                'La contraseña debe tener como mínimo 12 caracteres.'
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Confirmación
        |--------------------------------------------------------------------------
        */
        $this->newLine();

        $this->table(
            ['Campo', 'Valor'],
            [
                ['Documento', $documento],
                ['Nombres', $nombre],
                ['Apellido paterno', $apellidoPaterno],
                ['Apellido materno', $apellidoMaterno ?: '-'],
                ['Usuario', $usuario],
                ['Rol', 'ADMINISTRADOR'],
            ]
        );

        if (!$this->confirm('¿Crear este administrador?', true)) {
            $this->warn('Operación cancelada.');

            return self::SUCCESS;
        }

        /*
        |--------------------------------------------------------------------------
        | Creación transaccional
        |--------------------------------------------------------------------------
        */
        DB::transaction(function () use (
            $documento,
            $nombre,
            $apellidoPaterno,
            $apellidoMaterno,
            $usuario,
            $password,
            $rol
        ) {
            $idColaborador = DB::table('colaboradores')->insertGetId([
                'nro_documento'    => $documento,
                'per_nombre'       => mb_strtoupper($nombre),
                'per_apepat'       => mb_strtoupper($apellidoPaterno),
                'per_apemat'       => $apellidoMaterno
                    ? mb_strtoupper($apellidoMaterno)
                    : null,
                'cargo'            => 'ADMINISTRADOR DE SISTEMAS',
                'tipo_colaborador' => 'ADMINISTRATIVO',
                'estado'           => 'ACTIVO',
            ]);

            DB::table('usuarios')->insert([
                'id_colaborador' => $idColaborador,
                'id_rol'         => $rol->id_rol,
                'nombre_usuario' => $usuario,
                'contrasena'     => Hash::make($password),
                'estado'         => 'ACTIVO',
            ]);
        });

        $this->newLine();

        $this->info('Administrador creado correctamente.');

        return self::SUCCESS;
    }
}
