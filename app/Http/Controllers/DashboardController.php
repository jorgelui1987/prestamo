<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cuota;
use App\Models\Empeno;
use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->esSuperAdmin()) {
            return redirect()->route('superadmin.index');
        }

        if (auth()->user()->rol === 'cobrador') {
            $cobradorId = auth()->id();
        } else {
            $cobradorId = $request->query('cobrador_id');
        }

        // Filtrar clientes por cobrador (creados por él o con préstamos con él)
        $totalClientes = Cliente::query()
            ->when($cobradorId, function ($q) use ($cobradorId) {
                $q->where('created_by', $cobradorId)
                  ->orWhereExists(function ($sub) use ($cobradorId) {
                      $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                          ->from('prestamos')
                          ->whereColumn('prestamos.cliente_id', 'clientes.id')
                          ->where('prestamos.cobrador_id', $cobradorId);
                  });
            })
            ->count();

        // Filtrar préstamos por cobrador
        $capitalPrestado = (float) Prestamo::whereIn('estado', ['activo', 'mora'])
            ->when($cobradorId, fn($q) => $q->where('cobrador_id', $cobradorId))
            ->sum('monto');

        $gananciaInteres = (float) Prestamo::query()
            ->when($cobradorId, fn($q) => $q->where('cobrador_id', $cobradorId))
            ->sum('interes_total');

        $totalEmpenos = Empeno::query()
            ->when($cobradorId, fn($q) => $q->whereHas('cliente', function ($c) use ($cobradorId) {
                $c->where('created_by', $cobradorId);
            }))
            ->count();

        $totalCobrado = (float) Pago::query()
            ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
            ->sum('monto');

        $totalPorCobrar = (float) Prestamo::whereIn('estado', ['activo', 'mora'])
            ->when($cobradorId, fn($q) => $q->where('cobrador_id', $cobradorId))
            ->sum('saldo');

        // Cobrado Hoy
        $cobradoHoy = (float) Pago::whereDate('fecha_pago', now()->toDateString())
            ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
            ->sum('monto');

        $cobradoHoyEfectivo = (float) Pago::whereDate('fecha_pago', now()->toDateString())
            ->where('metodo', 'efectivo')
            ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
            ->sum('monto');

        $cobradoHoyTransferencia = (float) Pago::whereDate('fecha_pago', now()->toDateString())
            ->where('metodo', 'transferencia')
            ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
            ->sum('monto');

        // Estado de la cartera (para grafico donut)
        $cuotasAlDia = Cuota::whereHas('prestamo')->where('estado', 'pendiente')
            ->when($cobradorId, fn($q) => $q->whereHas('prestamo', fn($p) => $p->where('cobrador_id', $cobradorId)))
            ->count();

        $cuotasPagadas = Cuota::whereHas('prestamo')->where('estado', 'pagado')
            ->when($cobradorId, fn($q) => $q->whereHas('prestamo', fn($p) => $p->where('cobrador_id', $cobradorId)))
            ->count();

        $cuotasVencidas = Cuota::whereHas('prestamo')->where('estado', 'vencido')
            ->when($cobradorId, fn($q) => $q->whereHas('prestamo', fn($p) => $p->where('cobrador_id', $cobradorId)))
            ->count();

        // Balance prestado vs recuperado (barra)
        $totalPrestado = (float) Prestamo::query()
            ->when($cobradorId, fn($q) => $q->where('cobrador_id', $cobradorId))
            ->sum('total_pagar');

        $totalRecuperado = $totalCobrado;

        // Prestamos recientes
        $prestamosRecientes = Prestamo::with('cliente')
            ->when($cobradorId, fn($q) => $q->where('cobrador_id', $cobradorId))
            ->latest()
            ->take(5)
            ->get();

        // Cobros de los últimos 7 días
        $cobrosSemana = [];
        $diasSemana = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->toDateString();
            $diasSemana[] = now()->subDays($i)->translatedFormat('D');
            $total = (float) Pago::whereDate('fecha_pago', $fecha)
                ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
                ->sum('monto');
            $cobrosSemana[] = $total;
        }

        // Distribución por método de pago (hoy)
        $metodosPago = [];
        foreach (['efectivo', 'transferencia'] as $metodo) {
            $total = (float) Pago::whereDate('fecha_pago', now()->toDateString())
                ->where('metodo', $metodo)
                ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
                ->sum('monto');
            if ($total > 0) {
                $metodosPago[] = ['metodo' => ucfirst($metodo), 'total' => $total];
            }
        }

        // Rendimiento de Cobradores Hoy
        if (auth()->user()->rol === 'cobrador') {
            $listaCobradores = \App\Models\User::where('id', auth()->id())->get();
        } else {
            $listaCobradores = \App\Models\User::where('rol', 'cobrador')->get();
        }
        $rendimientoCobradores = [];

        foreach ($listaCobradores as $cob) {
            // Clientes totales asignados en ruta hoy (cuotas pendientes)
            $rutaTotal = Cuota::whereIn('estado', ['pendiente', 'parcial', 'vencido'])
                ->whereHas('prestamo', fn($p) => $p->where('cobrador_id', $cob->id))
                ->count();

            // Cobros ya realizados hoy por este cobrador
            $cobrosRealizados = Pago::where('user_id', $cob->id)
                ->whereDate('fecha_pago', now()->toDateString())
                ->count();

            // Total cobrado hoy por este cobrador
            $montoCobradoHoy = (float) Pago::where('user_id', $cob->id)
                ->whereDate('fecha_pago', now()->toDateString())
                ->sum('monto');

            // Meta individual del cobrador
            $metaDiaria = (float) $cob->meta_diaria;
            $porcentajeMeta = $metaDiaria > 0 ? min(round(($montoCobradoHoy / $metaDiaria) * 100), 100) : 0;

            // Visitas sin éxito hoy
            $visitasSinExito = \App\Models\VisitaSinExito::where('user_id', $cob->id)
                ->whereDate('fecha', now()->toDateString())
                ->count();

            // Promesas de pago para hoy (de visitas anteriores)
            $promesasHoy = \App\Models\VisitaSinExito::where('user_id', $cob->id)
                ->whereDate('fecha_promesa', now()->toDateString())
                ->where('promesa_cumplida', false)
                ->count();

            $rendimientoCobradores[] = [
                'nombre' => $cob->name,
                'ruta_total' => $rutaTotal + $cobrosRealizados,
                'cobros_realizados' => $cobrosRealizados,
                'monto_cobrado' => $montoCobradoHoy,
                'porcentaje' => ($rutaTotal + $cobrosRealizados) > 0 ? round(($cobrosRealizados / ($rutaTotal + $cobrosRealizados)) * 100) : 0,
                'meta_diaria' => $metaDiaria,
                'porcentaje_meta' => $porcentajeMeta,
                'visitas_sin_exito' => $visitasSinExito,
                'promesas_hoy' => $promesasHoy,
            ];
        }

        return view('dashboard.index', compact(
            'totalClientes', 'capitalPrestado', 'gananciaInteres', 'totalEmpenos',
            'totalCobrado', 'totalPorCobrar', 'cuotasAlDia', 'cuotasPagadas',
            'cuotasVencidas', 'totalPrestado', 'totalRecuperado', 'prestamosRecientes',
            'cobradoHoy', 'cobradoHoyEfectivo', 'cobradoHoyTransferencia',
            'rendimientoCobradores', 'listaCobradores', 'cobradorId',
            'cobrosSemana', 'diasSemana', 'metodosPago'
        ));
    }
}
