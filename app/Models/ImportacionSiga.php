<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportacionSiga extends Model
{
    protected $table      = 'importaciones_siga';
    protected $primaryKey = 'id_importacion';

    public $timestamps = false;

    protected $fillable = [
        'nombre_archivo', 'tipo_importacion',
        'total_registros', 'registros_correctos', 'registros_observados',
        'estado', 'importado_por', 'creado_en',
    ];

    protected $casts = ['creado_en' => 'datetime'];

    public function detalles()
    {
        return $this->hasMany(ImportacionSigaDetalle::class, 'id_importacion', 'id_importacion');
    }

    public function importadoPor()
    {
        return $this->belongsTo(User::class, 'importado_por', 'id_usuario');
    }
}
