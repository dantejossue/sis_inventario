<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Simplifica el módulo de bajas:
 *   - estado  → REGISTRADA | EJECUTADA | RECHAZADA
 *   - causal_baja → DANO_IRREPARABLE | OBSOLESCENCIA | REPARACION_NO_CONVENIENTE
 *                   | RAEE | SUSTRACCION | OTRO
 *
 * Migración de datos SEGURA (no borra filas). Las fechas, documentos, usuarios
 * y observaciones históricas se conservan; el detalle intermedio del flujo
 * anterior se colapsa a REGISTRADA.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Causal: ampliar enum, remapear, estrechar ──
        DB::statement("ALTER TABLE bajas_activo MODIFY causal_baja ENUM(
            'DANO','OBSOLESCENCIA_TECNICA','MANTENIMIENTO_ONEROSO','SIN_REPARACION','RAEE','SUSTRACCION','OTRO',
            'DANO_IRREPARABLE','OBSOLESCENCIA','REPARACION_NO_CONVENIENTE'
        ) NOT NULL");

        DB::table('bajas_activo')->where('causal_baja', 'DANO')->update(['causal_baja' => 'DANO_IRREPARABLE']);
        DB::table('bajas_activo')->where('causal_baja', 'SIN_REPARACION')->update(['causal_baja' => 'DANO_IRREPARABLE']);
        DB::table('bajas_activo')->where('causal_baja', 'OBSOLESCENCIA_TECNICA')->update(['causal_baja' => 'OBSOLESCENCIA']);
        DB::table('bajas_activo')->where('causal_baja', 'MANTENIMIENTO_ONEROSO')->update(['causal_baja' => 'REPARACION_NO_CONVENIENTE']);
        // RAEE, SUSTRACCION, OTRO se conservan sin cambios.

        DB::statement("ALTER TABLE bajas_activo MODIFY causal_baja ENUM(
            'DANO_IRREPARABLE','OBSOLESCENCIA','REPARACION_NO_CONVENIENTE','RAEE','SUSTRACCION','OTRO'
        ) NOT NULL");

        // ── Estado: colapsar intermedios a REGISTRADA, estrechar ──
        // El conjunto final (REGISTRADA, EJECUTADA, RECHAZADA) ⊂ enum actual, así que
        // basta con remapear antes de estrechar.
        DB::table('bajas_activo')
            ->whereIn('estado', ['EN_EVALUACION', 'RECOMENDADA', 'VALIDADA', 'SOLICITADA', 'APROBADA'])
            ->update(['estado' => 'REGISTRADA']);

        DB::statement("ALTER TABLE bajas_activo MODIFY estado ENUM(
            'REGISTRADA','EJECUTADA','RECHAZADA'
        ) NOT NULL DEFAULT 'REGISTRADA'");
    }

    public function down(): void
    {
        // Estado: reabrir el enum al conjunto anterior (el intermedio exacto no se reconstruye).
        DB::statement("ALTER TABLE bajas_activo MODIFY estado ENUM(
            'REGISTRADA','EN_EVALUACION','RECOMENDADA','VALIDADA','RECHAZADA','EJECUTADA'
        ) NOT NULL DEFAULT 'REGISTRADA'");

        // Causal: ampliar, revertir remapeo (aproximado), estrechar al original.
        DB::statement("ALTER TABLE bajas_activo MODIFY causal_baja ENUM(
            'DANO','OBSOLESCENCIA_TECNICA','MANTENIMIENTO_ONEROSO','SIN_REPARACION','RAEE','SUSTRACCION','OTRO',
            'DANO_IRREPARABLE','OBSOLESCENCIA','REPARACION_NO_CONVENIENTE'
        ) NOT NULL");

        DB::table('bajas_activo')->where('causal_baja', 'DANO_IRREPARABLE')->update(['causal_baja' => 'DANO']);
        DB::table('bajas_activo')->where('causal_baja', 'OBSOLESCENCIA')->update(['causal_baja' => 'OBSOLESCENCIA_TECNICA']);
        DB::table('bajas_activo')->where('causal_baja', 'REPARACION_NO_CONVENIENTE')->update(['causal_baja' => 'MANTENIMIENTO_ONEROSO']);

        DB::statement("ALTER TABLE bajas_activo MODIFY causal_baja ENUM(
            'DANO','OBSOLESCENCIA_TECNICA','MANTENIMIENTO_ONEROSO','SIN_REPARACION','RAEE','SUSTRACCION','OTRO'
        ) NOT NULL");
    }
};
