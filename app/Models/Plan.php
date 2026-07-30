<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';

    protected $fillable = [
        'nombre', 'descripcion', 'precio', 'limite_usuarios', 'limite_clientes', 'limite_prestamos'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'limite_usuarios' => 'integer',
        'limite_clientes' => 'integer',
        'limite_prestamos' => 'integer',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
}
