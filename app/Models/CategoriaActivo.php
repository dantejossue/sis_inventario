<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaActivo extends Model
{
    protected $table      = 'categoria_activo';
    protected $primaryKey = 'id_categoria';

    public $timestamps = true;
    const CREATED_AT   = 'creado_en';
    const UPDATED_AT   = 'actualizado_en';

    protected $fillable = ['nombre', 'descripcion', 'icono', 'requiere_ficha_tecnica', 'estado'];

    protected $casts = ['requiere_ficha_tecnica' => 'boolean'];

    /** Íconos Boxicons disponibles para representar tipos de activo. */
    public const ICONOS = [
        'bx-laptop', 'bx-desktop', 'bx-server', 'bx-hdd', 'bx-chip',
        'bx-network-chart', 'bx-wifi', 'bx-broadcast', 'bx-devices',
        'bx-tv', 'bx-printer', 'bx-video-recording', 'bx-camera',
        'bx-plug', 'bx-mobile', 'bx-tab', 'bx-headphone', 'bx-mouse',
        'bx-keyboard', 'bx-projector', 'bx-microchip', 'bx-package',
    ];

    /** Ícono efectivo (con fallback genérico). */
    public function iconoOrDefault(): string
    {
        return $this->icono ?: 'bx-package';
    }

    public function modelos()
    {
        return $this->hasMany(Modelo::class, 'id_categoria', 'id_categoria');
    }
}
