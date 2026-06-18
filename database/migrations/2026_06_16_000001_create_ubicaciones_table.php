<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubicación FÍSICA jerárquica de un activo (adjacency list / árbol).
     *
     * A diferencia de 'sede_dependencia' (que es la unidad ORGÁNICA donde labora
     * un colaborador), 'ubicaciones' modela el lugar físico real donde está el
     * equipo: Sede → Edificio → Piso → Ambiente/Almacén.
     *
     *   - id_sede:            a qué sede pertenece (denormalizado para filtrar
     *                         "activos por sede" sin recorrer el árbol).
     *   - id_ubicacion_padre: nodo superior dentro de la misma sede (self-FK).
     *
     * Relación con activos:
     *   activo.id_ubicacion_actual → ubicaciones.id_ubicacion
     */
    public function up(): void
    {
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->integer('id_ubicacion')->autoIncrement();

            $table->integer('id_sede');
            $table->integer('id_ubicacion_padre')->nullable();

            $table->string('nombre', 150);
            $table->enum('tipo', [
                'PISO',
                'OFICINA',
                'ALMACEN',
                'PABELLON',
                'AULA',
                'LABORATORIO',
                'OTRO',
            ])->default('OFICINA');

            // Código de ambiente patrimonial (opcional)
            $table->string('codigo', 50)->nullable();
            $table->string('descripcion', 255)->nullable();

            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            // ── Claves foráneas ───────────────────────────────────────
            $table->foreign('id_sede')
                ->references('id_sede')->on('sedes');

            $table->foreign('id_ubicacion_padre')
                ->references('id_ubicacion')->on('ubicaciones')
                ->nullOnDelete(); // Si se borra el padre, los hijos quedan colgando de la raíz

            // ── Índices ───────────────────────────────────────────────
            $table->index('id_sede');
            $table->index('id_ubicacion_padre');
            $table->index('estado');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones');
    }
};
