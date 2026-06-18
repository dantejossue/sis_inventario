<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model
{
    // 1. Apuntando a la tabla dependencias
    protected $table = 'dependencias';

    // 2. Definiendo la llave primaria personalizada
    protected $primaryKey = 'id_dependencia';

    // 3. Desactvando los timestamps en inglés de laravel
    public $timestamps = false;

    // 4. Los campos que permitimos guardar
    protected $fillable = ['nombre_dependencia', 'descripcion', 'estado'];

    public function sedeDependencias()
    {
        return $this->hasMany(Sede_Dependencia::class, 'id_dependencia', 'id_dependencia');
    }

    public function sedes()
    {
        return $this->belongsToMany(Sede::class, 'sede_dependencia', 'id_dependencia', 'id_sede')
                    ->wherePivot('estado', 'ACTIVO');
    }
}
