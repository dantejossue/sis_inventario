<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Referencias polimórficas al sistema de trámite documentario / mesa de
     * partes. Es el puente con SGD: enlaza un expediente externo a cualquier
     * entidad por (entidad_tipo, entidad_id).
     */
    public function up(): void
    {
        Schema::create('tramites_referencias', function (Blueprint $table) {
            $table->integer('id_tramite_referencia')->autoIncrement();

            $table->enum('entidad_tipo', [
                'ACTIVO', 'MOVIMIENTO', 'MANTENIMIENTO', 'BAJA', 'INVENTARIO',
                'SANEAMIENTO', 'ENTREGA_CARGO', 'TRAMITE', 'OTRO',
            ]);
            $table->integer('entidad_id');

            $table->string('numero_expediente', 100)->nullable();
            $table->string('numero_documento', 100)->nullable();
            $table->string('tipo_documento', 100)->nullable();
            $table->string('asunto', 255)->nullable();

            $table->enum('sistema_origen', ['TRAMITE_DOCUMENTARIO', 'MESA_PARTES', 'SGD', 'OTRO'])->default('TRAMITE_DOCUMENTARIO');
            $table->enum('estado_tramite', ['PENDIENTE', 'EN_PROCESO', 'APROBADO', 'OBSERVADO', 'RECHAZADO', 'ARCHIVADO'])->default('PENDIENTE');

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_respuesta')->nullable();
            $table->string('url_tramite', 255)->nullable();
            $table->text('observaciones')->nullable();

            $table->integer('registrado_por')->nullable();
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('registrado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index(['entidad_tipo', 'entidad_id'], 'idx_tramite_entidad');
            $table->index('estado_tramite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tramites_referencias');
    }
};
