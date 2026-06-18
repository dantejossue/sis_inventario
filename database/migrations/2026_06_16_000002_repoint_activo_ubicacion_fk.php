<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Re-apunta activo.id_ubicacion_actual desde 'sede_dependencia' (unidad
     * orgánica del responsable) hacia 'ubicaciones' (lugar físico real).
     *
     * A partir de aquí la ubicación del activo es independiente del colaborador:
     * se elige explícitamente en el formulario.
     */
    public function up(): void
    {
        // Los valores actuales referencian sede_dependencia, no ubicaciones.
        // Se anulan para poder crear la nueva FK; se reasignarán manualmente.
        DB::table('activo')->update(['id_ubicacion_actual' => null]);

        $this->dropForeignIfExists('activo', 'fk_activo_ubicacion');

        Schema::table('activo', function (Blueprint $table) {
            $table->foreign('id_ubicacion_actual', 'fk_activo_ubicacion')
                ->references('id_ubicacion')->on('ubicaciones')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('activo')->update(['id_ubicacion_actual' => null]);

        $this->dropForeignIfExists('activo', 'fk_activo_ubicacion');

        Schema::table('activo', function (Blueprint $table) {
            $table->foreign('id_ubicacion_actual', 'fk_activo_ubicacion')
                ->references('id_sede_dependencia')->on('sede_dependencia')
                ->nullOnDelete();
        });
    }

    /**
     * Elimina una FK solo si existe (la migración puede re-ejecutarse tras un
     * fallo parcial en el que la FK ya fue eliminada).
     */
    private function dropForeignIfExists(string $table, string $constraint): void
    {
        $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();

        if ($exists) {
            Schema::table($table, function (Blueprint $t) use ($constraint) {
                $t->dropForeign($constraint);
            });
        }
    }
};
