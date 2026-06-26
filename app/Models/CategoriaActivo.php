<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaActivo extends Model
{
    protected $table      = 'categoria_activo';
    protected $primaryKey = 'id_categoria';

    public $timestamps = true;
    const CREATED_AT   = 'creado_en';
    const UPDATED_AT   = 'actualizado_en';

    protected $fillable = ['nombre', 'descripcion', 'requiere_ficha_tecnica', 'estado'];

    protected $casts = ['requiere_ficha_tecnica' => 'boolean'];

    public function modelos()
    {
        return $this->hasMany(Modelo::class, 'id_categoria', 'id_categoria');
    }
}
