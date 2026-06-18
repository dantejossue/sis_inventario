<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'movimientos';

    protected $primaryKey = 'id_movimiento';

    // La BD gestiona creado_en / actualizado_en
    public $timestamps = false;

    protected $casts = [
        'fecha_movimiento'            => 'datetime',
        'fecha_cierre'                => 'datetime',
        'fecha_devolucion_programada' => 'date',
    ];

    protected $fillable = [
        'codigo_movimiento',
        'tipo',
        'estado',
        'id_colaborador_origen',
        'id_colaborador_destino',
        'id_ubicacion_origen',
        'id_ubicacion_destino',
        'fecha_movimiento',
        'fecha_devolucion_programada',
        'fecha_cierre',
        'motivo',
        'observaciones',
        'registrado_por',
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleMovimientoActivo::class, 'id_movimiento', 'id_movimiento');
    }

    public function colaboradorOrigen()
    {
        return $this->belongsTo(Colaborador::class, 'id_colaborador_origen', 'id_colaborador');
    }

    public function colaboradorDestino()
    {
        return $this->belongsTo(Colaborador::class, 'id_colaborador_destino', 'id_colaborador');
    }

    public function ubicacionOrigen()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_origen', 'id_ubicacion');
    }

    public function ubicacionDestino()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_destino', 'id_ubicacion');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }

    /** Activos abarcados por este movimiento (a través del detalle). */
    public function activos()
    {
        return $this->belongsToMany(Activo::class, 'detalle_movimiento_activo', 'id_movimiento', 'id_activo', 'id_movimiento', 'id_activo');
    }
}
