<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use Notifiable;

    protected $table      = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public    $timestamps = false;

    protected $fillable = [
        'id_colaborador',
        'id_rol',
        'nombre_usuario',
        'contrasena',
        'estado',
        'ultimo_acceso_en',
        'fecha_cambio_clave_en',
        'intentos_fallidos',
        'bloqueado_hasta',
        'fecha_baja',
    ];

    protected $hidden = [
        'contrasena',
    ];

    // Laravel usa este método para verificar la contraseña en Auth::login / Hash::check
    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    // ── Relaciones ────────────────────────────────────────────────────

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'id_colaborador', 'id_colaborador');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    // ── Helpers de autorización (usados en Blade y Policies) ──────────

    public function esAdministrador(): bool
    {
        return $this->rol->nombre === 'ADMINISTRADOR';
    }

    public function esOperador(): bool
    {
        return $this->rol->nombre === 'OPERADOR';
    }

    public function esAlmacen(): bool
    {
        return $this->rol->nombre === 'ALMACEN';
    }

    public function esServiciosGenerales(): bool
    {
        return $this->rol->nombre === 'SERVICIOS_GENERALES';
    }

    public function esProveedor(): bool
    {
        return $this->rol->nombre === 'PROVEEDOR';
    }

    /** Devuelve true si el usuario está bloqueado por intentos fallidos */
    public function estaBloqueado(): bool
    {
        return $this->bloqueado_hasta !== null
            && now()->lt($this->bloqueado_hasta);
    }
}
