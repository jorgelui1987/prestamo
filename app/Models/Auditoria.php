<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Tenantable;

class Auditoria extends Model
{
    use Tenantable;

    protected $table = 'auditorias';

    protected $fillable = [
        'user_id', 'usuario_nombre', 'accion', 'modulo', 'referencia', 'descripcion', 'ip', 'tenant_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Registra una entrada en la bitacora */
    public static function registrar(string $accion, ?string $modulo = null, ?string $referencia = null, ?string $descripcion = null): void
    {
        $user = auth()->user();

        static::create([
            'user_id' => $user?->id,
            'usuario_nombre' => $user?->name ?? 'Sistema',
            'accion' => $accion,
            'modulo' => $modulo,
            'referencia' => $referencia,
            'descripcion' => $descripcion,
            'ip' => request()->ip(),
        ]);
    }
}
