<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Una fila por cada transición de la condición física de un activo. La escribe
 * el ActivoObserver de forma resiliente; nunca debe romper la operación de
 * negocio que originó el cambio.
 */
class HistorialCondicionActivo extends Model
{
    protected $table      = 'historial_condicion_activo';
    protected $primaryKey = 'id_historial';

    // La BD gestiona creado_en (DEFAULT CURRENT_TIMESTAMP); no hay actualizado_en.
    public $timestamps = false;

    protected $fillable = [
        'id_activo',
        'condicion_anterior', 'condicion_nueva',
        'origen', 'entidad_origen_tipo', 'entidad_origen_id',
        'motivo', 'registrado_por',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }
}
