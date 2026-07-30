<?php

namespace App\Mail;

use App\Models\MovimientoCaja;
use App\Models\Pago;
use App\Models\Prestamo;
use App\Models\Cuota;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class ReporteDiario extends Mailable
{
    use Queueable, SerializesModels;

    public array $datos;
    public string $empresa;

    public function __construct(array $datos, string $empresa = 'Mi Empresa')
    {
        $this->datos = $datos;
        $this->empresa = $empresa;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📊 Resumen Diario - ' . $this->empresa . ' - ' . now()->format('d/m/Y'),
            from: new Address(config('mail.from.address'), config('mail.from.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.reporte-diario',
        );
    }
}