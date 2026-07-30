<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use Illuminate\Http\Request;

class CobranzaController extends Controller
{
    /** Bandeja de cobranza: cuotas por vencer y vencidas */
    public function index(Request $request)
    {
        Cuota::actualizarVencidas();

        $filtro = $request->query('filtro', 'todas'); // todas / hoy / vencidas / proximas
        $buscar = $request->query('q');
        
        $user = auth()->user();
        $isCobrador = $user->rol === 'cobrador';
        $cobradorId = $isCobrador ? $user->id : $request->query('cobrador_id');

        $query = Cuota::query()
            ->select('cuotas.*')
            ->join('prestamos', 'cuotas.prestamo_id', '=', 'prestamos.id')
            ->with('prestamo.cliente')
            ->whereIn('cuotas.estado', ['pendiente', 'parcial', 'vencido'])
            ->when($cobradorId, fn ($q) => $q->where('prestamos.cobrador_id', $cobradorId))
            ->when($buscar, fn ($q) => $q->whereHas('prestamo.cliente', function ($c) use ($buscar) {
                $c->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('documento', 'like', "%{$buscar}%");
            }));

        match ($filtro) {
            'hoy' => $query->whereDate('cuotas.fecha_vencimiento', now()->toDateString()),
            'vencidas' => $query->where('cuotas.estado', 'vencido'),
            'proximas' => $query->whereDate('cuotas.fecha_vencimiento', '>=', now()->toDateString())
                                ->whereDate('cuotas.fecha_vencimiento', '<=', now()->addDays(7)->toDateString()),
            default => null,
        };

        // Agrupar por préstamo para que solo salga una fila por cliente/préstamo con la cuota más antigua pendiente
        // Usamos una subconsulta o un filtro para obtener solo la cuota con el ID más bajo (la más antigua) de cada préstamo pendiente
        $query->whereIn('cuotas.id', function ($subQuery) {
            $subQuery->select(\Illuminate\Support\Facades\DB::raw('MIN(c2.id)'))
                ->from('cuotas as c2')
                ->whereIn('c2.estado', ['pendiente', 'parcial', 'vencido'])
                ->groupBy('c2.prestamo_id');
        });

        // Ordenar por orden de ruta de forma ascendente, y luego por fecha de vencimiento
        $cuotas = $query->orderBy('prestamos.orden_ruta')
            ->orderBy('cuotas.fecha_vencimiento')
            ->paginate(15)
            ->withQueryString();

        $resumen = [
            'vencidas' => Cuota::whereHas('prestamo')->where('estado', 'vencido')
                ->when($cobradorId, fn ($q) => $q->whereHas('prestamo', fn($p) => $p->where('cobrador_id', $cobradorId)))
                ->count(),
            'hoy' => Cuota::whereHas('prestamo')->whereIn('estado', ['pendiente', 'parcial'])
                ->whereDate('fecha_vencimiento', now()->toDateString())
                ->when($cobradorId, fn ($q) => $q->whereHas('prestamo', fn($p) => $p->where('cobrador_id', $cobradorId)))
                ->count(),
            'porCobrar' => (float) Cuota::whereHas('prestamo')->whereIn('estado', ['pendiente', 'parcial', 'vencido'])
                ->when($cobradorId, fn ($q) => $q->whereHas('prestamo', fn($p) => $p->where('cobrador_id', $cobradorId)))
                ->sum(\Illuminate\Support\Facades\DB::raw('monto - monto_pagado')),
            'montoVencido' => (float) Cuota::whereHas('prestamo')->where('estado', 'vencido')
                ->when($cobradorId, fn ($q) => $q->whereHas('prestamo', fn($p) => $p->where('cobrador_id', $cobradorId)))
                ->sum(\Illuminate\Support\Facades\DB::raw('monto - monto_pagado')),
        ];

        $cobradores = \App\Models\User::whereIn('rol', ['cobrador', 'operador', 'admin', 'gerente'])->orderBy('name')->get();

        return view('cobranzas.index', compact('cuotas', 'filtro', 'buscar', 'resumen', 'cobradores', 'cobradorId', 'isCobrador'));
    }

    /** Bandeja de Mora: solo cuotas vencidas */
    public function mora(Request $request)
    {
        Cuota::actualizarVencidas();
        $buscar = $request->query('q');

        $cuotas = Cuota::query()
            ->with('prestamo.cliente')
            ->where('estado', 'vencido')
            ->when($buscar, fn ($q) => $q->whereHas('prestamo.cliente', function ($c) use ($buscar) {
                $c->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('documento', 'like', "%{$buscar}%");
            }))
            ->orderBy('fecha_vencimiento')
            ->paginate(15)
            ->withQueryString();

        $resumen = [
            'cuotas' => Cuota::whereHas('prestamo')->where('estado', 'vencido')->count(),
            'monto' => (float) Cuota::whereHas('prestamo')->where('estado', 'vencido')->sum(\Illuminate\Support\Facades\DB::raw('monto - monto_pagado')),
            'prestamos' => \App\Models\Prestamo::where('estado', 'mora')->count(),
        ];

        return view('cobranzas.mora', compact('cuotas', 'buscar', 'resumen'));
    }

}
