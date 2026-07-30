<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Tenantable;

class VisitaSinExito extends Model
{
    use Tenantable;

    protected $table = 'visitas_sin_exito';

    protected $fillable = [
        'prestamo_id', 'user_id', 'tenant_id', 'fecha', 'motivo', 'observaciones',
        'fecha_promesa', 'promesa_cumplida', 'latitud', 'longitud',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_promesa' => 'date',
        'promesa_cumplida' => 'boolean',
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
