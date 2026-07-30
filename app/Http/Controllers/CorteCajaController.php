<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\Pago;
use Illuminate\Http\Request;

class CorteCajaController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->query('fecha', now()->toDateString());

        $resumen = $this->calcular($fecha);

        // Detalle por metodo de los cobros del dia
        $cobrosPorMetodo = Pago::whereDate('fecha_pago', $fecha)
            ->selectRaw('metodo, SUM(monto) as total')
            ->groupBy('metodo')
            ->pluck('total', 'metodo')
            ->toArray();

        $cortesPrevios = CorteCaja::with('user')->latest('fecha')->latest('id')->take(10)->get();

        return view('corte.index', compact('fecha', 'resumen', 'cobrosPorMetodo', 'cortesPrevios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'monto_contado' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $resumen = $this->calcular($data['fecha']);
        $diferencia = round((float) $data['monto_contado'] - $resumen['saldo'], 2);

        CorteCaja::create([
            'fecha' => $data['fecha'],
            'user_id' => auth()->id(),
            'total_cobros' => $resumen['cobros'],
            'total_ingresos' => $resumen['ingresos'],
            'total_egresos' => $resumen['egresos'],
            'saldo_calculado' => $resumen['saldo'],
            'monto_contado' => $data['monto_contado'],
            'diferencia' => $diferencia,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        $msg = $diferencia == 0.0
            ? 'Corte guardado: caja cuadrada.'
            : 'Corte guardado. Diferencia: S/ '.number_format($diferencia, 2).($diferencia > 0 ? ' (sobrante)' : ' (faltante)').'.';

        return redirect()->route('corte.index', ['fecha' => $data['fecha']])->with('ok', $msg);
    }

    private function calcular(string $fecha): array
    {
        $cobros = (float) Pago::whereDate('fecha_pago', $fecha)->sum('monto');
        $ingresos = (float) MovimientoCaja::whereDate('fecha', $fecha)->where('tipo', 'ingreso')->sum('monto');
        $egresos = (float) MovimientoCaja::whereDate('fecha', $fecha)->where('tipo', 'egreso')->sum('monto');

        return [
            'cobros' => $cobros,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'saldo' => round($cobros + $ingresos - $egresos, 2),
        ];
    }
}
