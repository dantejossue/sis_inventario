<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Traza de cambios sensibles (brief §19). Referencia polimórfica
 * (entidad_tipo, entidad_id). El registro nunca debe romper la operación
 * principal: registrar() captura cualquier error y solo lo loguea.
 */
class AuditoriaCambio extends Model
{
    protected $table      = 'auditoria_cambios';
    protected $primaryKey = 'id_auditoria';
    public $timestamps    = false;

    public const ACCIONES = ['CREAR', 'ACTUALIZAR', 'ELIMINAR', 'EJECUTAR', 'CANCELAR', 'CERRAR', 'SINCRONIZAR', 'OTRO'];

    protected $fillable = [
        'entidad_tipo', 'entidad_id', 'accion',
        'valores_anteriores', 'valores_nuevos', 'motivo',
        'id_usuario', 'ip', 'user_agent',
    ];

    protected $casts = [
        'valores_anteriores' => 'array',
        'valores_nuevos'     => 'array',
        'creado_en'          => 'datetime',
    ];

    /**
     * Registra un evento de auditoría. Resiliente: si algo falla, no interrumpe
     * la transacción de negocio.
     */
    public static function registrar(
        string $entidadTipo,
        int|string|null $entidadId,
        string $accion,
        ?array $anteriores = null,
        ?array $nuevos = null,
        ?string $motivo = null
    ): void {
        try {
            $req = request();
            static::create([
                'entidad_tipo'       => $entidadTipo,
                'entidad_id'         => (int) $entidadId,
                'accion'             => in_array($accion, self::ACCIONES, true) ? $accion : 'OTRO',
                'valores_anteriores' => $anteriores ?: null,
                'valores_nuevos'     => $nuevos ?: null,
                'motivo'             => $motivo,
                'id_usuario'         => Auth::id(),
                'ip'                 => $req?->ip(),
                'user_agent'         => $req ? substr((string) $req->userAgent(), 0, 255) : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar auditoría: ' . $e->getMessage(), [
                'entidad' => $entidadTipo, 'id' => $entidadId, 'accion' => $accion,
            ]);
        }
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
