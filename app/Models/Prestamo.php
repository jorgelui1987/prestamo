<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Traits\Tenantable;

class Prestamo extends Model
{
    use HasFactory, Tenantable;

    protected $table = 'prestamos';

    protected $fillable = [
        'codigo', 'cliente_id', 'monto', 'tasa_interes', 'numero_cuotas',
        'frecuencia', 'monto_cuota', 'total_pagar', 'interes_total', 'saldo',
        'fecha_inicio', 'estado', 'observaciones', 'user_id', 'tenant_id', 'cobrador_id', 'orden_ruta',
        'numero_boleta', 'costo_boleta', 'firma_ruta',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'monto' => 'decimal:2',
        'saldo' => 'decimal:2',
        'total_pagar' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cobrador()
    {
        return $this->belongsTo(User::class, 'cobrador_id');
    }

    /**
     * Recalcula el saldo y el estado del prestamo a partir de sus cuotas.
     */
    public function recalcularEstado(): void
    {
        $this->loadMissing('cuotas');

        $pagado = (float) $this->cuotas->sum('monto_pagado');
        $this->saldo = max(round((float) $this->total_pagar - $pagado, 2), 0);

        if ($this->saldo <= 0) {
            $this->estado = 'pagado';
        } else {
            $tieneVencidas = $this->cuotas->contains(function ($c) {
                return $c->estado !== 'pagado'
                    && Carbon::parse($c->fecha_vencimiento)->startOfDay()->isPast();
            });
            $this->estado = $tieneVencidas ? 'mora' : 'activo';
        }

        $this->save();
    }
}
