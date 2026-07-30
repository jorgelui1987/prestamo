<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\User;
use App\Models\VisitaSinExito;
use Illuminate\Http\Request;

class RastreoController extends Controller
{
    public function index(Request $request)
    {
        $cobradorId = $request->query('cobrador_id');
        $fecha = $request->query('fecha', now()->toDateString());

        $cobradores = User::where('rol', 'cobrador')->orderBy('name')->get();
        $ubicaciones = [];

        // Obtener pagos con ubicación de la fecha seleccionada
        $pagos = Pago::with(['prestamo.cliente', 'user'])
            ->whereDate('fecha_pago', $fecha)
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
            ->latest()
            ->get();

        foreach ($pagos as $pago) {
            $ubicaciones[] = [
                'cobrador' => $pago->user->name,
                'cliente' => $pago->prestamo?->cliente?->nombre_completo ?? '—',
                'tipo' => 'cobro',
                'monto' => (float) $pago->monto,
                'lat' => (float) $pago->latitud,
                'lng' => (float) $pago->longitud,
                'created_at' => $pago->created_at->format('H:i'),
            ];
        }

        // Obtener visitas sin éxito con ubicación de la fecha seleccionada
        $visitas = VisitaSinExito::with(['prestamo.cliente', 'user'])
            ->whereDate('fecha', $fecha)
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->when($cobradorId, fn($q) => $q->where('user_id', $cobradorId))
            ->latest()
            ->get();

        foreach ($visitas as $visita) {
            $ubicaciones[] = [
                'cobrador' => $visita->user->name,
                'cliente' => $visita->prestamo?->cliente?->nombre_completo ?? '—',
                'tipo' => $visita->motivo === 'promesa_pago' ? 'promesa' : 'visita',
                'monto' => null,
                'lat' => (float) $visita->latitud,
                'lng' => (float) $visita->longitud,
                'created_at' => $visita->created_at->format('H:i'),
            ];
        }

        // Ordenar por hora descendente
        usort($ubicaciones, fn($a, $b) => $b['created_at'] <=> $a['created_at']);

        return view('reportes.rastreo', compact('ubicaciones', 'cobradores', 'cobradorId', 'fecha'));
    }
}