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

    protected $fillable = [
        'id_modelo', 'id_categoria', 'id_ubicacion_actual', 'id_responsable_actual',
        'id_condicion_actual', 'id_situacion_actual',
        'codigo_interno', 'codigo_patrimonial', 'codigo_siga',
        'numero_pecosa', 'numero_orden_compra', 'fecha_alta_siga', 'numero_serie',
        'descripcion', 'imagen', 'fecha_adquisicion', 'valor_compra', 'proveedor',
        'garantia_inicio', 'garantia_fin', 'observaciones', 'qr_token',
        'origen_registro', 'estado_validacion', 'estado_siga', 'id_importacion',
        'creado_por', 'actualizado_por',
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

    public function condicion()
    {
        return $this->belongsTo(EstadoActivo::class, 'id_condicion_actual', 'id_estado_activo');
    }

    public function situacion()
    {
        return $this->belongsTo(EstadoActivo::class, 'id_situacion_actual', 'id_estado_activo');
    }

    public function responsable()
    {
        return $this->belongsTo(Colaborador::class, 'id_responsable_actual', 'id_colaborador');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_actual', 'id_ubicacion');
    }
}
