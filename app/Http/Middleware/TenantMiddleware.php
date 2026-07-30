<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            $tenant = $user->tenant;

            // Registrar el tenant actual en el contenedor de servicios
            app()->instance('tenant', $tenant);

            // Compartir el tenant con todas las vistas de Blade
            view()->share('currentTenant', $tenant);

            // Bloquear si la empresa está inactiva (excepto superadmin)
            if (!$user->esSuperAdmin() && $tenant && !$tenant->activo) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                abort(403, 'Tu empresa ha sido desactivada. Contacta al administrador de la plataforma.');
            }
        }

        return $next($request);
    }
}
