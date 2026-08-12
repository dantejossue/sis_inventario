<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Colaborador extends Model
{
    protected $table      = 'colaboradores';
    protected $primaryKey = 'id_colaborador';
    public    $timestamps = false; // Usamos creado_en / actualizado_en personalizados

    protected $fillable = [
        // Datos personales
        'nro_documento',
        'per_nombre',
        'per_apepat',
        'per_apemat',
        'fecha_nacimiento',
        'nro_movil',
        'per_email',
        'per_direccion',
        'per_foto',
        // Datos laborales
        'id_sede_dependencia',
        'cargo',
        'tipo_colaborador',
        'fecha_ingreso',
        'fecha_salida',
        // Control
        'estado',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso'    => 'date',
        'fecha_salida'     => 'date',
        'creado_en'        => 'datetime',
        'actualizado_en'   => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────────────

    public function sedeDependencia(): BelongsTo
    {
        return $this->belongsTo(Sede_Dependencia::class, 'id_sede_dependencia', 'id_sede_dependencia');
    }

    public function usuario(): HasOne
    {
        return $this->hasOne(User::class, 'id_colaborador', 'id_colaborador');
    }

    // ── Accessors ─────────────────────────────────────────────────────

    /** Devuelve el nombre completo concatenado: "DANTE JOSSUE CAMPOS OCHOA" */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->per_nombre} {$this->per_apepat} {$this->per_apemat}");
    }

    /** Devuelve las iniciales para avatares: "DC" */
    public function getInicialesAttribute(): string
    {
        return strtoupper(
            substr($this->per_nombre, 0, 1) . substr($this->per_apepat, 0, 1)
        );
    }

    public const CARGOS = [
        'JEFE' => 'Jefe',
        'ESPECIALISTA' => 'Especialista',
        'TECNICO' => 'Técnico',
        'ASISTENTE' => 'Asistente',
        'OTRO' => 'Otro',
    ];
}
