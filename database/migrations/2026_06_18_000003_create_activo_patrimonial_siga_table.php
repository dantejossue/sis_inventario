<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Espejo de los datos patrimoniales tal como vienen de SIGA (1-a-1 con
     * activo). Conserva la fuente de verdad patrimonial sin contaminar la tabla
     * operativa 'activo'.
     */
    public function up(): void
    {
        Schema::create('activo_patrimonial_siga', function (Blueprint $table) {
            $table->integer('id_activo_patrimonial_siga')->autoIncrement();
            $table->integer('id_activo');
            $table->integer('id_importacion')->nullable();
            $table->integer('id_importacion_detalle')->nullable();

            $table->string('sbn', 60)->nullable();
            $table->string('descripcion_siga', 255)->nullable();
            $table->string('sede_siga', 150)->nullable();
            $table->string('centro_costos', 100)->nullable();
            $table->string('proveedor_siga', 150)->nullable();
            $table->date('fecha_compra')->nullable();
            $table->decimal('valor_adquisicion', 14, 2)->nullable();
            $table->date('fecha_alta')->nullable();
            $table->decimal('valor_libros', 14, 2)->nullable();
            $table->decimal('valor_neto', 14, 2)->nullable();
            $table->string('codigo_sede_siga', 60)->nullable();
            $table->string('codigo_ubicacion_siga', 60)->nullable();
            $table->string('sede_ubicacion_siga', 255)->nullable();
            $table->string('unidad_ejecutora', 150)->nullable();
            $table->integer('cantidad')->nullable();
            $table->string('codigo_barras_anterior', 100)->nullable();
            $table->string('numero_oc', 60)->nullable();
            $table->string('medidas', 100)->nullable();
            $table->string('tipo_ingreso', 100)->nullable();
            $table->string('correlativo', 60)->nullable();
            $table->string('cuenta_contable', 60)->nullable();
            $table->string('estado_conservacion_siga', 60)->nullable();
            $table->string('condicion_siga', 60)->nullable();
            $table->string('grupo', 100)->nullable();
            $table->string('clase', 100)->nullable();
            $table->string('familia', 100)->nullable();
            $table->string('item_correlativo', 60)->nullable();
            $table->string('color', 60)->nullable();
            $table->text('observaciones_siga')->nullable();
            $table->text('observaciones_adicionales')->nullable();

            $table->dateTime('fecha_importacion')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->unique('id_activo', 'uk_activo_patrimonial_activo');
            $table->foreign('id_activo')->references('id_activo')->on('activo')->cascadeOnDelete();
            $table->foreign('id_importacion')->references('id_importacion')->on('importaciones_siga')->nullOnDelete();
            $table->foreign('id_importacion_detalle')->references('id_importacion_detalle')->on('importacion_siga_detalle')->nullOnDelete();

            $table->index('sbn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo_patrimonial_siga');
    }
};
