<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede_Dependencia extends Model
{
    // 1. Apuntando a la tabla sede_dependencia
    protected $table = 'sede_dependencia';

    // 2. Definiendo la llave primaria personalizada
    protected $primaryKey = 'id_sede_dependencia';

    // 3. Desactvando los timestamps en inglés de laravel
    public $timestamps = false;

    // 4. Los campos que permitimos guardar
    protected $fillable = [
        'id_sede',
        'id_dependencia',
        'estado'
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede', 'id_sede');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia', 'id_dependencia');
    }
}
