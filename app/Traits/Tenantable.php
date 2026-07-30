<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait Tenantable
{
    protected static function bootTenantable(): void
    {
        // Aplicar el Global Scope para filtrar por tenant_id y aislar cobradores independientes
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('tenant')) {
                $builder->where($builder->getQuery()->from . '.tenant_id', app('tenant')->id);
            }

            // Si el usuario autenticado es un cobrador, aislar completamente sus datos
            // Evitar bucle infinito si el modelo actual es User
            if (static::class !== \App\Models\User::class && auth()->check() && auth()->user()->rol === 'cobrador') {
                $table = $builder->getQuery()->from;
                if ($table === 'prestamos') {
                    $builder->where('prestamos.cobrador_id', auth()->id());
                } elseif ($table === 'clientes') {
                    // Clientes que tienen préstamos asignados a este cobrador, O que fueron creados por este cobrador
                    $builder->where(function ($query) {
                        $query->whereExists(function ($q) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                                ->from('prestamos')
                                ->whereColumn('prestamos.cliente_id', 'clientes.id')
                                ->where('prestamos.cobrador_id', auth()->id());
                        })->orWhere('clientes.created_by', auth()->id());
                    });
                } elseif ($table === 'pagos') {
                    $builder->where('pagos.user_id', auth()->id());
                } elseif ($table === 'movimientos_caja') {
                    $builder->where('movimientos_caja.user_id', auth()->id());
                } elseif ($table === 'cortes_caja') {
                    $builder->where('cortes_caja.user_id', auth()->id());
                } elseif ($table === 'auditorias') {
                    $builder->where('auditorias.user_id', auth()->id());
                }
            }
        });

        // Asignar automáticamente el tenant_id al crear un nuevo registro
        static::creating(function (Model $model) {
            if (app()->bound('tenant') && !$model->tenant_id) {
                $model->tenant_id = app('tenant')->id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
