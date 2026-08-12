<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Avance técnico de un mantenimiento. Cada registro es un asiento histórico
 * (no se sobrescribe). Las evidencias del avance se guardan en
 * documentos_adjuntos (entidad MANTENIMIENTO) enlazadas por id_avance.
 */
class MantenimientoAvance extends Model
{
    protected $table      = 'mantenimiento_avances';
    protected $primaryKey = 'id_avance';

    // La BD gestiona creado_en / actualizado_en.
    public $timestamps = false;

    protected $fillable = [
        'id_mantenimiento',
        'diagnostico',
        'actividad_realizada',
        'observacion',
        'costo',
        'registrado_por',
    ];

    protected $casts = [
        'costo'          => 'decimal:2',
        'creado_en'      => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'id_mantenimiento', 'id_mantenimiento');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }

    /** Evidencias adjuntas a este avance (documentos transversales por id_avance). */
    public function documentos()
    {
        return $this->hasMany(DocumentoAdjunto::class, 'id_avance', 'id_avance');
    }
}
