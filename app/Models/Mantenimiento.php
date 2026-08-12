<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    protected $table      = 'mantenimientos';
    protected $primaryKey = 'id_mantenimiento';

    // La BD gestiona creado_en / actualizado_en
    public $timestamps = false;

    /** Estados en los que el mantenimiento sigue abierto (activo intervenido). */
    public const ESTADOS_ABIERTOS = ['REGISTRADO', 'EN_ATENCION'];

    /** Estados terminales del proceso. */
    public const ESTADOS_FINALES = ['FINALIZADO', 'CANCELADO'];

    /** Resultado técnico independiente del estado de proceso. */
    public const RESULTADOS_ATENCION = ['OPERATIVO', 'RECOMENDADO_BAJA'];

    // Flujo anterior (fuera de uso). Se conserva comentado como referencia histórica:
    // ESTADOS_ABIERTOS  = ['SOLICITADO', 'EN_REVISION', 'EN_MANTENIMIENTO', 'DERIVADO_PROVEEDOR'];
    // ESTADOS_RESULTADO = ['ATENDIDO', 'SIN_REPARACION', 'RECOMENDADO_BAJA'];  (CERRADO era terminal)

    protected $fillable = [
        'id_activo',
        'solicitado_por',
        'registrado_por',
        'tecnico_responsable',
        'tipo_mantenimiento',
        'modalidad_atencion',
        // 'origen_reporte',
        // 'prioridad',
        'descripcion',
        'diagnostico',
        'proveedor',
        'costo',
        'fecha_reporte',
        'fecha_inicio',
        'fecha_fin',
        'resultado',
        'resultado_atencion',
        'recomienda_baja',
        'estado',
    ];

    protected $casts = [
        'fecha_reporte'   => 'date',
        'fecha_inicio'    => 'date',
        'fecha_fin'       => 'date',
        'recomienda_baja' => 'boolean',
        'costo'           => 'decimal:2',
        'creado_en'       => 'datetime',
        'actualizado_en'  => 'datetime',
    ];

    /** Folio legible derivado del id (no hay columna de código en el TO BE). */
    public function getCodigoAttribute(): string
    {
        return 'MANT-' . str_pad((string) $this->id_mantenimiento, 6, '0', STR_PAD_LEFT);
    }

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }

    public function solicitadoPor()
    {
        return $this->belongsTo(Colaborador::class, 'solicitado_por', 'id_colaborador');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }

    public function tecnicoResponsable()
    {
        return $this->belongsTo(Colaborador::class, 'tecnico_responsable', 'id_colaborador');
    }

    /** Historial cronológico de avances técnicos. */
    public function avances()
    {
        return $this->hasMany(MantenimientoAvance::class, 'id_mantenimiento', 'id_mantenimiento')
            ->orderBy('creado_en');
    }

    /** Evidencias adjuntas (documentos transversales). */
    public function documentos()
    {
        return $this->hasMany(DocumentoAdjunto::class, 'entidad_id', 'id_mantenimiento')
            ->where('entidad_tipo', 'MANTENIMIENTO');
    }
}
