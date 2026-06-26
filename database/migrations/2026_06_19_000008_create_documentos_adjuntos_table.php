<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Documentos adjuntos polimórficos: se enlazan a cualquier entidad por
     * (entidad_tipo, entidad_id) sin FK rígida.
     */
    public function up(): void
    {
        Schema::create('documentos_adjuntos', function (Blueprint $table) {
            $table->integer('id_documento')->autoIncrement();

            $table->enum('entidad_tipo', [
                'ACTIVO', 'MOVIMIENTO', 'MANTENIMIENTO', 'BAJA', 'INVENTARIO',
                'SANEAMIENTO', 'ENTREGA_CARGO', 'TRAMITE', 'OTRO',
            ]);
            $table->integer('entidad_id');

            $table->string('tipo_documento', 100)->nullable();
            $table->string('numero_documento', 100)->nullable();
            $table->date('fecha_documento')->nullable();
            $table->string('archivo', 255);
            $table->string('descripcion', 255)->nullable();

            $table->integer('subido_por')->nullable();
            $table->dateTime('creado_en')->useCurrent();

            $table->foreign('subido_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index(['entidad_tipo', 'entidad_id'], 'idx_documento_entidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_adjuntos');
    }
};
