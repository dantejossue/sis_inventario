<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivoTecnico extends Model
{
    protected $table      = 'activo_tecnico';
    protected $primaryKey = 'id_activo_tecnico';

    public $timestamps = true;
    const CREATED_AT   = 'creado_en';
    const UPDATED_AT   = 'actualizado_en';

    protected $fillable = [
        'id_activo',
        'procesador', 'memoria_ram', 'almacenamiento', 'tipo_almacenamiento',
        'sistema_operativo', 'direccion_mac', 'direccion_ip', 'nombre_equipo', 'dominio',
        'licencia_office', 'antivirus', 'accesorios', 'observaciones_tecnicas',
        'estado_operativo',
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }
}
