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
        'condicion_salida_id',
        'condicion_entrada_id',
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
}
