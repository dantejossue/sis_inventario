<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'movimientos';

    protected $primaryKey = 'id_movimiento';

    // La BD gestiona creado_en / actualizado_en
    public $timestamps = false;

    /** Tipos de movimiento interno OTI (la devolución NO es tipo). */
    public const TIPOS = ['PRESTAMO', 'TRANSFERENCIA', 'REGULARIZACION'];

    /** Estados del movimiento; solo EJECUTADO impacta el activo. */
    public const ESTADOS = ['BORRADOR', 'EJECUTADO', 'OBSERVADO', 'CANCELADO'];

    protected $casts = [
        'fecha_registro'            => 'datetime',
        'fecha_movimiento'          => 'datetime',
        'fecha_devolucion_estimada' => 'date',
        'fecha_devolucion_real'     => 'date',
        'fecha_ejecucion'           => 'datetime',
        'fecha_cancelacion'         => 'datetime',
        'requiere_tramite'          => 'boolean',
    ];

    protected $fillable = [
        'codigo_movimiento',
        'tipo',
        'estado',
        'fecha_registro',
        'fecha_movimiento',
        'fecha_devolucion_estimada',
        'fecha_devolucion_real',
        'estado_devolucion',
        'motivo',
        'observaciones',
        'observacion_devolucion',
        'requiere_tramite',
        'numero_tramite',
        'documento_referencia',
        'registrado_por',
        'ejecutado_por',
        'cancelado_por',
        'fecha_ejecucion',
        'fecha_cancelacion',
        'motivo_cancelacion',
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleMovimientoActivo::class, 'id_movimiento', 'id_movimiento');
    }

    /** Documentos de sustento del movimiento (actas de entrega/conformidad). */
    public function documentos()
    {
        return $this->hasMany(DocumentoAdjunto::class, 'entidad_id', 'id_movimiento')
            ->where('entidad_tipo', 'MOVIMIENTO');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }

    public function ejecutadoPor()
    {
        return $this->belongsTo(User::class, 'ejecutado_por', 'id_usuario');
    }

    public function canceladoPor()
    {
        return $this->belongsTo(User::class, 'cancelado_por', 'id_usuario');
    }

    /** Activos abarcados por este movimiento (a través del detalle). */
    public function activos()
    {
        return $this->belongsToMany(Activo::class, 'detalle_movimiento_activo', 'id_movimiento', 'id_activo', 'id_movimiento', 'id_activo');
    }
}
