<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Pago;
use App\Models\Prestamo;
use App\Models\User;
use App\Models\VisitaSinExito;
use Illuminate\Http\Request;

class ReporteEfectividadController extends Controller
{
    public function index(Request $request)
    {
        $cobradorId = $request->query('cobrador_id');
        $desde = $request->query('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->query('hasta', now()->toDateString());

        $cobradores = User::where('rol', 'cobrador')->orderBy('name')->get();
        $reportes = [];

        foreach ($cobradores as $cob) {
            if ($cobradorId && $cob->id != $cobradorId) continue;

            // Total de cuotas asignadas en el período
            $totalCuotasAsignadas = Cuota::whereHas('prestamo', fn($p) => $p->where('cobrador_id', $cob->id))
                ->whereBetween('fecha_vencimiento', [$desde, $hasta])
                ->count();

            // Total cobrado en el período
            $totalCobrado = (float) Pago::where('user_id', $cob->id)
                ->whereBetween('fecha_pago', [$desde, $hasta])
                ->sum('monto');

            // Cantidad de cobros realizados
            $cobrosRealizados = Pago::where('user_id', $cob->id)
                ->whereBetween('fecha_pago', [$desde, $hasta])
                ->count();

            // Visitas sin éxito
            $visitasSinExito = VisitaSinExito::where('user_id', $cob->id)
                ->whereBetween('fecha', [$desde, $hasta])
                ->count();

            // Promesas de pago registradas
            $promesasRegistradas = VisitaSinExito::where('user_id', $cob->id)
                ->whereBetween('fecha', [$desde, $hasta])
                ->where('motivo', 'promesa_pago')
                ->count();

            // Promesas cumplidas
            $promesasCumplidas = VisitaSinExito::where('user_id', $cob->id)
                ->whereBetween('fecha', [$desde, $hasta])
                ->where('motivo', 'promesa_pago')
                ->where('promesa_cumplida', true)
                ->count();

            // Cartera asignada (monto total)
            $carteraAsignada = (float) Prestamo::where('cobrador_id', $cob->id)
                ->whereIn('estado', ['activo', 'mora'])
                ->sum('saldo');

            // Tasa de cobro = cobros / (cobros + visitas sin éxito) * 100
            $totalGestiones = $cobrosRealizados + $visitasSinExito;
            $tasaCobro = $totalGestiones > 0 ? round(($cobrosRealizados / $totalGestiones) * 100, 1) : 0;

            // Promedio por cobro
            $promedioPorCobro = $cobrosRealizados > 0 ? round($totalCobrado / $cobrosRealizados, 2) : 0;

            // Tasa de cumplimiento de promesas
            $tasaPromesas = $promesasRegistradas > 0 ? round(($promesasCumplidas / $promesasRegistradas) * 100, 1) : 0;

            // Meta individual
            $metaDiaria = (float) $cob->meta_diaria;
            $diasLaborables = max(1, now()->parse($desde)->diffInDays(now()->parse($hasta)) + 1);
            $metaPeriodo = $metaDiaria * $diasLaborables;
            $porcentajeMeta = $metaPeriodo > 0 ? min(round(($totalCobrado / $metaPeriodo) * 100), 100) : 0;

            $reportes[] = [
                'cobrador' => $cob->name,
                'total_cuotas' => $totalCuotasAsignadas,
                'cobros_realizados' => $cobrosRealizados,
                'total_cobrado' => $totalCobrado,
                'visitas_sin_exito' => $visitasSinExito,
                'tasa_cobro' => $tasaCobro,
                'promedio_por_cobro' => $promedioPorCobro,
                'promesas_registradas' => $promesasRegistradas,
                'promesas_cumplidas' => $promesasCumplidas,
                'tasa_promesas' => $tasaPromesas,
                'cartera_asignada' => $carteraAsignada,
                'meta_periodo' => $metaPeriodo,
                'porcentaje_meta' => $porcentajeMeta,
            ];
        }

        // Ordenar por total cobrado descendente
        usort($reportes, fn($a, $b) => $b['total_cobrado'] <=> $a['total_cobrado']);

        return view('reportes.efectividad', compact('reportes', 'cobradores', 'cobradorId', 'desde', 'hasta'));
    }
}