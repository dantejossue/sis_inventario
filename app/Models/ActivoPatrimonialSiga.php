<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivoPatrimonialSiga extends Model
{
    protected $table      = 'activo_patrimonial_siga';
    protected $primaryKey = 'id_activo_patrimonial_siga';

    public $timestamps = false;

    protected $fillable = [
        'id_activo', 'id_importacion', 'id_importacion_detalle',
        'sbn', 'descripcion_siga', 'sede_siga', 'centro_costos', 'proveedor_siga',
        'fecha_compra', 'valor_adquisicion', 'fecha_alta', 'valor_libros', 'valor_neto',
        'codigo_sede_siga', 'codigo_ubicacion_siga', 'sede_ubicacion_siga', 'unidad_ejecutora',
        'cantidad', 'codigo_barras_anterior', 'numero_oc', 'medidas', 'tipo_ingreso', 'correlativo',
        'cuenta_contable', 'estado_conservacion_siga', 'condicion_siga', 'grupo', 'clase', 'familia',
        'item_correlativo', 'color', 'observaciones_siga', 'observaciones_adicionales',
        'fecha_importacion',
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }
}
