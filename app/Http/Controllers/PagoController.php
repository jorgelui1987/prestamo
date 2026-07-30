<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public const METODOS = [
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia',
    ];

    /** Historial de pagos */
    public function index(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $buscar = $request->query('q');

        $pagos = Pago::query()
            ->with('prestamo.cliente')
            ->when($desde, fn ($q) => $q->whereDate('fecha_pago', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha_pago', '<=', $hasta))
            ->when($buscar, function ($q) use ($buscar) {
                $q->where('codigo', 'like', "%{$buscar}%")
                    ->orWhereHas('prestamo', fn ($p) => $p->where('codigo', 'like', "%{$buscar}%"));
            })
            ->latest('fecha_pago')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $totalHoy = (float) Pago::whereDate('fecha_pago', now()->toDateString())->sum('monto');
        $totalMes = (float) Pago::whereYear('fecha_pago', now()->year)
            ->whereMonth('fecha_pago', now()->month)->sum('monto');

        return view('pagos.index', compact('pagos', 'desde', 'hasta', 'buscar', 'totalHoy', 'totalMes'));
    }

    /** Formulario para registrar pago de un prestamo */
    public function create(Prestamo $prestamo)
    {
        Cuota::actualizarVencidas();
        $prestamo->load(['cliente', 'cuotas' => fn ($q) => $q->orderBy('numero')]);

        if ($prestamo->estado === 'pagado') {
            return redirect()->route('prestamos.show', $prestamo)
                ->with('ok', 'Este préstamo ya está totalmente pagado.');
        }

        return view('pagos.create', [
            'prestamo' => $prestamo,
            'metodos' => self::METODOS,
        ]);
    }

    /** Registra el pago aplicandolo en cascada a las cuotas pendientes */
    public function store(Request $request, Prestamo $prestamo)
    {
        $data = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['required', 'date'],
            'metodo' => ['required', 'in:'.implode(',', array_keys(self::METODOS))],
            'referencia' => ['nullable', 'string', 'max:120'],
        ]);

        // Capturar ubicación si se envió desde la app móvil
        $latitud = $request->input('latitud');
        $longitud = $request->input('longitud');

        $resultado = DB::transaction(function () use ($prestamo, $data, $latitud, $longitud) {
            // Bloquear el préstamo para actualización (evitar condiciones de carrera)
            $prestamoLocked = Prestamo::where('id', $prestamo->id)->lockForUpdate()->first();
            $prestamoLocked->loadMissing('cuotas');

            $saldo = (float) $prestamoLocked->total_pagar - (float) $prestamoLocked->cuotas->sum('monto_pagado');

            if ($saldo <= 0) {
                return ['error' => 'El préstamo ya no tiene saldo pendiente.'];
            }

            $monto = min((float) $data['monto'], round($saldo, 2));
            $aviso = ((float) $data['monto'] > $saldo)
                ? ' (el monto se ajustó al saldo pendiente)'
                : '';

            $restante = $monto;

            // Bloquear las cuotas correspondientes para actualización
            $cuotas = $prestamoLocked->cuotas()
                ->whereColumn('monto_pagado', '<', 'monto')
                ->orderBy('numero')
                ->lockForUpdate()
                ->get();

            foreach ($cuotas as $cuota) {
                if ($restante <= 0) {
                    break;
                }

                $deuda = round((float) $cuota->monto - (float) $cuota->monto_pagado, 2);
                $aplica = min($restante, $deuda);

                $cuota->monto_pagado = round((float) $cuota->monto_pagado + $aplica, 2);
                if ($cuota->monto_pagado >= (float) $cuota->monto) {
                    $cuota->estado = 'pagado';
                    $cuota->fecha_pago = $data['fecha_pago'];
                } else {
                    $cuota->estado = 'parcial';
                }
                $cuota->save();

                $pagoData = [
                    'codigo' => $this->generarCodigo(),
                    'prestamo_id' => $prestamoLocked->id,
                    'cuota_id' => $cuota->id,
                    'monto' => $aplica,
                    'fecha_pago' => $data['fecha_pago'],
                    'metodo' => $data['metodo'],
                    'referencia' => $data['referencia'] ?? null,
                    'user_id' => auth()->id(),
                ];

                // Guardar ubicación si se capturó desde la app móvil
                if ($latitud && $longitud) {
                    $pagoData['latitud'] = $latitud;
                    $pagoData['longitud'] = $longitud;
                }

                Pago::create($pagoData);

                $restante = round($restante - $aplica, 2);
            }

            $prestamoLocked->recalcularEstado();

            return ['monto' => $monto, 'aviso' => $aviso];
        });

        if (isset($resultado['error'])) {
            return back()->with('ok', $resultado['error']);
        }

        // Si el usuario es un cobrador o viene de la app móvil, redirigir a la pantalla de éxito móvil
        if (auth()->user()->rol === 'cobrador' || $request->query('origen') === 'movil') {
            return redirect()->route('movil.exito', [
                'prestamo' => $prestamo->id,
                'monto' => $resultado['monto']
            ]);
        }

        return redirect()->route('prestamos.show', $prestamo)
            ->with('ok', 'Pago de S/ '.number_format($resultado['monto'], 2).' registrado correctamente'.$resultado['aviso'].'.');
    }

    /** Anula (revierte) un pago */
    public function destroy(Pago $pago)
    {
        $prestamo = $pago->prestamo;

        DB::transaction(function () use ($pago, $prestamo) {
            if ($pago->cuota_id) {
                $cuota = Cuota::find($pago->cuota_id);
                if ($cuota) {
                    $cuota->monto_pagado = max(round((float) $cuota->monto_pagado - (float) $pago->monto, 2), 0);
                    if ($cuota->monto_pagado <= 0) {
                        $cuota->estado = Carbon::parse($cuota->fecha_vencimiento)->isPast() ? 'vencido' : 'pendiente';
                        $cuota->fecha_pago = null;
                    } else {
                        $cuota->estado = 'parcial';
                    }
                    $cuota->save();
                }
            }
            $pago->delete();
            $prestamo?->recalcularEstado();
        });

        return back()->with('ok', 'Pago anulado y saldo actualizado.');
    }

    private function generarCodigo(): string
    {
        $next = (int) Pago::withoutGlobalScopes()->max('id') + 1;

        do {
            $codigo = 'PAG-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (Pago::withoutGlobalScopes()->where('codigo', $codigo)->exists());

        return $codigo;
    }
}
