<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use App\Traits\Tenantable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Tenantable;

    protected $fillable = [
        'name', 'email', 'password', 'rol', 'telefono', 'avatar', 'activo', 'tenant_id',
        'meta_diaria',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'two_factor_enabled' => 'boolean',
        ];
    }

    public function esAdmin(): bool
    {
        return in_array($this->rol, ['admin', 'superadmin'], true);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /** True solo para el super administrador de la plataforma */
    public function esSuperAdmin(): bool
    {
        return $this->rol === 'superadmin';
    }

    /** Etiqueta legible del rol */
    public function getRolLabelAttribute(): string
    {
        return [
            'superadmin' => 'Super Administrador',
            'admin' => 'Administrador',
            'gerente' => 'Gerente',
            'operador' => 'Operador',
            'cobrador' => 'Cobrador',
        ][$this->rol] ?? ucfirst((string) $this->rol);
    }

    /** URL publica del avatar, o null si no tiene */
    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return \App\Support\StorageHelper::url($this->avatar);
    }

    /** Inicial para el avatar por defecto */
    public function getInicialAttribute(): string
    {
        return strtoupper(mb_substr($this->name ?? 'U', 0, 1));
    }
}
