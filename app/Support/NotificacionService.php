<?php

namespace App\Support;

use App\Models\Cuota;
use App\Models\Empeno;
use App\Models\VisitaSinExito;
use Illuminate\Support\Carbon;

/**
 * Genera las notificaciones operativas que se muestran en la campana del topbar.
 */
class NotificacionService
{
    /** @return array{items: array, total: int} */
    public static function obtener(): array
    {
        $hoy = Carbon::today();
        $items = [];

        $user = auth()->user();
        if (!$user || $user->esSuperAdmin()) {
            return ['items' => [], 'total' => 0];
        }

        // 1. Cuotas vencidas (en mora) de la empresa actual
        $vencidas = Cuota::whereHas('prestamo')->where('estado', 'vencido')->count();
        if ($vencidas > 0) {
            $items[] = [
                'icono' => 'bi-exclamation-octagon-fill',
                'color' => '#ef4444',
                'titulo' => $vencidas.' '.($vencidas === 1 ? 'cuota vencida' : 'cuotas vencidas'),
                'detalle' => 'Requieren gestión de cobranza',
                'url' => route('mora.index'),
            ];
        }

        // 2. Cuotas por vencer en los próximos 3 días de la empresa actual
        $porVencer = Cuota::whereHas('prestamo')
            ->where('estado', 'pendiente')
            ->whereBetween('fecha_vencimiento', [$hoy, $hoy->copy()->addDays(3)])
            ->count();
        if ($porVencer > 0) {
            $items[] = [
                'icono' => 'bi-clock-history',
                'color' => '#f59e0b',
                'titulo' => $porVencer.' '.($porVencer === 1 ? 'cuota por vencer' : 'cuotas por vencer'),
                'detalle' => 'Vencen en los próximos 3 días',
                'url' => route('cobranzas.index'),
            ];
        }

        // 3. Empeños próximos a vencer (5 días) o vencidos sin renovar de la empresa actual
        $puedeEmpenos = in_array($user->rol, ['admin', 'gerente', 'operador'], true);
        $empenos = $puedeEmpenos ? Empeno::where('estado', 'vigente')
            ->where('fecha_vencimiento', '<=', $hoy->copy()->addDays(5))
            ->count() : 0;
        if ($empenos > 0) {
            $items[] = [
                'icono' => 'bi-gem',
                'color' => '#8b5cf6',
                'titulo' => $empenos.' '.($empenos === 1 ? 'empeño por vencer' : 'empeños por vencer'),
                'detalle' => 'Próximos a su fecha límite',
                'url' => route('empenos.index'),
            ];
        }

        // 4. Promesas de pago para hoy (NO cumplidas aún)
        $promesasHoy = VisitaSinExito::where('motivo', 'promesa_pago')
            ->whereDate('fecha_promesa', $hoy)
            ->where('promesa_cumplida', false)
            ->count();
        if ($promesasHoy > 0) {
            $items[] = [
                'icono' => 'bi-calendar-check-fill',
                'color' => '#06b6d4',
                'titulo' => $promesasHoy.' '.($promesasHoy === 1 ? 'promesa de pago para hoy' : 'promesas de pago para hoy'),
                'detalle' => 'Clientes que prometieron pagar hoy',
                'url' => route('reportes.promesas'),
            ];
        }

        // 5. Promesas de pago vencidas (días anteriores sin cumplir)
        $promesasVencidas = VisitaSinExito::where('motivo', 'promesa_pago')
            ->whereDate('fecha_promesa', '<', $hoy)
            ->where('promesa_cumplida', false)
            ->count();
        if ($promesasVencidas > 0) {
            $items[] = [
                'icono' => 'bi-x-circle-fill',
                'color' => '#f97316',
                'titulo' => $promesasVencidas.' '.($promesasVencidas === 1 ? 'promesa vencida sin cumplir' : 'promesas vencidas sin cumplir'),
                'detalle' => 'Requieren seguimiento',
                'url' => route('reportes.promesas', ['estado' => 'pendiente']),
            ];
        }

        // 6. Visitas sin éxito registradas hoy
        $visitasHoy = VisitaSinExito::whereDate('fecha', $hoy)
            ->where('user_id', $user->id)
            ->count();
        if ($visitasHoy > 0) {
            $items[] = [
                'icono' => 'bi-person-x-fill',
                'color' => '#6b7280',
                'titulo' => $visitasHoy.' '.($visitasHoy === 1 ? 'visita sin éxito hoy' : 'visitas sin éxito hoy'),
                'detalle' => 'Clientes que no pudieron pagar',
                'url' => route('reportes.index'),
            ];
        }

        return ['items' => $items, 'total' => count($items)];
    }
}