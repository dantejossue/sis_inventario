<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cabecera de movimientos de activos. Un movimiento agrupa uno o varios
     * activos (ver detalle_movimiento_activo) y aplica un efecto según su tipo:
     * ASIGNAR, TRANSFERENCIA, PRESTAMO, DEVOLUCION, REUBICACION o BAJA.
     */
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->integer('id_movimiento')->autoIncrement();

            $table->string('codigo_movimiento', 50)->unique('uk_movimiento_codigo');
            $table->enum('tipo', ['ASIGNAR', 'TRANSFERENCIA', 'PRESTAMO', 'DEVOLUCION', 'REUBICACION', 'BAJA']);
            $table->enum('estado', ['ABIERTO', 'CERRADO'])->default('CERRADO');

            $table->integer('id_colaborador_origen')->nullable();
            $table->integer('id_colaborador_destino')->nullable();
            $table->integer('id_ubicacion_origen')->nullable();
            $table->integer('id_ubicacion_destino')->nullable();

            $table->dateTime('fecha_movimiento')->useCurrent();
            $table->date('fecha_devolucion_programada')->nullable();
            $table->dateTime('fecha_cierre')->nullable();

            $table->text('motivo')->nullable();
            $table->text('observaciones')->nullable();

            $table->integer('registrado_por')->nullable();

            $table->dateTime('creado_en')->useCurrent();
            $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_colaborador_origen')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('id_colaborador_destino')->references('id_colaborador')->on('colaboradores')->nullOnDelete();
            $table->foreign('id_ubicacion_origen')->references('id_ubicacion')->on('ubicaciones')->nullOnDelete();
            $table->foreign('id_ubicacion_destino')->references('id_ubicacion')->on('ubicaciones')->nullOnDelete();
            $table->foreign('registrado_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('tipo');
            $table->index('estado');
            $table->index('fecha_movimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
