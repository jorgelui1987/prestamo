<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VisitaSinExito;
use Illuminate\Http\Request;

class PromesasController extends Controller
{
    public function index(Request $request)
    {
        $cobradorId = $request->query('cobrador_id');
        $desde = $request->query('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->query('hasta', now()->toDateString());
        $estado = $request->query('estado');

        $query = VisitaSinExito::with(['prestamo.cliente', 'user'])
            ->where('motivo', 'promesa_pago')
            ->whereBetween('fecha', [$desde, $hasta])
            ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
            ->when($estado === 'pendiente', fn($q) => $q->where('promesa_cumplida', false))
            ->when($estado === 'cumplida', fn($q) => $q->where('promesa_cumplida', true));

        $promesas = $query->latest('fecha_promesa')->paginate(20)->withQueryString();

        $cobradores = User::where('rol', 'cobrador')->orderBy('name')->get();

        $totales = [
            'total' => VisitaSinExito::where('motivo', 'promesa_pago')
                ->whereBetween('fecha', [$desde, $hasta])
                ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
                ->count(),
            'cumplidas' => VisitaSinExito::where('motivo', 'promesa_pago')
                ->where('promesa_cumplida', true)
                ->whereBetween('fecha', [$desde, $hasta])
                ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
                ->count(),
            'pendientes' => VisitaSinExito::where('motivo', 'promesa_pago')
                ->where('promesa_cumplida', false)
                ->whereBetween('fecha', [$desde, $hasta])
                ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
                ->count(),
        ];

        return view('reportes.promesas', compact('promesas', 'cobradores', 'cobradorId', 'desde', 'hasta', 'estado', 'totales'));
    }

    public function cumplir(VisitaSinExito $visita)
    {
        $visita->update(['promesa_cumplida' => true]);

        return back()->with('ok', 'Promesa de pago marcada como cumplida.');
    }
}