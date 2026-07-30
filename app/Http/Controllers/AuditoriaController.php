<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user() && auth()->user()->esAdmin(), 403, 'Acceso restringido a administradores.');

        $userId = $request->query('user_id');
        $accion = $request->query('accion');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $registros = Auditoria::with('user')
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($accion, fn ($q) => $q->where('accion', $accion))
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $usuarios = User::query()
            ->when(!auth()->user()->esSuperAdmin(), fn ($q) => $q->where('rol', '!=', 'superadmin'))
            ->orderBy('name')
            ->get();
        $acciones = Auditoria::query()->select('accion')->distinct()->pluck('accion');

        return view('auditoria.index', compact('registros', 'usuarios', 'acciones', 'userId', 'accion', 'desde', 'hasta'));
    }
}
