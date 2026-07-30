<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Tenantable;

class CorteCaja extends Model
{
    use Tenantable;

    protected $table = 'cortes_caja';

    protected $fillable = [
        'fecha', 'user_id', 'total_cobros', 'total_ingresos', 'total_egresos',
        'saldo_calculado', 'monto_contado', 'diferencia', 'observaciones', 'tenant_id',
    ];

    protected $casts = ['fecha' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
