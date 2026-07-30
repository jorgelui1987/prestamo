<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Tenantable;

class Cliente extends Model
{
    use HasFactory, Tenantable;

    protected $table = 'clientes';

    protected $fillable = [
        'codigo', 'nombres', 'apellidos', 'documento', 'tipo_documento',
        'telefono', 'email', 'direccion', 'ocupacion', 'ingreso_mensual',
        'estado', 'observaciones', 'tenant_id', 'created_by',
    ];

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class);
    }

    public function empenos()
    {
        return $this->hasMany(Empeno::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }

    /** Obtiene el color del semáforo de confianza */
    public function getSemaforoColorAttribute(): string
    {
        // Usar consulta directa a BD en lugar de relación cargada
        $tieneMora = \App\Models\Prestamo::where('cliente_id', $this->id)
            ->where('estado', 'mora')
            ->exists();

        if ($this->estado === 'moroso' || $tieneMora) {
            return '#ef4444'; // Rojo - Mal pagador
        }

        $tieneActivo = \App\Models\Prestamo::where('cliente_id', $this->id)
            ->where('estado', 'activo')
            ->exists();

        if ($tieneActivo) {
            return '#10b981'; // Verde - Al día
        }

        return '#f59e0b'; // Amarillo - Sin préstamos activos
    }

    /** Obtiene la etiqueta del semáforo de confianza */
    public function getSemaforoLabelAttribute(): string
    {
        $tieneMora = \App\Models\Prestamo::where('cliente_id', $this->id)
            ->where('estado', 'mora')
            ->exists();

        if ($this->estado === 'moroso' || $tieneMora) {
            return 'Mal Pagador / En Mora';
        }

        $tieneActivo = \App\Models\Prestamo::where('cliente_id', $this->id)
            ->where('estado', 'activo')
            ->exists();

        if ($tieneActivo) {
            return 'Excelente Pagador / Al Día';
        }

        return 'Sin Préstamos Activos / Regular';
    }
}
