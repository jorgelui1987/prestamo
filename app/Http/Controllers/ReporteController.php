<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cuota;
use App\Models\Empeno;
use App\Models\MovimientoCaja;
use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public const TIPOS = [
        'prestamos' => ['titulo' => 'Cartera de Prestamos', 'icono' => 'bi-cash-stack', 'fecha' => true],
        'pagos' => ['titulo' => 'Pagos / Cobros', 'icono' => 'bi-credit-card-2-front', 'fecha' => true],
        'mora' => ['titulo' => 'Cartera en Mora', 'icono' => 'bi-exclamation-triangle', 'fecha' => false],
        'clientes' => ['titulo' => 'Clientes', 'icono' => 'bi-people', 'fecha' => false],
        'empenos' => ['titulo' => 'Empenos', 'icono' => 'bi-gem', 'fecha' => false],
        'caja' => ['titulo' => 'Movimientos de Caja', 'icono' => 'bi-cash', 'fecha' => true],
    ];

    public function index()
    {
        return view('reportes.index', ['tipos' => self::TIPOS]);
    }

    public function enviarReporteDiario(Request $request)
    {
        $email = $request->input('email');
        try {
            \Illuminate\Support\Facades\Artisan::call('reportes:diario', $email ? ['--email' => $email] : []);
            return redirect()->route('reportes.index')->with('ok', '📊 Reporte diario enviado correctamente a los administradores.');
        } catch (\Exception $e) {
            return redirect()->route('reportes.index')->with('ok', 'Error: ' . $e->getMessage());
        }
    }

    public function ver(Request $request, string $tipo)
    {
        abort_unless(isset(self::TIPOS[$tipo]), 404);

        [$desde, $hasta] = $this->rango($request);
        $data = $this->dataset($tipo, $desde, $hasta);

        return view('reportes.ver', [
            'tipo' => $tipo,
            'meta' => self::TIPOS[$tipo],
            'headers' => $data['headers'],
            'rows' => $data['rows'],
            'totales' => $data['totales'] ?? [],
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }

    public function excel(Request $request, string $tipo): StreamedResponse
    {
        abort_unless(isset(self::TIPOS[$tipo]), 404);

        [$desde, $hasta] = $this->rango($request);
        $data = $this->dataset($tipo, $desde, $hasta);
        $filename = 'reporte_'.$tipo.'_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel
            fputcsv($out, $data['headers'], ';');
            foreach ($data['rows'] as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function rango(Request $request): array
    {
        $desde = $request->query('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->query('hasta', now()->toDateString());

        return [$desde, $hasta];
    }

    private function dataset(string $tipo, string $desde, string $hasta): array
    {
        return match ($tipo) {
            'prestamos' => $this->repPrestamos($desde, $hasta),
            'pagos' => $this->repPagos($desde, $hasta),
            'mora' => $this->repMora(),
            'clientes' => $this->repClientes(),
            'empenos' => $this->repEmpenos(),
            'caja' => $this->repCaja($desde, $hasta),
        };
    }

    private function repPrestamos(string $desde, string $hasta): array
    {
        $query = Prestamo::with('cliente')
            ->whereBetween('fecha_inicio', [$desde, $hasta])
            ->orderBy('fecha_inicio');

        $totales = [
            'Monto' => (float) $query->sum('monto'),
            'Total a pagar' => (float) $query->sum('total_pagar'),
            'Saldo' => (float) $query->sum('saldo'),
        ];

        $rows = [];
        foreach ($query->lazy() as $p) {
            $rows[] = [
                $p->codigo,
                $p->cliente->nombre_completo ?? '',
                number_format($p->monto, 2, '.', ''),
                number_format($p->tasa_interes, 2, '.', ''),
                $p->numero_cuotas,
                $p->frecuencia,
                number_format($p->total_pagar, 2, '.', ''),
                number_format($p->saldo, 2, '.', ''),
                $p->fecha_inicio->format('d/m/Y'),
                ucfirst($p->estado),
            ];
        }

        return [
            'headers' => ['Codigo', 'Cliente', 'Monto', 'Tasa %', 'Cuotas', 'Frecuencia', 'Total a pagar', 'Saldo', 'Inicio', 'Estado'],
            'rows' => $rows,
            'totales' => $totales,
        ];
    }

    private function repPagos(string $desde, string $hasta): array
    {
        $query = Pago::with('prestamo.cliente')
            ->whereBetween('fecha_pago', [$desde, $hasta])
            ->orderBy('fecha_pago');

        $totales = [
            'Monto' => (float) $query->sum('monto'),
        ];

        $rows = [];
        foreach ($query->lazy() as $p) {
            $rows[] = [
                $p->codigo,
                $p->fecha_pago->format('d/m/Y'),
                $p->prestamo->codigo ?? '',
                $p->prestamo->cliente->nombre_completo ?? '',
                number_format($p->monto, 2, '.', ''),
                ucfirst($p->metodo),
            ];
        }

        return [
            'headers' => ['Codigo', 'Fecha', 'Prestamo', 'Cliente', 'Monto', 'Metodo'],
            'rows' => $rows,
            'totales' => $totales,
        ];
    }

    private function repMora(): array
    {
        Cuota::actualizarVencidas();
        $query = Cuota::whereHas('prestamo')->with('prestamo.cliente')
            ->where('estado', 'vencido')
            ->orderBy('fecha_vencimiento');

        $totalDeuda = 0.0;
        $rows = [];
        foreach ($query->lazy() as $c) {
            $deuda = (float) $c->monto - (float) $c->monto_pagado;
            $totalDeuda += $deuda;
            $rows[] = [
                $c->prestamo->codigo ?? '',
                $c->prestamo->cliente->nombre_completo ?? '',
                $c->prestamo->cliente->telefono ?? '',
                $c->numero,
                $c->fecha_vencimiento->format('d/m/Y'),
                $c->dias_atraso,
                number_format($deuda, 2, '.', ''),
            ];
        }

        return [
            'headers' => ['Prestamo', 'Cliente', 'Telefono', 'Cuota', 'Vencimiento', 'Dias atraso', 'Deuda'],
            'rows' => $rows,
            'totales' => ['Deuda' => $totalDeuda],
        ];
    }

    private function repClientes(): array
    {
        $query = Cliente::orderBy('nombres');

        $rows = [];
        foreach ($query->lazy() as $c) {
            $rows[] = [
                $c->codigo,
                $c->nombre_completo,
                $c->tipo_documento.' '.$c->documento,
                $c->telefono,
                $c->ocupacion,
                ucfirst($c->estado),
            ];
        }

        return [
            'headers' => ['Codigo', 'Nombre', 'Documento', 'Telefono', 'Ocupacion', 'Estado'],
            'rows' => $rows,
        ];
    }

    private function repEmpenos(): array
    {
        Empeno::actualizarVencidos();
        $query = Empeno::with('cliente')->orderBy('fecha_vencimiento');

        $totalPrestado = 0.0;
        $totalRecuperar = 0.0;

        $rows = [];
        foreach ($query->lazy() as $e) {
            $totalPrestado += (float) $e->monto_prestado;
            $totalRecuperar += (float) $e->total_recuperar;

            $rows[] = [
                $e->codigo,
                $e->articulo,
                $e->cliente->nombre_completo ?? '',
                number_format($e->valor_tasacion, 2, '.', ''),
                number_format($e->monto_prestado, 2, '.', ''),
                number_format($e->total_recuperar, 2, '.', ''),
                $e->fecha_vencimiento->format('d/m/Y'),
                ucfirst($e->estado),
            ];
        }

        return [
            'headers' => ['Codigo', 'Articulo', 'Cliente', 'Tasacion', 'Prestado', 'A recuperar', 'Vence', 'Estado'],
            'rows' => $rows,
            'totales' => ['Prestado' => $totalPrestado, 'A recuperar' => $totalRecuperar],
        ];
    }

    private function repCaja(string $desde, string $hasta): array
    {
        $query = MovimientoCaja::with('user')
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha');

        $totalIngresos = (float) MovimientoCaja::whereBetween('fecha', [$desde, $hasta])->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = (float) MovimientoCaja::whereBetween('fecha', [$desde, $hasta])->where('tipo', 'egreso')->sum('monto');

        $rows = [];
        foreach ($query->lazy() as $m) {
            $rows[] = [
                $m->codigo,
                $m->fecha->format('d/m/Y'),
                ucfirst($m->tipo),
                ucfirst(str_replace('_', ' ', $m->categoria)),
                $m->concepto,
                number_format($m->monto, 2, '.', ''),
                ucfirst($m->metodo),
            ];
        }

        return [
            'headers' => ['Codigo', 'Fecha', 'Tipo', 'Categoria', 'Concepto', 'Monto', 'Metodo'],
            'rows' => $rows,
            'totales' => [
                'Ingresos' => $totalIngresos,
                'Egresos' => $totalEgresos,
            ],
        ];
    }
}
