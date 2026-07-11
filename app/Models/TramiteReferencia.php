<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TramiteReferencia extends Model
{
    protected $table      = 'tramites_referencias';
    protected $primaryKey = 'id_tramite_referencia';

    // La BD gestiona creado_en / actualizado_en
    public $timestamps = false;

    /** Entidades vinculables (enum de la tabla). */
    public const ENTIDADES = [
        'ACTIVO', 'MOVIMIENTO', 'MANTENIMIENTO', 'BAJA', 'INVENTARIO',
        'SANEAMIENTO', 'ENTREGA_CARGO', 'OTRO',
    ];

    protected $fillable = [
        'entidad_tipo', 'entidad_id',
        'numero_expediente', 'numero_documento', 'tipo_documento', 'asunto',
        'sistema_origen', 'estado_tramite',
        'fecha_inicio', 'fecha_respuesta', 'url_tramite',
        'observaciones', 'registrado_por',
    ];

    protected $casts = [
        'fecha_inicio'    => 'date',
        'fecha_respuesta' => 'date',
        'creado_en'       => 'datetime',
    ];

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }
}
