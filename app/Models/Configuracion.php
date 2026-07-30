<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Traits\Tenantable;

class Configuracion extends Model
{
    use Tenantable;

    protected $table = 'configuraciones';

    protected $fillable = ['clave', 'valor', 'grupo', 'tenant_id'];

    public $timestamps = true;

    /** Obtiene un valor de configuracion (con cache aislada por tenant) */
    public static function get(string $clave, $default = null)
    {
        $tenantId = app()->bound('tenant') ? app('tenant')->id : null;
        $cacheKey = 'config_all_' . ($tenantId ?? 'global');

        $all = Cache::rememberForever($cacheKey, function () use ($tenantId) {
            $query = static::withoutGlobalScopes();
            
            if ($tenantId) {
                // Usuario autenticado: filtrar por su tenant
                $query->where('tenant_id', $tenantId);
            } else {
                // Sin autenticar (login): buscar configs sin tenant especifico
                $query->whereNull('tenant_id');
            }
            
            return $query->pluck('valor', 'clave')
                ->toArray();
        });

        // Si no se encontro valor y no hay tenant (login), buscar en cualquier tenant como fallback
        if (!isset($all[$clave]) && !$tenantId) {
            $fallback = static::withoutGlobalScopes()
                ->where('clave', $clave)
                ->whereNotNull('tenant_id')
                ->orderBy('tenant_id')
                ->value('valor');
            
            if ($fallback) {
                return $fallback;
            }
        }

        return $all[$clave] ?? $default;
    }

    /** Guarda un valor de configuracion */
    public static function set(string $clave, $valor, string $grupo = 'general'): void
    {
        $tenantId = app()->bound('tenant') ? app('tenant')->id : null;

        static::withoutGlobalScopes()->updateOrCreate(
            [
                'clave' => $clave,
                'tenant_id' => $tenantId,
            ],
            [
                'valor' => $valor,
                'grupo' => $grupo,
            ]
        );
        
        $cacheKey = 'config_all_' . ($tenantId ?? 'global');
        Cache::forget($cacheKey);
    }

    protected static function booted(): void
    {
        static::bootTenantable();

        static::saved(function () {
            $tenantId = app()->bound('tenant') ? app('tenant')->id : null;
            Cache::forget('config_all_' . ($tenantId ?? 'global'));
        });
        static::deleted(function () {
            $tenantId = app()->bound('tenant') ? app('tenant')->id : null;
            Cache::forget('config_all_' . ($tenantId ?? 'global'));
        });
    }
}
