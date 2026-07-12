<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Activo extends Model
{
    use SoftDeletes;

    protected $table      = 'activo';
    protected $primaryKey = 'id_activo';

    public $timestamps = true;
    const CREATED_AT   = 'creado_en';
    const UPDATED_AT   = 'actualizado_en';
    const DELETED_AT   = 'deleted_at';

    /** Condición física del activo (ENUM directo, brief OTI). */
    public const CONDICIONES = ['NUEVO', 'BUENO', 'REGULAR', 'MALO'];

    /** Situación operativa del activo (ENUM directo, brief OTI). */
    public const SITUACIONES = [
        'DISPONIBLE', 'EN_USO', 'EN_PRESTAMO', 'EN_MANTENIMIENTO',
        'EN_PROVEEDOR', 'OBSERVADO', 'DADO_DE_BAJA',
    ];

    /** Etiquetas legibles para la UI. */
    public const CONDICION_LABELS = [
        'NUEVO' => 'Nuevo', 'BUENO' => 'Bueno', 'REGULAR' => 'Regular', 'MALO' => 'Malo',
    ];

    public const SITUACION_LABELS = [
        'DISPONIBLE'       => 'Disponible',
        'EN_USO'           => 'En uso',
        'EN_PRESTAMO'      => 'En préstamo',
        'EN_MANTENIMIENTO' => 'En mantenimiento',
        'EN_PROVEEDOR'     => 'En proveedor',
        'OBSERVADO'        => 'Observado',
        'DADO_DE_BAJA'     => 'Dado de baja',
    ];

    public function condicionLabel(): string
    {
        return self::CONDICION_LABELS[$this->condicion_actual] ?? (string) $this->condicion_actual;
    }

    public function situacionLabel(): string
    {
        return self::SITUACION_LABELS[$this->situacion_actual] ?? (string) $this->situacion_actual;
    }

    protected $fillable = [
        'id_modelo', 'id_categoria', 'id_ubicacion_actual', 'id_responsable_actual',
        'condicion_actual', 'situacion_actual',
        'codigo_interno', 'codigo_patrimonial', 'codigo_siga',
        'numero_pecosa', 'numero_orden_compra', 'fecha_alta_siga', 'numero_serie',
        'descripcion', 'imagen', 'fecha_adquisicion', 'valor_compra', 'proveedor',
        'garantia_inicio', 'garantia_fin', 'observaciones', 'qr_token',
        'origen_registro', 'estado_siga',
        'creado_por', 'actualizado_por',
    ];

    protected $casts = [
        'fecha_alta_siga'    => 'date',
        'fecha_adquisicion'  => 'date',
        'garantia_inicio'    => 'date',
        'garantia_fin'       => 'date',
        'valor_compra'       => 'decimal:2',
    ];

    /**
     * Devuelve el token QR del activo, generándolo y guardándolo si aún no existe.
     */
    public function ensureQrToken(): string
    {
        if (! $this->qr_token) {
            $this->qr_token = (string) Str::uuid();
            $this->save();
        }

        return $this->qr_token;
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'id_modelo', 'id_modelo');
    }

    public function responsable()
    {
        return $this->belongsTo(Colaborador::class, 'id_responsable_actual', 'id_colaborador');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_actual', 'id_ubicacion');
    }

    /** Ficha técnica TI (1-a-1), solo para categorías que la requieren. */
    public function activoTecnico()
    {
        return $this->hasOne(ActivoTecnico::class, 'id_activo', 'id_activo');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaActivo::class, 'id_categoria', 'id_categoria');
    }

    /** Participaciones del activo en movimientos (detalle por activo). */
    public function detallesMovimiento()
    {
        return $this->hasMany(DetalleMovimientoActivo::class, 'id_activo', 'id_activo');
    }

    /** Documentos adjuntos asociados al activo (actas, guías, fotos…). */
    public function documentos()
    {
        return $this->hasMany(DocumentoAdjunto::class, 'entidad_id', 'id_activo')
            ->where('entidad_tipo', 'ACTIVO');
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por', 'id_usuario');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'actualizado_por', 'id_usuario');
    }
}
