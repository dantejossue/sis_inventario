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
        'bx-laptop',
        'bx-desktop',
        'bxs-server',
        'bxs-hdd',
        'bxs-chip',
        'bxs-network-chart',
        'bx-wifi',
        'bx-broadcast',
        'bxs-devices',
        'bxs-tv',
        'bxs-printer',
        'bxs-video-recording',
        'bxs-camera',
        'bxs-plug',
        'bxs-mobile',
        'bx-tab',
        'bx-headphone',
        'bxs-mouse',
        'bxs-keyboard',
        'bx-projector',
        'bxs-microchip',
        'bxs-package',
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
