<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Cuota extends Model
{
    protected $table = 'cuotas';

    protected $fillable = [
        'prestamo_id', 'numero', 'fecha_vencimiento', 'monto', 'capital',
        'interes', 'mora', 'monto_pagado', 'fecha_pago', 'estado',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class);
    }

    /** Saldo pendiente de la cuota */
    public function getPendienteAttribute(): float
    {
        return round((float) $this->monto + (float) $this->mora - (float) $this->monto_pagado, 2);
    }

    /** Dias de atraso (0 si no esta vencida) */
    public function getDiasAtrasoAttribute(): int
    {
        if ($this->estado === 'pagado') {
            return 0;
        }
        $venc = Carbon::parse($this->fecha_vencimiento)->startOfDay();

        return $venc->isPast() ? $venc->diffInDays(now()->startOfDay()) : 0;
    }

    /**
     * Marca como 'vencido' las cuotas pendientes/parciales cuya fecha ya paso
     * y calcula dinámicamente la mora acumulada según la configuración.
     * Se ejecuta de forma perezosa al abrir las bandejas.
     */
    public static function actualizarVencidas(): void
    {
        $moraDiaria = (float) \App\Models\Configuracion::get('mora_diaria', 1.0);
        $diasGracia = (int) \App\Models\Configuracion::get('dias_gracia', 0);

        // Obtener todas las cuotas pendientes, parciales o ya vencidas cuya fecha de vencimiento ya pasó
        $cuotasVencidas = self::whereIn('estado', ['pendiente', 'parcial', 'vencido'])
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->get();

        foreach ($cuotasVencidas as $cuota) {
            $venc = Carbon::parse($cuota->fecha_vencimiento)->startOfDay();
            $diasAtraso = $venc->diffInDays(now()->startOfDay());

            $mora = 0.0;
            if ($diasAtraso > $diasGracia && $moraDiaria > 0) {
                // Mora = monto de la cuota * % mora diaria * días de atraso
                $mora = round((float) $cuota->monto * ($moraDiaria / 100) * $diasAtraso, 2);
            }

            $cuota->update([
                'estado' => 'vencido',
                'mora' => $mora,
                'updated_at' => now(),
            ]);
        }

        // Prestamos con cuotas vencidas pasan a estado 'mora'
        DB::table('prestamos')
            ->where('estado', 'activo')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('cuotas')
                    ->whereColumn('cuotas.prestamo_id', 'prestamos.id')
                    ->where('cuotas.estado', 'vencido');
            })
            ->update(['estado' => 'mora', 'updated_at' => now()]);
    }
}
