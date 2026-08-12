<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BajaActivo extends Model
{
    protected $table      = 'bajas_activo';
    protected $primaryKey = 'id_baja';

    // La BD gestiona creado_en / actualizado_en
    public $timestamps = false;

    /** Estados de proceso (flujo simplificado). */
    public const ESTADOS = ['REGISTRADA', 'EJECUTADA', 'RECHAZADA'];

    /** Estado en el que la baja sigue en curso (única propuesta pendiente). */
    public const ESTADOS_ABIERTOS = ['REGISTRADA'];

    public const CAUSALES = [
        'DANO_IRREPARABLE', 'OBSOLESCENCIA', 'REPARACION_NO_CONVENIENTE',
        'RAEE', 'SUSTRACCION', 'OTRO',
    ];

    // Flujo anterior (fuera de uso). Referencia histórica:
    // ESTADOS_ABIERTOS = ['REGISTRADA', 'EN_EVALUACION', 'RECOMENDADA', 'VALIDADA'];
    // CAUSALES viejas   = ['DANO', 'OBSOLESCENCIA_TECNICA', 'MANTENIMIENTO_ONEROSO', 'SIN_REPARACION', 'RAEE', 'SUSTRACCION', 'OTRO'];
    // La evaluación técnica (clasificacion_final, valor_referencial, informe) ya no forma
    // parte del flujo nuevo; las columnas se conservan para los datos históricos.
    public const CLASIFICACIONES = [
        'RAEE', 'CHATARRA', 'OBSOLETO', 'SIN_REPARACION', 'NO_DETERMINADO', 'OTRO',
    ];

    protected $fillable = [
        'id_activo', 'id_mantenimiento_origen',
        'registrado_por', 'evaluado_por', 'validado_por', 'ejecutado_por', 'rechazado_por',
        'causal_baja', 'clasificacion_final',
        'motivo', 'diagnostico_tecnico', 'motivo_rechazo',
        'numero_informe_tecnico', 'numero_documento_validacion', 'valor_referencial',
        'estado',
        'fecha_registro', 'fecha_evaluacion', 'fecha_validacion', 'fecha_baja', 'fecha_rechazo',
        'observaciones',
    ];

    protected $casts = [
        'fecha_registro'   => 'date',
        'fecha_evaluacion' => 'date',
        'fecha_validacion' => 'date',
        'fecha_baja'       => 'date',
        'fecha_rechazo'    => 'date',
        'valor_referencial' => 'decimal:2',
        'creado_en'        => 'datetime',
        'actualizado_en'   => 'datetime',
    ];

    /** Folio legible derivado del id (no hay columna de código en el esquema). */
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

    /** Usuario OTI que registró la baja. */
    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }

    /** Colaborador OTI que evaluó técnicamente. */
    public function evaluadoPor()
    {
        return $this->belongsTo(Colaborador::class, 'evaluado_por', 'id_colaborador');
    }

    /** Colaborador OTI que validó internamente la recomendación. */
    public function validadoPor()
    {
        return $this->belongsTo(Colaborador::class, 'validado_por', 'id_colaborador');
    }

    /** Usuario OTI que ejecutó formalmente la baja. */
    public function ejecutadoPor()
    {
        return $this->belongsTo(User::class, 'ejecutado_por', 'id_usuario');
    }

    /** Usuario OTI que rechazó la propuesta. */
    public function rechazadoPor()
    {
        return $this->belongsTo(User::class, 'rechazado_por', 'id_usuario');
    }

    /** Referencias de trámite documentario vinculadas a esta baja. */
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
