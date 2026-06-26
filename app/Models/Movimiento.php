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
        'fecha_registro'      => 'datetime',
        'fecha_movimiento'    => 'datetime',
        'fecha_registro_siga' => 'datetime',
        'requiere_tramite'    => 'boolean',
    ];

    protected $fillable = [
        'codigo_movimiento',
        'tipo',
        'motivo',
        'estado',
        'registrado_por',
        'validado_oti_por',
        'validado_patrimonio_por',
        'fecha_registro',
        'fecha_movimiento',
        'requiere_tramite',
        'estado_siga',
        'fecha_registro_siga',
        'observacion_siga',
        'observaciones',
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleMovimientoActivo::class, 'id_movimiento', 'id_movimiento');
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
