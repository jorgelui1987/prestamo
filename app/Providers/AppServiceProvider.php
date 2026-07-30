<?php

namespace App\Providers;

use App\Models\Auditoria;
use App\Support\NotificacionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Modelos auditados => etiqueta de modulo */
    private const AUDITABLES = [
        \App\Models\Cliente::class => 'Clientes',
        \App\Models\Prestamo::class => 'Prestamos',
        \App\Models\Pago::class => 'Pagos',
        \App\Models\Empeno::class => 'Empenos',
        \App\Models\MovimientoCaja::class => 'Caja',
        \App\Models\CorteCaja::class => 'Corte de Caja',
        \App\Models\User::class => 'Usuarios',
        \App\Models\Configuracion::class => 'Configuracion',
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        // Notificaciones para la campana del topbar (solo usuarios autenticados)
        View::composer('layouts.app', function ($view) {
            $view->with('notificaciones', auth()->check() ? NotificacionService::obtener() : ['items' => [], 'total' => 0]);
        });

        $acciones = [
            'created' => 'creo',
            'updated' => 'actualizo',
            'deleted' => 'elimino',
        ];

        foreach ($acciones as $evento => $verbo) {
            Event::listen("eloquent.$evento: *", function (string $eventName, array $data) use ($verbo) {
                $model = $data[0] ?? null;
                if (! $model instanceof Model) {
                    return;
                }

                $clase = get_class($model);
                if (! isset(self::AUDITABLES[$clase]) || ! auth()->check()) {
                    return;
                }

                $referencia = $model->codigo ?? (string) $model->getKey();
                Auditoria::registrar($verbo, self::AUDITABLES[$clase], $referencia);
            });
        }
    }
}
