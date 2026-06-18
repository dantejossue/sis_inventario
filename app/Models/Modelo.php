<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    protected $table      = 'modelo';
    protected $primaryKey = 'id_modelo';

    public $timestamps = true;
    const CREATED_AT   = 'creado_en';
    const UPDATED_AT   = 'actualizado_en';

    protected $fillable = ['id_marca', 'id_categoria', 'nombre', 'descripcion', 'estado'];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    public function categoriaActivo()
    {
        return $this->belongsTo(CategoriaActivo::class, 'id_categoria', 'id_categoria');
    }
}
