<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de cuentas de acceso al sistema.
     * Relación 1:1 con colaboradores — un colaborador puede tener exactamente una cuenta.
     *
     * Seguridad implementada:
     *   - Bloqueo por intentos fallidos (intentos_fallidos + bloqueado_hasta)
     *   - Registro de último acceso para auditoría
     *   - Fecha de baja lógica (no se eliminan registros)
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            // ── Clave primaria ────────────────────────────────────────
            $table->integer('id_usuario')->autoIncrement();

            // Relación 1:1 con colaboradores (unique garantiza la cardinalidad)
            $table->integer('id_colaborador')->unique('uk_usuario_colaborador');
            $table->integer('id_rol');

            // ── Credenciales ──────────────────────────────────────────
            $table->string('nombre_usuario', 100)->unique('uk_usuario_nombre');
            $table->string('contrasena', 255); // Siempre bcrypt, nunca texto plano

            // ── Estado y control de acceso ────────────────────────────
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->dateTime('ultimo_acceso_en')->nullable();
            $table->dateTime('fecha_cambio_clave_en')->nullable();
            $table->integer('intentos_fallidos')->default(0);
            $table->dateTime('bloqueado_hasta')->nullable(); // Null = no bloqueado

            // ── Auditoría temporal ────────────────────────────────────
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();
            $table->dateTime('fecha_baja')->nullable(); // Baja lógica, no física

            // ── Claves foráneas ───────────────────────────────────────
            $table->foreign('id_colaborador')
                ->references('id_colaborador')
                ->on('colaboradores')
                ->restrictOnDelete(); // Desactivar primero el usuario antes de eliminar el colaborador

            $table->foreign('id_rol')
                ->references('id_rol')
                ->on('roles')
                ->restrictOnDelete(); // No eliminar un rol con usuarios activos

            // ── Índices de rendimiento ────────────────────────────────
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
