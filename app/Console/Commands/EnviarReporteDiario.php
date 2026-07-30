<?php

namespace App\Console\Commands;

use App\Mail\ReporteDiario;
use App\Models\Cuota;
use App\Models\MovimientoCaja;
use App\Models\Pago;
use App\Models\User;
use App\Models\VisitaSinExito;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarReporteDiario extends Command
{
    protected $signature = 'reportes:diario {--email= : Correo específico al que enviar}';
    protected $description = 'Envía el resumen diario de cobros, gastos y mora por correo electrónico';

    public function handle()
    {
        // Configurar SMTP desde la base de datos
        $mailHost = \App\Models\Configuracion::get('mail_host', 'smtp.hostinger.com');
        $mailPort = \App\Models\Configuracion::get('mail_port', '587');
        $mailUsername = \App\Models\Configuracion::get('mail_username', '');
        $mailPassword = \App\Models\Configuracion::get('mail_password', '');
        $mailEncryption = \App\Models\Configuracion::get('mail_encryption', 'tls');
        $mailFromAddress = \App\Models\Configuracion::get('mail_from_address', '');
        $mailFromName = \App\Models\Configuracion::get('mail_from_name', 'Sistema de Prestamos');

        // Si no hay usuario configurado, mostrar error
        if (empty($mailUsername) || empty($mailPassword)) {
            $this->error('❌ Correo SMTP no configurado. Ve a Configuración → Correo SMTP y completa los datos.');
            return 1;
        }

        // Configurar dinámicamente el mailer
        config([
            'mail.mailers.smtp.host' => $mailHost,
            'mail.mailers.smtp.port' => (int) $mailPort,
            'mail.mailers.smtp.username' => $mailUsername,
            'mail.mailers.smtp.password' => $mailPassword,
            'mail.mailers.smtp.encryption' => $mailEncryption ?: null,
            'mail.from.address' => $mailFromAddress ?: $mailUsername,
            'mail.from.name' => $mailFromName,
        ]);

        $hoy = now()->toDateString();
        $empresa = \App\Models\Configuracion::get('empresa_nombre', 'Mi Empresa');

        // 1. Cobros del día
        $cobrosHoy = Pago::whereDate('fecha_pago', $hoy);
        $totalCobros = (float) $cobrosHoy->sum('monto');
        $cantidadCobros = $cobrosHoy->count();
        $efectivo = (float) Pago::whereDate('fecha_pago', $hoy)->where('metodo', 'efectivo')->sum('monto');
        $transferencia = (float) Pago::whereDate('fecha_pago', $hoy)->where('metodo', 'transferencia')->sum('monto');

        // 2. Gastos del día
        $gastosHoy = MovimientoCaja::whereDate('fecha', $hoy)->where('tipo', 'egreso');
        $totalGastos = (float) $gastosHoy->sum('monto');
        $neto = $totalCobros - $totalGastos;

        // 3. Clientes visitados
        $pagaron = Pago::whereDate('fecha_pago', $hoy)->distinct('prestamo_id')->count('prestamo_id');
        $noPagaron = VisitaSinExito::whereDate('fecha', $hoy)->count();

        // 4. Cuotas vencidas
        Cuota::actualizarVencidas();
        $vencidas = Cuota::where('estado', 'vencido')->count();

        // 5. Ranking de cobradores
        $ranking = User::where('rol', 'cobrador')
            ->where('activo', true)
            ->get()
            ->map(function ($user) use ($hoy) {
                $total = (float) Pago::where('user_id', $user->id)
                    ->whereDate('fecha_pago', $hoy)
                    ->sum('monto');
                return [
                    'nombre' => $user->name,
                    'total' => $total,
                ];
            })
            ->filter(fn($r) => $r['total'] > 0)
            ->sortByDesc('total')
            ->values()
            ->take(5)
            ->toArray();

        // 6. Mora (top 10)
        $mora = Cuota::where('estado', 'vencido')
            ->with('prestamo.cliente')
            ->orderBy('fecha_vencimiento')
            ->take(10)
            ->get()
            ->map(function ($c) {
                return [
                    'cliente' => $c->prestamo->cliente->nombre_completo ?? 'N/A',
                    'cuota' => $c->numero,
                    'dias' => $c->dias_atraso,
                    'deuda' => round((float) $c->monto - (float) $c->monto_pagado, 2),
                ];
            })
            ->toArray();

        $datos = [
            'total_cobros' => $totalCobros,
            'cantidad_cobros' => $cantidadCobros,
            'efectivo' => $efectivo,
            'transferencia' => $transferencia,
            'total_gastos' => $totalGastos,
            'neto' => $neto,
            'pagaron' => $pagaron,
            'no_pagaron' => $noPagaron,
            'vencidas' => $vencidas,
            'ranking' => $ranking,
            'mora' => $mora,
        ];

        // Determinar destinatarios
        $email = $this->option('email');
        if ($email) {
            // Enviar a un correo específico
            Mail::to($email)->send(new ReporteDiario($datos, $empresa));
            $this->info("Reporte enviado a {$email}");
        } else {
            // Enviar a todos los administradores
            $admins = User::whereIn('rol', ['admin', 'superadmin'])
                ->where('activo', true)
                ->get();

            if ($admins->isEmpty()) {
                $this->warn('No hay administradores activos para enviar el reporte.');
                return 0;
            }

            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new ReporteDiario($datos, $empresa));
                $this->info("Reporte enviado a {$admin->email} ({$admin->name})");
            }
        }

        $this->info('Reporte diario enviado correctamente.');
        return 0;
    }
}