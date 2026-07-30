<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Tenantable;

class Pago extends Model
{
    use Tenantable;

    protected $table = 'pagos';

    protected $fillable = [
        'codigo', 'prestamo_id', 'cuota_id', 'monto', 'fecha_pago',
        'metodo', 'referencia', 'user_id', 'tenant_id', 'latitud', 'longitud',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class);
    }
}
