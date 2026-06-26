<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Firmas asociadas al acta de un movimiento (entrega/recepción, jefatura,
     * control patrimonial, etc.).
     */
    public function up(): void
    {
        Schema::create('movimiento_firmas', function (Blueprint $table) {
            $table->integer('id_firma')->autoIncrement();
            $table->integer('id_movimiento');

            $table->enum('rol_firma', [
                'ENTREGA', 'RECIBE', 'RESPONSABLE', 'JEFE_AREA',
                'CONTROL_PATRIMONIAL', 'OTI', 'ALMACEN', 'SEGURIDAD',
            ]);

            $table->integer('id_colaborador')->nullable();
            $table->integer('id_usuario')->nullable();

            $table->enum('estado', ['PENDIENTE', 'FIRMADO', 'RECHAZADO'])->default('PENDIENTE');
            $table->dateTime('fecha_firma')->nullable();
            $table->string('archivo_firma', 255)->nullable();
            $table->string('observaciones', 255)->nullable();

            $table->dateTime('creado_en')->useCurrent();

            $table->foreign('id_movimiento')->references('id_movimiento')->on('movimientos')->cascadeOnDelete();
            $table->foreign('id_colaborador')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('id_movimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_firmas');
    }
};
