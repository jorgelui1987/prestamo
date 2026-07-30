<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empeno;
use App\Models\Prestamo;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $user = auth()->user();
        $puedeCredito = $user->esSuperAdmin() || in_array($user->rol, ['admin', 'gerente', 'operador'], true);

        $clientes = collect();
        $prestamos = collect();
        $empenos = collect();

        if (mb_strlen($q) >= 2) {
            $like = "%{$q}%";

            $clientes = Cliente::query()
                ->where('nombres', 'like', $like)
                ->orWhere('apellidos', 'like', $like)
                ->orWhere('documento', 'like', $like)
                ->orWhere('codigo', 'like', $like)
                ->orWhere('telefono', 'like', $like)
                ->limit(15)->get();

            if ($puedeCredito) {
                $prestamos = Prestamo::with('cliente')
                    ->where('codigo', 'like', $like)
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombres', 'like', $like)->orWhere('apellidos', 'like', $like))
                    ->limit(15)->get();

                $empenos = Empeno::with('cliente')
                    ->where('codigo', 'like', $like)
                    ->orWhere('articulo', 'like', $like)
                    ->limit(15)->get();
            }
        }

        $total = $clientes->count() + $prestamos->count() + $empenos->count();

        return view('buscar.index', compact('q', 'clientes', 'prestamos', 'empenos', 'total'));
    }
}
