<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoAdjunto extends Model
{
    protected $table      = 'documentos_adjuntos';
    protected $primaryKey = 'id_documento';

    // La BD gestiona creado_en (DEFAULT CURRENT_TIMESTAMP); no hay actualizado_en.
    public $timestamps = false;

    /** Entidades a las que puede adjuntarse un documento (enum de la tabla). */
    public const ENTIDADES = [
        'ACTIVO', 'MOVIMIENTO', 'MANTENIMIENTO', 'BAJA', 'INVENTARIO',
        'SANEAMIENTO', 'ENTREGA_CARGO', 'TRAMITE', 'OTRO',
    ];

    protected $fillable = [
        'entidad_tipo', 'entidad_id',
        'tipo_documento', 'numero_documento', 'fecha_documento',
        'archivo', 'nombre_original', 'extension', 'tamano_kb',
        'descripcion', 'subido_por',
    ];

    protected $casts = [
        'fecha_documento' => 'date',
        'creado_en'       => 'datetime',
    ];

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por', 'id_usuario');
    }
}
