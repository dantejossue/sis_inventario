<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoActivo extends Model
{
    protected $table      = 'estado_activo';
    protected $primaryKey = 'id_estado_activo';

    public $timestamps = false;

    protected $fillable = ['tipo', 'codigo', 'nombre', 'descripcion', 'estado'];
}
