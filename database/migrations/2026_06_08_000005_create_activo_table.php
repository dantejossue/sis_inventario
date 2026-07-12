<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activo', function (Blueprint $table) {
            $table->integer('id_activo')->autoIncrement();

            // ── Relaciones (todas SET NULL: el activo sobrevive al catálogo) ──
            $table->unsignedInteger('id_modelo')->nullable();
            $table->unsignedInteger('id_categoria')->nullable();
            $table->integer('id_ubicacion_actual')->nullable();
            $table->integer('id_responsable_actual')->nullable();

            // ── Identificación ────────────────────────────────────────
            $table->string('codigo_interno', 50)->nullable()->unique('uk_activo_codigo_interno');
            $table->string('codigo_patrimonial', 100)->nullable()->unique('uk_activo_codigo_patrimonial');
            $table->string('codigo_siga', 60)->nullable();
            $table->string('numero_pecosa', 60)->nullable();
            $table->string('numero_orden_compra', 80)->nullable();
            $table->date('fecha_alta_siga')->nullable();
            $table->string('numero_serie', 150)->nullable(); // index, ya no único

            // ── Descriptivos ──────────────────────────────────────────
            $table->string('descripcion', 255)->nullable();
            $table->string('imagen', 255)->nullable();
            $table->date('fecha_adquisicion')->nullable();
            $table->decimal('valor_compra', 12, 2)->nullable();
            $table->string('proveedor', 150)->nullable();
            $table->date('garantia_inicio')->nullable();
            $table->date('garantia_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('qr_token', 255)->nullable()->unique();

            // ── Condición física y situación operativa (ENUM directo, brief OTI) ──
            // Reemplazan a las antiguas FK id_condicion_actual / id_situacion_actual.
            $table->enum('condicion_actual', ['NUEVO', 'BUENO', 'REGULAR', 'MALO'])->default('BUENO');
            $table->enum('situacion_actual', [
                'DISPONIBLE', 'EN_USO', 'EN_PRESTAMO', 'EN_MANTENIMIENTO',
                'EN_PROVEEDOR', 'OBSERVADO', 'DADO_DE_BAJA',
            ])->default('DISPONIBLE');

            // ── Origen de registro ────────────────────────────────────
            $table->enum('origen_registro', ['MANUAL', 'EXCEL', 'REGULARIZACION'])->default('MANUAL');
            // Flag referencial "pendiente de actualización en SIGA" a nivel de activo.
            $table->enum('estado_siga', ['NO_APLICA', 'PENDIENTE_ACTUALIZACION', 'REGISTRADO', 'OBSERVADO'])->default('NO_APLICA');

            // ── Auditoría ─────────────────────────────────────────────
            $table->integer('creado_por')->nullable();
            $table->integer('actualizado_por')->nullable();
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();
            $table->softDeletes('deleted_at'); // Baja lógica (F1)

            // ── Claves foráneas ───────────────────────────────────────
            $table->foreign('id_modelo')->references('id_modelo')->on('modelo')->nullOnDelete();
            $table->foreign('id_categoria')->references('id_categoria')->on('categoria_activo')->nullOnDelete();
            $table->foreign('id_ubicacion_actual', 'fk_activo_ubicacion')->references('id_ubicacion')->on('ubicaciones')->nullOnDelete();
            $table->foreign('id_responsable_actual')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('creado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();
            $table->foreign('actualizado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            // ── Índices ───────────────────────────────────────────────
            $table->index('id_modelo');
            $table->index('id_categoria');
            $table->index('condicion_actual');
            $table->index('situacion_actual');
            $table->index('id_responsable_actual');
            $table->index('numero_serie');
            $table->index('codigo_siga');
            $table->index('estado_siga');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo');
    }
};
