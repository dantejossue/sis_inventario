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

            $table->unsignedInteger('id_modelo');
            $table->unsignedInteger('id_categoria')->nullable();
            $table->unsignedInteger('id_ubicacion_actual')->nullable();
            $table->integer('id_responsable_actual')->nullable();
            $table->unsignedInteger('id_condicion_actual');
            $table->unsignedInteger('id_situacion_actual');

            $table->string('codigo_interno', 50)->unique('uk_activo_codigo_interno');
            $table->string('codigo_patrimonial', 100)->unique('uk_activo_codigo_patrimonial');
            $table->string('numero_serie', 150)->nullable()->unique('uk_activo_numero_serie');
            $table->string('descripcion', 255)->nullable();
            $table->date('fecha_adquisicion')->nullable();
            $table->decimal('valor_compra', 12, 2)->nullable();
            $table->string('proveedor', 150)->nullable();
            $table->date('garantia_inicio')->nullable();
            $table->date('garantia_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('qr_token', 255)->nullable()->unique();

            $table->unsignedInteger('creado_por')->nullable();
            $table->unsignedInteger('actualizado_por')->nullable();
            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_modelo')->references('id_modelo')->on('modelo');
            $table->foreign('id_categoria')->references('id_categoria')->on('categoria_activo')->nullOnDelete();
            $table->foreign('id_condicion_actual')->references('id_estado_activo')->on('estado_activo');
            $table->foreign('id_situacion_actual')->references('id_estado_activo')->on('estado_activo');
            $table->foreign('id_responsable_actual')->references('id_colaborador')->on('colaboradores')->nullOnDelete();

            $table->index('id_modelo');
            $table->index('id_condicion_actual');
            $table->index('id_situacion_actual');
            $table->index('id_responsable_actual');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo');
    }
};
