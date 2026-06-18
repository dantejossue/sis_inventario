<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';

    protected $primaryKey = 'id_ubicacion';

    // La BD gestiona creado_en / actualizado_en (useCurrent / useCurrentOnUpdate)
    public $timestamps = false;

    protected $fillable = [
        'id_sede',
        'id_ubicacion_padre',
        'nombre',
        'tipo',
        'codigo',
        'descripcion',
        'estado',
    ];

    /** Sede a la que pertenece la ubicación física. */
    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede', 'id_sede');
    }

    /** Nodo superior en el árbol (edificio del piso, piso del ambiente, etc.). */
    public function padre()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_padre', 'id_ubicacion');
    }

    /** Nodos hijos directos. */
    public function hijos()
    {
        return $this->hasMany(Ubicacion::class, 'id_ubicacion_padre', 'id_ubicacion');
    }

    /** Activos ubicados físicamente aquí. */
    public function activos()
    {
        return $this->hasMany(Activo::class, 'id_ubicacion_actual', 'id_ubicacion');
    }
}
