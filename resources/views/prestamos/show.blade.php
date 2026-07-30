@extends('layouts.app')

@section('title', 'Prestamo '.$prestamo->codigo)
@section('topbar', 'Detalle de Prestamo')

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:12px;flex-wrap:wrap">
        <div>
            <a href="{{ route('prestamos.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
            <h1 class="page-title" style="margin-top:12px">
                Prestamo {{ $prestamo->codigo }}
                @php $map = ['activo'=>'b-blue','pagado'=>'b-green','mora'=>'b-red','cancelado'=>'b-gray']; @endphp
                <span class="badge-pill {{ $map[$prestamo->estado] ?? 'b-gray' }}" style="font-size:13px;vertical-align:middle">{{ ucfirst($prestamo->estado) }}</span>
            </h1>
            <p class="page-subtitle" style="margin:0">
                Cliente: <strong>{{ $prestamo->cliente->nombre_completo ?? '-' }}</strong> · {{ $prestamo->cliente->tipo_documento ?? '' }} {{ $prestamo->cliente->documento ?? '' }}
                @if ($prestamo->numero_boleta)
                    · <span style="color: var(--danger); font-weight: 700;"><i class="bi bi-ticket-perforated-fill"></i> Boleta Rifa N° {{ $prestamo->numero_boleta }} (S/ {{ number_format($prestamo->costo_boleta, 2) }})</span>
                @endif
            </p>
        </div>
        <div style="display:flex;gap:8px">
            @if ($prestamo->estado !== 'pagado')
                <a href="{{ route('pagos.create', $prestamo) }}" class="btn btn-primary"><i class="bi bi-cash-coin"></i> Registrar pago</a>
            @endif
            <a href="{{ route('prestamos.edit', $prestamo) }}" class="btn btn-light"><i class="bi bi-pencil"></i> Editar</a>
            <button class="btn btn-light" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
        </div>
    </div>

    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
        <div class="stat-card bg-blue">
            <i class="bi bi-cash stat-icon"></i>
            <div class="stat-label">CAPITAL PRESTADO</div>
            <div class="stat-value">S/ {{ number_format($prestamo->monto, 2) }}</div>
            <div class="stat-foot">Tasa {{ rtrim(rtrim(number_format($prestamo->tasa_interes,2),'0'),'.') }}%</div>
        </div>
        <div class="stat-card bg-purple">
            <i class="bi bi-graph-up-arrow stat-icon"></i>
            <div class="stat-label">INTERES TOTAL</div>
            <div class="stat-value">S/ {{ number_format($prestamo->interes_total, 2) }}</div>
            <div class="stat-foot">Total: S/ {{ number_format($prestamo->total_pagar, 2) }}</div>
        </div>
        <div class="stat-card bg-teal">
            <i class="bi bi-check2-circle stat-icon"></i>
            <div class="stat-label">PAGADO</div>
            <div class="stat-value">S/ {{ number_format($pagado, 2) }}</div>
            <div class="stat-foot">{{ $progreso }}% completado</div>
        </div>
        <div class="stat-card bg-orange">
            <i class="bi bi-wallet2 stat-icon"></i>
            <div class="stat-label">SALDO PENDIENTE</div>
            <div class="stat-value">S/ {{ number_format($prestamo->saldo, 2) }}</div>
            <div class="stat-foot">{{ $prestamo->numero_cuotas }} cuotas · {{ ucfirst($prestamo->frecuencia) }}</div>
        </div>
    </div>

    <div class="card" style="margin-bottom:24px">
        <div class="card__body">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px">
                <span style="font-weight:600">Progreso de pago</span>
                <span style="color:var(--text-muted)">{{ $progreso }}%</span>
            </div>
            <div style="height:14px;background:#e2e8f0;border-radius:8px;overflow:hidden">
                <div style="height:100%;width:{{ $progreso }}%;background:linear-gradient(90deg,#14b8a6,#0d9488);border-radius:8px;transition:width .3s"></div>
            </div>
        </div>
    </div>

    @if ($prestamo->firma_ruta)
        <div class="card" style="margin-bottom:24px">
            <div class="card__header"><i class="bi bi-pencil-fill"></i> Firma de Conformidad del Cliente</div>
            <div class="card__body" style="text-align:center;background:#f8fafc;border-radius:12px;padding:16px">
                <img src="{{ asset('storage/' . $prestamo->firma_ruta) }}" alt="Firma del Cliente" style="max-height:120px;max-width:100%;object-fit:contain">
                <div style="font-size:11px;color:var(--text-muted);margin-top:8px">Firma digital capturada al momento del desembolso</div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card__header">
            <span>Cronograma de Cuotas</span>
            <span style="font-size:12px;color:var(--text-muted)">Inicio: {{ \Illuminate\Support\Carbon::parse($prestamo->fecha_inicio)->format('d/m/Y') }}</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>#</th><th>Vencimiento</th><th>Capital</th><th>Interes</th><th>Cuota</th><th>Pagado</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @foreach ($prestamo->cuotas as $c)
                        @php
                            $vencida = $c->estado !== 'pagado' && \Illuminate\Support\Carbon::parse($c->fecha_vencimiento)->isPast();
                            $estado = $vencida ? 'vencido' : $c->estado;
                            $bmap = ['pendiente'=>'b-yellow','pagado'=>'b-green','vencido'=>'b-red','parcial'=>'b-blue'];
                        @endphp
                        <tr>
                            <td><strong>{{ $c->numero }}</strong></td>
                            <td>{{ \Illuminate\Support\Carbon::parse($c->fecha_vencimiento)->format('d/m/Y') }}</td>
                            <td>S/ {{ number_format($c->capital, 2) }}</td>
                            <td>S/ {{ number_format($c->interes, 2) }}</td>
                            <td><strong>S/ {{ number_format($c->monto, 2) }}</strong></td>
                            <td>S/ {{ number_format($c->monto_pagado, 2) }}</td>
                            <td><span class="badge-pill {{ $bmap[$estado] ?? 'b-gray' }}">{{ ucfirst($estado) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight:700;background:#f8fafc">
                        <td colspan="2">TOTALES</td>
                        <td>S/ {{ number_format($prestamo->cuotas->sum('capital'), 2) }}</td>
                        <td>S/ {{ number_format($prestamo->cuotas->sum('interes'), 2) }}</td>
                        <td>S/ {{ number_format($prestamo->cuotas->sum('monto'), 2) }}</td>
                        <td>S/ {{ number_format($pagado, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
