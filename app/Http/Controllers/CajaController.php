<?php

namespace App\Http\Controllers;

use App\Models\MovimientoCaja;
use App\Models\Pago;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public const CATEGORIAS = [
        'ingreso' => [
            'aporte_capital' => 'Ingreso de dinero a la oficina (Fondo/Sencillo)',
            'cobro_externo' => 'Cobro externo / Pago recibido en oficina',
            'otro_ingreso' => 'Otro ingreso de dinero',
        ],
        'egreso' => [
            'entrega_caja_cobrador' => 'Entregar dinero al cobrador (Para dar cambio)',
            'desembolso' => 'Desembolso de préstamo (Dinero entregado al cliente)',
            'gasto_operativo' => 'Gasto del día (Gasolina, almuerzo, etc.)',
            'pago_proveedor' => 'Pago a proveedor o servicios',
            'retiro' => 'Retirar dinero de la caja (Ganancia/Retiro)',
            'otro_egreso' => 'Otro egreso de dinero',
        ],
    ];

    public const METODOS = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia'];

    public function index(Request $request)
    {
        $fecha = $request->query('fecha', now()->toDateString());
        $cobradorId = $request->query('cobrador_id');

        // Filtrar movimientos de caja por cobrador si se especifica
        $movimientos = MovimientoCaja::with('user')
            ->whereDate('fecha', $fecha)
            ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
            ->latest('id')
            ->get();

        // Filtrar cobros por cobrador si se especifica
        $cobros = (float) Pago::whereDate('fecha_pago', $fecha)
            ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
            ->sum('monto');

        $ingresos = (float) $movimientos->where('tipo', 'ingreso')->sum('monto');
        $egresos = (float) $movimientos->where('tipo', 'egreso')->sum('monto');
        $saldo = $cobros + $ingresos - $egresos;

        // Desglosar para la calculadora amigable
        $baseInicial = (float) $movimientos->where('categoria', 'entrega_caja_cobrador')->sum('monto');
        $prestamosNuevos = (float) $movimientos->where('categoria', 'desembolso')->sum('monto');
        $gastosDia = (float) $movimientos->whereIn('categoria', ['gasto_operativo', 'pago_proveedor', 'retiro', 'otro_egreso'])->sum('monto');
        $otrosIngresos = (float) $movimientos->whereIn('categoria', ['aporte_capital', 'cobro_externo', 'otro_ingreso'])->sum('monto');

        $cobradores = \App\Models\User::where('rol', 'cobrador')->orderBy('name')->get();

        return view('caja.index', [
            'movimientos' => $movimientos,
            'fecha' => $fecha,
            'cobros' => $cobros,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'saldo' => $saldo,
            'categorias' => self::CATEGORIAS,
            'metodos' => self::METODOS,
            'cobradores' => $cobradores,
            'cobradorId' => $cobradorId,
            'baseInicial' => $baseInicial,
            'prestamosNuevos' => $prestamosNuevos,
            'gastosDia' => $gastosDia,
            'otrosIngresos' => $otrosIngresos,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'tipo' => ['required', 'in:ingreso,egreso'],
            'categoria' => ['required', 'string', 'max:40'],
            'concepto' => ['required', 'string', 'max:150'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo' => ['required', 'in:'.implode(',', array_keys(self::METODOS))],
            'cobrador_id' => ['nullable', 'exists:users,id'], // Permitir asociar un cobrador al movimiento
        ]);

        // Validar saldo disponible si es un egreso
        if ($data['tipo'] === 'egreso') {
            $cobros = (float) Pago::whereDate('fecha_pago', $data['fecha'])->sum('monto');
            $ingresos = (float) MovimientoCaja::whereDate('fecha', $data['fecha'])->where('tipo', 'ingreso')->sum('monto');
            $egresos = (float) MovimientoCaja::whereDate('fecha', $data['fecha'])->where('tipo', 'egreso')->sum('monto');
            $saldoActual = $cobros + $ingresos - $egresos;

            if ($saldoActual < (float) $data['monto']) {
                return back()->withErrors([
                    'monto' => 'El monto del egreso supera el saldo disponible en caja para esta fecha (Saldo actual: S/ ' . number_format($saldoActual, 2) . ').'
                ])->withInput();
            }
        }

        $data['codigo'] = $this->generarCodigo();
        // Si se especificó un cobrador (por ejemplo, para entregarle caja), el movimiento se asocia a su ID
        $data['user_id'] = $data['cobrador_id'] ?? auth()->id();
        unset($data['cobrador_id']);
        
        MovimientoCaja::create($data);

        return redirect()->route('caja.index', [
            'fecha' => $data['fecha'],
            'cobrador_id' => $request->query('cobrador_id') // Mantener el cobrador seleccionado en la redirección
        ])->with('ok', 'Movimiento registrado.');
    }

    public function destroy(MovimientoCaja $movimiento, Request $request)
    {
        $fecha = $movimiento->fecha->toDateString();
        $movimiento->delete();

        return redirect()->route('caja.index', [
            'fecha' => $fecha,
            'cobrador_id' => $request->query('cobrador_id') // Mantener el cobrador seleccionado al eliminar
        ])->with('ok', 'Movimiento eliminado.');
    }

    private function generarCodigo(): string
    {
        $next = (int) MovimientoCaja::withoutGlobalScopes()->max('id') + 1;

        do {
            $codigo = 'MOV-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (MovimientoCaja::withoutGlobalScopes()->where('codigo', $codigo)->exists());

        return $codigo;
    }

    public function guardarArqueo(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'cobrador_id' => ['required', 'exists:users,id'],
            'monto_contado' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string'],
        ]);

        // Calcular el saldo teórico del cobrador para esa fecha
        $movimientos = MovimientoCaja::whereDate('fecha', $data['fecha'])
            ->where('user_id', $data['cobrador_id'])
            ->get();

        $cobros = (float) Pago::whereDate('fecha_pago', $data['fecha'])
            ->where('user_id', $data['cobrador_id'])
            ->sum('monto');

        $ingresos = (float) $movimientos->where('tipo', 'ingreso')->sum('monto');
        $egresos = (float) $movimientos->where('tipo', 'egreso')->sum('monto');
        $saldoTeorico = round($cobros + $ingresos - $egresos, 2);

        $diferencia = round((float) $data['monto_contado'] - $saldoTeorico, 2);

        // Guardar el corte de caja oficial
        \App\Models\CorteCaja::create([
            'fecha' => $data['fecha'],
            'user_id' => $data['cobrador_id'], // El corte pertenece al cobrador
            'total_cobros' => $cobros,
            'total_ingresos' => $ingresos,
            'total_egresos' => $egresos,
            'saldo_calculated' => $saldoTeorico, // Ajustar según el nombre de columna real
            'saldo_calculado' => $saldoTeorico,
            'monto_contado' => $data['monto_contado'],
            'diferencia' => $diferencia,
            'observaciones' => $data['observaciones'] ?? 'Cierre de caja diario.',
        ]);

        // Crear un movimiento de egreso para "vaciar" la caja del cobrador y transferirla a la caja central
        if ($data['monto_contado'] > 0) {
            MovimientoCaja::create([
                'codigo' => $this->generarCodigo(),
                'fecha' => $data['fecha'],
                'tipo' => 'egreso',
                'categoria' => 'retiro',
                'concepto' => 'Cierre de caja diario y entrega de efectivo a oficina.',
                'monto' => $data['monto_contado'],
                'metodo' => 'efectivo',
                'user_id' => $data['cobrador_id'],
            ]);
        }

        $msg = $diferencia == 0.0
            ? 'Cierre de caja guardado con éxito: caja cuadrada.'
            : 'Cierre de caja guardado. Diferencia: S/ ' . number_format($diferencia, 2) . ($diferencia > 0 ? ' (sobrante)' : ' (faltante)') . '.';

        return redirect()->route('caja.index', [
            'fecha' => $data['fecha'],
            'cobrador_id' => $data['cobrador_id']
        ])->with('ok', $msg);
    }
}
