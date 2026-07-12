<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleMovimientoActivo extends Model
{
    protected $table = 'detalle_movimiento_activo';

    protected $primaryKey = 'id_detalle_movimiento';

    public $timestamps = false;

    protected $fillable = [
        'id_movimiento',
        'id_activo',
        'id_responsable_origen',
        'id_responsable_destino',
        'id_ubicacion_origen',
        'id_ubicacion_destino',
        'condicion_salida',
        'condicion_retorno',
        'situacion_anterior',
        'situacion_resultante',
        'resultado',
        'observacion_salida',
        'observacion_retorno',
        'observaciones',
    ];

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class, 'id_movimiento', 'id_movimiento');
    }

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }

    public function responsableOrigen()
    {
        return $this->belongsTo(Colaborador::class, 'id_responsable_origen', 'id_colaborador');
    }

    public function responsableDestino()
    {
        return $this->belongsTo(Colaborador::class, 'id_responsable_destino', 'id_colaborador');
    }

    public function ubicacionOrigen()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_origen', 'id_ubicacion');
    }

    public function ubicacionDestino()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_destino', 'id_ubicacion');
    }
}
