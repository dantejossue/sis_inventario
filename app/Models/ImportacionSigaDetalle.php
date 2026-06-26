<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportacionSigaDetalle extends Model
{
    protected $table      = 'importacion_siga_detalle';
    protected $primaryKey = 'id_importacion_detalle';

    public $timestamps = false;

    protected $fillable = [
        'id_importacion', 'id_activo', 'fila_excel',
        'codigo_patrimonial', 'numero_serie', 'denominacion',
        'estado', 'mensaje', 'datos_raw', 'creado_en',
    ];

    protected $casts = [
        'datos_raw' => 'array',
        'creado_en' => 'datetime',
    ];

    public function importacion()
    {
        return $this->belongsTo(ImportacionSiga::class, 'id_importacion', 'id_importacion');
    }

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }
}
