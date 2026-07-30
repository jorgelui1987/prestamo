<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\Tenantable;

class Empeno extends Model
{
    use Tenantable;

    protected $table = 'empenos';

    protected $fillable = [
        'codigo', 'cliente_id', 'articulo', 'descripcion', 'valor_tasacion',
        'monto_prestado', 'tasa_interes', 'fecha_inicio', 'fecha_vencimiento', 'estado', 'tenant_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /** Interes a cobrar por el empeno */
    public function getInteresAttribute(): float
    {
        return round((float) $this->monto_prestado * (float) $this->tasa_interes / 100, 2);
    }

    /** Monto total que el cliente debe pagar para recuperar el articulo */
    public function getTotalRecuperarAttribute(): float
    {
        return round((float) $this->monto_prestado + $this->interes, 2);
    }

    /** Dias restantes (negativo si ya vencio) */
    public function getDiasRestantesAttribute(): int
    {
        $venc = Carbon::parse($this->fecha_vencimiento)->startOfDay();
        $hoy = now()->startOfDay();

        return $hoy->lte($venc) ? $hoy->diffInDays($venc) : -$hoy->diffInDays($venc);
    }

    /** Marca como 'vencido' los empenos vigentes cuya fecha ya paso */
    public static function actualizarVencidos(): void
    {
        DB::table('empenos')
            ->where('estado', 'vigente')
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->update(['estado' => 'vencido', 'updated_at' => now()]);
    }
}
