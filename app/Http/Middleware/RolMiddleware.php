<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolMiddleware
{
    /**
     * Restringe el acceso a la ruta según el rol del usuario.
     * Uso: ->middleware('rol:admin,gerente'). El superadmin siempre pasa.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        // El super administrador tiene acceso total.
        if ($user->esSuperAdmin()) {
            return $next($request);
        }

        abort_unless(in_array($user->rol, $roles, true), 403, 'No tienes permisos para acceder a esta sección.');

        return $next($request);
    }
}
