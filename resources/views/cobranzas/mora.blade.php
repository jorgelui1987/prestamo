@extends('layouts.app')

@section('title', 'Mora')
@section('topbar', 'Mora')

@section('content')
    <h1 class="page-title">Gestión de Mora</h1>
    <p class="page-subtitle">Cuotas vencidas que requieren gestión de cobranza.</p>

    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
        <div class="stat-card" style="background:linear-gradient(135deg,#ef4444,#dc2626)">
            <i class="bi bi-exclamation-triangle stat-icon"></i>
            <div class="stat-label">CUOTAS VENCIDAS</div>
            <div class="stat-value">{{ $resumen['cuotas'] }}</div>
        </div>
        <div class="stat-card bg-orange">
            <i class="bi bi-cash-coin stat-icon"></i>
            <div class="stat-label">MONTO EN MORA</div>
            <div class="stat-value">S/ {{ number_format($resumen['monto'], 2) }}</div>
        </div>
        <div class="stat-card bg-purple">
            <i class="bi bi-person-exclamation stat-icon"></i>
            <div class="stat-label">PRÉSTAMOS EN MORA</div>
            <div class="stat-value">{{ $resumen['prestamos'] }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card__header" style="gap:12px;flex-wrap:wrap">
            <span>Cartera vencida ({{ $cuotas->total() }})</span>
            <form method="GET" class="topbar__search" style="margin:0">
                <i class="bi bi-search" style="color:#94a3b8"></i>
                <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar cliente...">
            </form>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Préstamo</th><th>Cliente</th><th>Teléfono</th><th>Cuota</th><th>Venció</th><th>Días</th><th>Deuda</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    @forelse ($cuotas as $c)
                        <tr>
                            <td><a href="{{ route('prestamos.show', $c->prestamo_id) }}" style="color:var(--primary);font-weight:600">{{ $c->prestamo->codigo ?? '—' }}</a></td>
                            <td>{{ $c->prestamo?->cliente?->nombre_completo ?? '—' }}</td>
                            <td>{{ $c->prestamo?->cliente?->telefono ?? '—' }}</td>
                            <td>#{{ $c->numero }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($c->fecha_vencimiento)->format('d/m/Y') }}</td>
                            <td><span class="badge-pill b-red">{{ $c->dias_atraso }}</span></td>
                            <td><strong>S/ {{ number_format($c->monto - $c->monto_pagado, 2) }}</strong></td>
                            <td><a href="{{ route('pagos.create', $c->prestamo_id) }}" class="btn btn-primary btn-sm"><i class="bi bi-cash-coin"></i> Cobrar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:36px">¡Sin cuotas vencidas! 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:0 20px 18px">{{ $cuotas->links() }}</div>
    </div>
@endsection
