<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MovilCobradorController extends Controller
{
    public function index(Request $request)
    {
        Cuota::actualizarVencidas();
        $user = auth()->user();
        $buscar = $request->query('q');

        // Obtener las cuotas pendientes asignadas a este cobrador, ordenadas por orden de ruta
        // EXCLUYENDO los préstamos que ya registraron un pago el día de hoy O una visita sin éxito hoy
        $cuotas = Cuota::query()
            ->select('cuotas.*')
            ->join('prestamos', 'cuotas.prestamo_id', '=', 'prestamos.id')
            ->with('prestamo.cliente')
            ->whereIn('cuotas.estado', ['pendiente', 'parcial', 'vencido'])
            ->where('prestamos.cobrador_id', $user->id)
            ->whereIn('cuotas.id', function ($subQuery) {
                $subQuery->select(DB::raw('MIN(c2.id)'))
                    ->from('cuotas as c2')
                    ->whereIn('c2.estado', ['pendiente', 'parcial', 'vencido'])
                    ->groupBy('c2.prestamo_id');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('pagos')
                    ->whereColumn('pagos.prestamo_id', 'prestamos.id')
                    ->whereDate('pagos.fecha_pago', now()->toDateString());
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('visitas_sin_exito')
                    ->whereColumn('visitas_sin_exito.prestamo_id', 'prestamos.id')
                    ->whereDate('visitas_sin_exito.fecha', now()->toDateString());
            })
            ->when($buscar, function ($q) use ($buscar) {
                $q->whereHas('prestamo.cliente', function ($c) use ($buscar) {
                    $c->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('apellidos', 'like', "%{$buscar}%")
                        ->orWhere('documento', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('prestamos.orden_ruta')
            ->orderBy('cuotas.fecha_vencimiento')
            ->get();

        // Resumen rápido calculado desde la colección ya cargada (evita 3 queries extra)
        $resumen = [
            'vencidas' => $cuotas->where('estado', 'vencido')->count(),
            'hoy' => $cuotas->whereIn('estado', ['pendiente', 'parcial'])
                ->filter(fn($c) => $c->fecha_vencimiento === now()->toDateString())
                ->count(),
            'total_clientes' => $cuotas->count(),
        ];

        return view('movil.index', compact('cuotas', 'resumen', 'buscar'));
    }

    public function cobroExpress(Prestamo $prestamo)
    {
        // Validar que el préstamo pertenezca al cobrador autenticado
        abort_unless($prestamo->cobrador_id === auth()->id(), 403, 'Este préstamo no está asignado a tu ruta de cobranza.');

        Cuota::actualizarVencidas();
        $prestamo->load(['cliente', 'cuotas' => fn ($q) => $q->orderBy('numero')]);

        if ($prestamo->estado === 'pagado') {
            return redirect()->route('movil.index')->with('ok', 'Este préstamo ya está totalmente pagado.');
        }

        $pendientes = $prestamo->cuotas->filter(fn ($c) => $c->estado !== 'pagado');
        $saldo = round((float) $prestamo->total_pagar - (float) $prestamo->cuotas->sum('monto_pagado'), 2);
        $proxima = $pendientes->sortBy('numero')->first();
        $proximaPend = $proxima ? round((float) $proxima->monto - (float) $proxima->mora - (float) $proxima->monto_pagado, 2) : 0;
        $mora = $proxima ? (float) $proxima->mora : 0;
        $totalSugerido = $proximaPend + $mora;

        return view('movil.cobrar', compact('prestamo', 'saldo', 'proximaPend', 'mora', 'totalSugerido'));
    }

    public function registrarGasto(Request $request)
    {
        $data = $request->validate([
            'concepto' => ['required', 'string', 'max:150'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo' => ['required', 'in:efectivo,transferencia,yape,plin'],
        ]);

        $data['fecha'] = now()->toDateString();
        $data['tipo'] = 'egreso';
        $data['categoria'] = 'gasto_operativo';
        $data['codigo'] = 'MOV-' . str_pad((string)((\App\Models\MovimientoCaja::withoutGlobalScopes()->max('id') ?? 0) + 1), 6, '0', STR_PAD_LEFT);
        $data['user_id'] = auth()->id();

        \App\Models\MovimientoCaja::create($data);

        return redirect()->route('movil.index')->with('ok', 'Gasto registrado correctamente.');
    }

    public function actualizarRuta(Request $request)
    {
        $data = $request->validate([
            'prestamo_id' => ['required', 'exists:prestamos,id'],
            'direccion' => ['required', 'in:subir,bajar'],
        ]);

        $prestamo = Prestamo::findOrFail($data['prestamo_id']);
        $currentOrder = $prestamo->orden_ruta;

        if ($data['direccion'] === 'subir') {
            // Buscar el préstamo inmediatamente anterior en la ruta
            $anterior = Prestamo::where('cobrador_id', auth()->id())
                ->where('orden_ruta', '<', $currentOrder)
                ->orderBy('orden_ruta', 'desc')
                ->first();

            if ($anterior) {
                $prestamo->update(['orden_ruta' => $anterior->orden_ruta]);
                $anterior->update(['orden_ruta' => $currentOrder]);
            }
        } else {
            // Buscar el préstamo inmediatamente posterior en la ruta
            $siguiente = Prestamo::where('cobrador_id', auth()->id())
                ->where('orden_ruta', '>', $currentOrder)
                ->orderBy('orden_ruta', 'asc')
                ->first();

            if ($siguiente) {
                $prestamo->update(['orden_ruta' => $siguiente->orden_ruta]);
                $siguiente->update(['orden_ruta' => $currentOrder]);
            }
        }

        return redirect()->route('movil.index')->with('ok', 'Orden de ruta actualizado.');
    }

    public function historialPagos(Request $request)
    {
        $user = auth()->user();
        
        // Obtener los pagos registrados por este cobrador el día de hoy
        $pagos = Pago::with('prestamo.cliente')
            ->where('user_id', $user->id)
            ->whereDate('fecha_pago', now()->toDateString())
            ->latest()
            ->get();

        // Obtener las visitas sin éxito registradas por este cobrador el día de hoy
        $visitasFallidas = \App\Models\VisitaSinExito::with('prestamo.cliente')
            ->where('user_id', $user->id)
            ->whereDate('fecha', now()->toDateString())
            ->latest()
            ->get();

        // Calcular el resumen de caja chica de hoy
        $totalEfectivoCobrado = (float) Pago::where('user_id', $user->id)
            ->where('metodo', 'efectivo')
            ->whereDate('fecha_pago', now()->toDateString())
            ->sum('monto');

        $totalGastosEfectivo = (float) \App\Models\MovimientoCaja::where('user_id', $user->id)
            ->where('tipo', 'egreso')
            ->where('metodo', 'efectivo')
            ->whereDate('fecha', now()->toDateString())
            ->sum('monto');

        $efectivoNeto = $totalEfectivoCobrado - $totalGastosEfectivo;

        return view('movil.historial', compact('pagos', 'visitasFallidas', 'totalEfectivoCobrado', 'totalGastosEfectivo', 'efectivoNeto'));
    }

    public function detalle(Prestamo $prestamo)
    {
        // Validar que el préstamo pertenezca al cobrador autenticado
        abort_unless($prestamo->cobrador_id === auth()->id(), 403, 'Este préstamo no está asignado a tu ruta de cobranza.');

        $prestamo->load(['cliente', 'cuotas' => fn ($q) => $q->orderBy('numero')]);
        $saldo = round((float) $prestamo->total_pagar - (float) $prestamo->cuotas->sum('monto_pagado'), 2);

        return view('movil.detalle', compact('prestamo', 'saldo'));
    }

    public function exito(Request $request)
    {
        $prestamo = Prestamo::with('cliente')->findOrFail($request->query('prestamo'));
        $monto = $request->query('monto');
        $saldo = round((float) $prestamo->total_pagar - (float) $prestamo->cuotas->sum('monto_pagado'), 2);

        return view('movil.exito', compact('prestamo', 'monto', 'saldo'));
    }

    public function registrarNoPago(Request $request)
    {
        $data = $request->validate([
            'prestamo_id' => ['required', 'exists:prestamos,id'],
            'motivo' => ['required', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string'],
            'fecha_promesa' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        \App\Models\VisitaSinExito::create([
            'prestamo_id' => $data['prestamo_id'],
            'user_id' => auth()->id(),
            'fecha' => now()->toDateString(),
            'motivo' => $data['motivo'],
            'observaciones' => $data['observaciones'] ?? null,
            'fecha_promesa' => $data['fecha_promesa'] ?? null,
        ]);

        $mensaje = 'Visita sin éxito registrada. El cliente se ocultará por hoy.';
        if (!empty($data['fecha_promesa'])) {
            $mensaje .= ' Promesa de pago registrada para el ' . \Illuminate\Support\Carbon::parse($data['fecha_promesa'])->format('d/m/Y') . '.';
        }

        return redirect()->route('movil.index')->with('ok', $mensaje);
    }

    public function anularNoPago(\App\Models\VisitaSinExito $visita)
    {
        // Asegurar que solo el cobrador dueño de la visita pueda anularla
        abort_unless($visita->user_id === auth()->id(), 403);
        
        $visita->delete();

        return redirect()->route('movil.historial')->with('ok', 'Visita sin éxito anulada. El cliente ha vuelto a tu lista de ruta.');
    }

    /**
     * Endpoint para sincronización batch desde el celular.
     * Recibe un array de operaciones pendientes y las procesa.
     */
    public function syncBatch(Request $request): JsonResponse
    {
        $user = auth()->user();
        $operaciones = $request->input('operaciones', []);
        $sincronizados = 0;
        $fallidos = 0;
        $errores = [];

        foreach ($operaciones as $op) {
            try {
                $tipo = $op['tipo'] ?? '';
                $datos = $op['datos'] ?? [];

                if (is_string($datos)) {
                    $datos = json_decode($datos, true) ?? [];
                }

                switch ($tipo) {
                    case 'cobro':
                        $prestamo = Prestamo::findOrFail($datos['prestamo_id']);
                        // Validar que sea su cobrador
                        if ($prestamo->cobrador_id !== $user->id) {
                            throw new \Exception('Préstamo no asignado a este cobrador');
                        }
                        $request->merge([
                            'monto' => $datos['monto'],
                            'metodo' => $datos['metodo'],
                            'fecha_pago' => $datos['fecha_pago'] ?? now()->toDateString(),
                            'referencia' => $datos['referencia'] ?? null,
                        ]);
                        app(PagoController::class)->store($request, $prestamo);
                        $sincronizados++;
                        break;

                    case 'gasto':
                        $this->registrarGasto(new Request([
                            'concepto' => $datos['concepto'],
                            'monto' => $datos['monto'],
                            'metodo' => $datos['metodo'],
                        ]));
                        $sincronizados++;
                        break;

                    case 'visita':
                        $this->registrarNoPago(new Request([
                            'prestamo_id' => $datos['prestamo_id'],
                            'motivo' => $datos['motivo'],
                            'observaciones' => $datos['observaciones'] ?? null,
                        ]));
                        $sincronizados++;
                        break;

                    default:
                        $fallidos++;
                        $errores[] = "Tipo desconocido: {$tipo}";
                }
            } catch (\Exception $e) {
                $fallidos++;
                $errores[] = $e->getMessage();
            }
        }

        return response()->json([
            'success' => $fallidos === 0,
            'sincronizados' => $sincronizados,
            'fallidos' => $fallidos,
            'errores' => $errores,
            'mensaje' => "{$sincronizados} operación(es) sincronizada(s)" . ($fallidos > 0 ? ", {$fallidos} fallida(s)." : " correctamente."),
        ]);
    }
}
