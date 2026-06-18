<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    // 1. Apuntando a la tabla sedes
    protected $table = 'sedes';

    // 2. Definiendo la llave primaria personalizada
    protected $primaryKey = 'id_sede';

    // 3. Desactvando los timestamps en inglés de laravel
    public $timestamps = false;

    // 4. Los campos que permitimos guardar
    protected $fillable = ['nombre_sede', 'ubicacion', 'estado'];

    public function sedeDependencias()
    {
        return $this->hasMany(Sede_Dependencia::class, 'id_sede', 'id_sede');
    }

    public function dependencias()
    {
        return $this->belongsToMany(Dependencia::class, 'sede_dependencia', 'id_sede', 'id_dependencia')
                    ->wherePivot('estado', 'ACTIVO');
    }
}
