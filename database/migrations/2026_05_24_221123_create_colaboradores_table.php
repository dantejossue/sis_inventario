<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla unificada que combina los datos personales y laborales del colaborador.
     * Reemplaza el diseño original de dos tablas separadas: 'persona' + 'personal'.
     *
     * Relaciones hacia abajo:
     *   - colaboradores 1:1  → usuarios
     *   - colaboradores N:1  → sede_dependencia
     *
     * Relaciones futuras (módulo de activos):
     *   - activo.id_responsable_actual → colaboradores.id_colaborador
     *   - movimiento.id_responsable_*  → colaboradores.id_colaborador
     */
    public function up(): void
    {
        Schema::create('colaboradores', function (Blueprint $table) {
            // ── Clave primaria ────────────────────────────────────────
            $table->integer('id_colaborador')->autoIncrement();

            // ── Datos personales ──────────────────────────────────────
            $table->string('nro_documento', 20)->unique('uk_colaborador_documento');
            $table->string('per_nombre', 100);
            $table->string('per_apepat', 100);
            $table->string('per_apemat', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('nro_movil', 20)->nullable();
            $table->string('per_email', 150)->nullable();
            $table->string('per_direccion', 255)->nullable();
            $table->string('per_foto', 255)->nullable();

            // ── Datos laborales ───────────────────────────────────────
            // Sede + dependencia donde labora actualmente
            $table->integer('id_sede_dependencia')->nullable();

            // Cargo libre: "JEFE DE ALMACÉN", "DIRECTOR OTI", "PRACTICANTE", etc.
            // Sin tabla catálogo porque no tiene lógica de negocio asociada
            $table->string('cargo', 100)->nullable();

            // Categoría funcional para filtros y reportes
            $table->enum('tipo_colaborador', [
                'ADMINISTRATIVO',
                'DOCENTE',
                'TECNICO',
                'PRACTICANTE',
                'EXTERNO',
            ])->default('ADMINISTRATIVO');

            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_salida')->nullable();

            // ── Control de estado ─────────────────────────────────────
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            // ── Clave foránea ─────────────────────────────────────────
            $table->foreign('id_sede_dependencia')
                ->references('id_sede_dependencia')
                ->on('sede_dependencia')
                ->nullOnDelete(); // Si se elimina la unidad, el colaborador no se pierde

            // ── Índices de rendimiento ────────────────────────────────
            $table->index('estado');
            $table->index('tipo_colaborador');
            $table->index('id_sede_dependencia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colaboradores');
    }
};
