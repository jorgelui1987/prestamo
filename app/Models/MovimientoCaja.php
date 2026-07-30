<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Tenantable;

class MovimientoCaja extends Model
{
    use Tenantable;

    protected $table = 'movimientos_caja';

    protected $fillable = [
        'codigo', 'fecha', 'tipo', 'categoria', 'concepto', 'monto', 'metodo', 'user_id', 'tenant_id',
    ];

    protected $casts = ['fecha' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
