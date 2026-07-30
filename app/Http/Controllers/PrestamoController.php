<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cuota;
use App\Models\Prestamo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PrestamoController extends Controller
{
    /** Frecuencias soportadas => label */
    public const FRECUENCIAS = [
        'diario' => 'Diario',
        'semanal' => 'Semanal',
        'quincenal' => 'Quincenal',
        'mensual' => 'Mensual',
    ];

    public function index(Request $request)
    {
        $estado = $request->query('estado');
        $buscar = $request->query('q');
        $cobradorId = $request->query('cobrador_id');

        $prestamos = Prestamo::query()
            ->with(['cliente', 'cobrador'])
            ->when($cobradorId, fn ($q) => $q->where('cobrador_id', $cobradorId))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($buscar, function ($q) use ($buscar) {
                $q->where(function ($sub) use ($buscar) {
                    $sub->where('codigo', 'like', "%{$buscar}%")
                        ->orWhereHas('cliente', function ($c) use ($buscar) {
                            $c->where('nombres', 'like', "%{$buscar}%")
                                ->orWhere('apellidos', 'like', "%{$buscar}%")
                                ->orWhere('documento', 'like', "%{$buscar}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $resumen = [
            'total' => Prestamo::query()
                ->when($cobradorId, fn ($q) => $q->where('cobrador_id', $cobradorId))
                ->count(),
            'activos' => Prestamo::where('estado', 'activo')
                ->when($cobradorId, fn ($q) => $q->where('cobrador_id', $cobradorId))
                ->count(),
            'mora' => Prestamo::where('estado', 'mora')
                ->when($cobradorId, fn ($q) => $q->where('cobrador_id', $cobradorId))
                ->count(),
            'capital' => (float) Prestamo::whereIn('estado', ['activo', 'mora'])
                ->when($cobradorId, fn ($q) => $q->where('cobrador_id', $cobradorId))
                ->sum('saldo'),
        ];

        $cobradores = User::where('rol', 'cobrador')->orderBy('name')->get();

        return view('prestamos.index', compact('prestamos', 'estado', 'buscar', 'resumen', 'cobradores', 'cobradorId'));
    }

    public function create()
    {
        $user = auth()->user();

        // TODOS los clientes de la empresa (sin filtrar por cobrador)
        // Así cualquier cobrador puede buscar y seleccionar un cliente existente
        // evitando duplicados cuando otro cobrador ya lo registró
        $clientes = Cliente::orderBy('nombres')->get();

        return view('prestamos.form', [
            'prestamo' => new Prestamo(['frecuencia' => 'mensual', 'fecha_inicio' => now()->toDateString(), 'orden_ruta' => 0]),
            'clientes' => $clientes,
            'cobradores' => User::whereIn('rol', ['cobrador', 'operador', 'admin', 'gerente'])->orderBy('name')->get(),
            'frecuencias' => self::FRECUENCIAS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        $calc = $this->calcular(
            (float) $data['monto'],
            (float) $data['tasa_interes'],
            (int) $data['numero_cuotas']
        );

        $prestamo = DB::transaction(function () use ($data, $calc, $request) {
            $cobradorId = auth()->user()->rol === 'cobrador' ? auth()->id() : ($data['cobrador_id'] ?? null);
            
            // Calcular automáticamente el siguiente orden de ruta si es cobrador o si no se especificó
            $ordenRuta = $data['orden_ruta'] ?? 0;
            if ($ordenRuta == 0 && $cobradorId) {
                $ordenRuta = (int) Prestamo::where('cobrador_id', $cobradorId)->max('orden_ruta') + 1;
            }

            $prestamo = Prestamo::create([
                'codigo' => $this->generarCodigo(),
                'cliente_id' => $data['cliente_id'],
                'monto' => $data['monto'],
                'tasa_interes' => $data['tasa_interes'],
                'numero_cuotas' => $data['numero_cuotas'],
                'frecuencia' => $data['frecuencia'],
                'monto_cuota' => $calc['monto_cuota'],
                'total_pagar' => $calc['total_pagar'],
                'interes_total' => $calc['interes_total'],
                'saldo' => $calc['total_pagar'],
                'fecha_inicio' => $data['fecha_inicio'],
                'estado' => 'activo',
                'observaciones' => $data['observaciones'] ?? null,
                'user_id' => auth()->id(),
                'cobrador_id' => $cobradorId,
                'orden_ruta' => $ordenRuta,
                'numero_boleta' => $data['numero_boleta'] ?? null,
                'costo_boleta' => $data['costo_boleta'] ?? 0,
            ]);

            // Guardar la firma digital si se proporcionó
            if ($request->filled('firma_base64')) {
                $image = str_replace('data:image/png;base64,', '', $request->firma_base64);
                $image = str_replace(' ', '+', $image);
                $imageName = 'firma_' . $prestamo->id . '_' . time() . '.png';
                \Illuminate\Support\Facades\Storage::disk('public')->put('firmas/' . $imageName, base64_decode($image));
                $prestamo->update(['firma_ruta' => 'firmas/' . $imageName]);
            }

            // Registrar automáticamente el egreso del dinero neto entregado en la caja chica del cobrador
            $costoBoleta = (float) ($data['costo_boleta'] ?? 0);
            $montoNeto = max((float) $data['monto'] - $costoBoleta, 0);

            if ($montoNeto > 0 && $cobradorId) {
                \App\Models\MovimientoCaja::create([
                    'codigo' => 'MOV-' . str_pad((string)((\App\Models\MovimientoCaja::withoutGlobalScopes()->max('id') ?? 0) + 1), 6, '0', STR_PAD_LEFT),
                    'fecha' => $data['fecha_inicio'],
                    'tipo' => 'egreso',
                    'categoria' => 'desembolso',
                    'concepto' => 'Desembolso de préstamo ' . $prestamo->codigo . ' (Neto entregado al cliente).',
                    'monto' => $montoNeto,
                    'metodo' => 'efectivo',
                    'user_id' => $cobradorId,
                ]);
            }

            $this->generarCronograma($prestamo, $calc);

            return $prestamo;
        });

        // Si el usuario es un cobrador o viene de la app móvil, redirigir de vuelta a la app móvil
        if (auth()->user()->rol === 'cobrador' || $request->query('origen') === 'movil') {
            return redirect()->route('movil.index')
                ->with('ok', "Préstamo {$prestamo->codigo} registrado correctamente.");
        }

        return redirect()->route('prestamos.show', $prestamo)
            ->with('ok', "Prestamo {$prestamo->codigo} registrado. Se generaron {$prestamo->numero_cuotas} cuotas.");
    }

    public function show(Prestamo $prestamo)
    {
        $prestamo->load(['cliente', 'cuotas' => fn ($q) => $q->orderBy('numero')]);

        $pagado = (float) $prestamo->cuotas->sum('monto_pagado');
        $progreso = $prestamo->total_pagar > 0
            ? round($pagado / $prestamo->total_pagar * 100)
            : 0;

        return view('prestamos.show', compact('prestamo', 'pagado', 'progreso'));
    }

    public function edit(Prestamo $prestamo)
    {
        if ($prestamo->pagos()->exists()) {
            return redirect()->route('prestamos.show', $prestamo)
                ->with('ok', 'No se puede editar un prestamo que ya tiene pagos registrados.');
        }

        $user = auth()->user();

        // Si es cobrador, solo puede ver sus propios clientes (creados por él o con préstamos con él)
        if ($user->rol === 'cobrador') {
            $clientes = Cliente::where('created_by', $user->id)
                ->orWhereExists(function ($q) use ($user) {
                    $q->select(DB::raw(1))
                        ->from('prestamos')
                        ->whereColumn('prestamos.cliente_id', 'clientes.id')
                        ->where('prestamos.cobrador_id', $user->id);
                })
                ->orderBy('nombres')
                ->get();
        } else {
            // El administrador/gerente ve todos los clientes de la empresa
            $clientes = Cliente::orderBy('nombres')->get();
        }

        return view('prestamos.form', [
            'prestamo' => $prestamo,
            'clientes' => $clientes,
            'cobradores' => User::whereIn('rol', ['cobrador', 'operador', 'admin', 'gerente'])->orderBy('name')->get(),
            'frecuencias' => self::FRECUENCIAS,
        ]);
    }

    public function update(Request $request, Prestamo $prestamo)
    {
        if ($prestamo->pagos()->exists()) {
            return redirect()->route('prestamos.show', $prestamo)
                ->with('ok', 'No se puede editar un prestamo con pagos registrados.');
        }

        $data = $this->validar($request);
        $calc = $this->calcular((float) $data['monto'], (float) $data['tasa_interes'], (int) $data['numero_cuotas']);

        DB::transaction(function () use ($prestamo, $data, $calc) {
            $prestamo->update([
                'cliente_id' => $data['cliente_id'],
                'monto' => $data['monto'],
                'tasa_interes' => $data['tasa_interes'],
                'numero_cuotas' => $data['numero_cuotas'],
                'frecuencia' => $data['frecuencia'],
                'monto_cuota' => $calc['monto_cuota'],
                'total_pagar' => $calc['total_pagar'],
                'interes_total' => $calc['interes_total'],
                'saldo' => $calc['total_pagar'],
                'fecha_inicio' => $data['fecha_inicio'],
                'observaciones' => $data['observaciones'] ?? null,
                'cobrador_id' => $data['cobrador_id'] ?? null,
                'orden_ruta' => $data['orden_ruta'] ?? 0,
            ]);

            $prestamo->cuotas()->delete();
            $this->generarCronograma($prestamo, $calc);
        });

        return redirect()->route('prestamos.show', $prestamo)
            ->with('ok', 'Prestamo actualizado y cronograma regenerado.');
    }

    public function destroy(Prestamo $prestamo)
    {
        abort_if(auth()->user()->rol === 'cobrador', 403, 'No tienes permisos para eliminar préstamos.');

        $prestamo->delete();

        return redirect()->route('prestamos.index')->with('ok', 'Prestamo eliminado.');
    }

    /* =================== LOGICA DE NEGOCIO =================== */

    private function calcular(float $monto, float $tasa, int $cuotas): array
    {
        $interesTotal = round($monto * $tasa / 100, 2);
        $totalPagar = round($monto + $interesTotal, 2);
        $montoCuota = round($totalPagar / max($cuotas, 1), 2);

        return [
            'interes_total' => $interesTotal,
            'total_pagar' => $totalPagar,
            'monto_cuota' => $montoCuota,
        ];
    }

    private function generarCronograma(Prestamo $prestamo, array $calc): void
    {
        $n = $prestamo->numero_cuotas;
        $capitalPorCuota = round($prestamo->monto / $n, 2);
        $interesPorCuota = round($calc['interes_total'] / $n, 2);
        $fecha = Carbon::parse($prestamo->fecha_inicio);

        $acumCapital = 0;
        $acumInteres = 0;
        $acumMonto = 0;

        for ($i = 1; $i <= $n; $i++) {
            $fecha = $this->siguienteFecha($fecha, $prestamo->frecuencia);

            if ($i < $n) {
                $capital = $capitalPorCuota;
                $interes = $interesPorCuota;
                $monto = $calc['monto_cuota'];
            } else {
                $capital = round($prestamo->monto - $acumCapital, 2);
                $interes = round($calc['interes_total'] - $acumInteres, 2);
                $monto = round($calc['total_pagar'] - $acumMonto, 2);
            }

            $acumCapital += $capital;
            $acumInteres += $interes;
            $acumMonto += $monto;

            Cuota::create([
                'prestamo_id' => $prestamo->id,
                'numero' => $i,
                'fecha_vencimiento' => $fecha->copy(),
                'monto' => $monto,
                'capital' => $capital,
                'interes' => $interes,
                'mora' => 0,
                'monto_pagado' => 0,
                'estado' => 'pendiente',
            ]);
        }
    }

    private function siguienteFecha(Carbon $fecha, string $frecuencia): Carbon
    {
        return match ($frecuencia) {
            'diario' => $fecha->copy()->addDay(),
            'semanal' => $fecha->copy()->addWeek(),
            'quincenal' => $fecha->copy()->addDays(15),
            default => $fecha->copy()->addMonth(),
        };
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'monto' => ['required', 'numeric', 'min:1'],
            'tasa_interes' => ['required', 'numeric', 'min:0', 'max:100'],
            'numero_cuotas' => ['required', 'integer', 'min:1', 'max:360'],
            'frecuencia' => ['required', 'in:diario,semanal,quincenal,mensual'],
            'fecha_inicio' => ['required', 'date'],
            'observaciones' => ['nullable', 'string'],
            'cobrador_id' => ['nullable', 'exists:users,id'],
            'orden_ruta' => ['nullable', 'integer', 'min:0'],
            'numero_boleta' => ['nullable', 'string', 'max:50'],
            'costo_boleta' => ['nullable', 'numeric', 'min:0'],
            'firma_base64' => ['nullable', 'string'],
        ], [], [
            'cliente_id' => 'cliente',
            'numero_cuotas' => 'numero de cuotas',
            'tasa_interes' => 'tasa de interes',
            'cobrador_id' => 'cobrador',
            'orden_ruta' => 'orden de ruta',
            'numero_boleta' => 'número de boleta',
            'costo_boleta' => 'costo de boleta',
        ]);
    }

    /**
     * Buscador global de préstamos con diseño moderno y filtros avanzados.
     */
    public function buscarGlobal(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $estado = $request->query('estado', '');
        $cobradorId = $request->query('cobrador_id', '');
        $fechaDesde = $request->query('fecha_desde', '');
        $fechaHasta = $request->query('fecha_hasta', '');
        $tipoBusqueda = $request->query('tipo', 'todo');

        $prestamos = Prestamo::with(['cliente', 'cobrador', 'cuotas' => fn ($q) => $q->orderBy('numero')])
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($cobradorId, fn ($q) => $q->where('cobrador_id', $cobradorId))
            ->when($fechaDesde, fn ($q) => $q->whereDate('fecha_inicio', '>=', $fechaDesde))
            ->when($fechaHasta, fn ($q) => $q->whereDate('fecha_inicio', '<=', $fechaHasta))
            ->when(mb_strlen($q) >= 1, function ($query) use ($q, $tipoBusqueda) {
                $query->where(function ($sub) use ($q, $tipoBusqueda) {
                    if ($tipoBusqueda === 'codigo' || $tipoBusqueda === 'todo') {
                        $sub->orWhere('codigo', 'like', "%{$q}%");
                    }
                    if ($tipoBusqueda === 'cliente' || $tipoBusqueda === 'todo') {
                        $sub->orWhereHas('cliente', function ($c) use ($q) {
                            $c->where('nombres', 'like', "%{$q}%")
                              ->orWhere('apellidos', 'like', "%{$q}%")
                              ->orWhere('documento', 'like', "%{$q}%")
                              ->orWhere('telefono', 'like', "%{$q}%");
                        });
                    }
                    if ($tipoBusqueda === 'monto' || $tipoBusqueda === 'todo') {
                        $monto = is_numeric($q) ? (float) $q : 0;
                        if ($monto > 0) {
                            $sub->orWhere('monto', $monto)
                                ->orWhere('saldo', $monto);
                        }
                    }
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // Estadísticas para el resumen
        $totalPrestamos = Prestamo::count();
        $totalActivos = Prestamo::where('estado', 'activo')->count();
        $totalMora = Prestamo::where('estado', 'mora')->count();
        $totalPagados = Prestamo::where('estado', 'pagado')->count();
        $capitalPendiente = (float) Prestamo::whereIn('estado', ['activo', 'mora'])->sum('saldo');

        $cobradores = User::where('rol', 'cobrador')->orderBy('name')->get();

        $totalResultados = $prestamos->total();

        return view('prestamos.buscar-global', compact(
            'prestamos', 'q', 'estado', 'cobradorId',
            'fechaDesde', 'fechaHasta', 'tipoBusqueda',
            'totalPrestamos', 'totalActivos', 'totalMora', 'totalPagados',
            'capitalPendiente', 'cobradores', 'totalResultados'
        ));
    }

    private function generarCodigo(): string
    {
        $next = (int) Prestamo::withoutGlobalScopes()->max('id') + 1;

        do {
            $codigo = 'PRE-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (Prestamo::withoutGlobalScopes()->where('codigo', $codigo)->exists());

        return $codigo;
    }
}
