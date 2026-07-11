<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BajaActivo extends Model
{
    protected $table      = 'bajas_activo';
    protected $primaryKey = 'id_baja';

    // La BD gestiona creado_en / actualizado_en
    public $timestamps = false;

    /** Estados en los que la propuesta de baja sigue en curso. */
    public const ESTADOS_ABIERTOS = ['SOLICITADA', 'EN_EVALUACION', 'RECOMENDADA', 'APROBADA'];

    public const CAUSALES = [
        'DANO', 'OBSOLESCENCIA_TECNICA', 'RAEE', 'MANTENIMIENTO_ONEROSO', 'SIN_REPARACION',
        'SANEAMIENTO_FALTANTE', 'SUSTRACCION', 'GARANTIA', 'OTRO',
    ];

    protected $fillable = [
        'id_activo', 'id_mantenimiento_origen',
        'solicitado_por', 'evaluado_por', 'aprobado_por',
        'causal_baja', 'motivo', 'diagnostico_tecnico',
        'estado', 'estado_siga',
        'fecha_solicitud', 'fecha_evaluacion', 'fecha_aprobacion', 'fecha_baja',
        'observaciones',
    ];

    protected $casts = [
        'fecha_solicitud'  => 'date',
        'fecha_evaluacion' => 'date',
        'fecha_aprobacion' => 'date',
        'fecha_baja'       => 'date',
        'creado_en'        => 'datetime',
        'actualizado_en'   => 'datetime',
    ];

    /** Folio legible derivado del id (no hay columna de código en el TO BE). */
    public function getCodigoAttribute(): string
    {
        return 'BAJA-' . str_pad((string) $this->id_baja, 6, '0', STR_PAD_LEFT);
    }

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }

    public function mantenimientoOrigen()
    {
        return $this->belongsTo(Mantenimiento::class, 'id_mantenimiento_origen', 'id_mantenimiento');
    }

    public function solicitadoPor()
    {
        return $this->belongsTo(Colaborador::class, 'solicitado_por', 'id_colaborador');
    }

    public function evaluadoPor()
    {
        return $this->belongsTo(Colaborador::class, 'evaluado_por', 'id_colaborador');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'aprobado_por', 'id_usuario');
    }

    /** Expediente(s) de trámite documentario vinculados a esta baja. */
    public function tramites()
    {
        return $this->hasMany(TramiteReferencia::class, 'entidad_id', 'id_baja')
            ->where('entidad_tipo', 'BAJA');
    }

    /** Documentos de sustento (informes, actas, fotos). */
    public function documentos()
    {
        return $this->hasMany(DocumentoAdjunto::class, 'entidad_id', 'id_baja')
            ->where('entidad_tipo', 'BAJA');
    }
}
