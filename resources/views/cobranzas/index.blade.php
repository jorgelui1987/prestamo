@extends('layouts.app')

@section('title', 'Cobranzas')
@section('topbar', 'Cobranzas')

@section('content')
    <h1 class="page-title">Bandeja de Cobranza</h1>
    <p class="page-subtitle">Cuotas por cobrar, vencidas y próximas a vencer.</p>

    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
        <div class="stat-card bg-red" style="background:linear-gradient(135deg,#ef4444,#dc2626)">
            <i class="bi bi-exclamation-octagon stat-icon"></i>
            <div class="stat-label">CUOTAS VENCIDAS</div>
            <div class="stat-value">{{ $resumen['vencidas'] }}</div>
            <div class="stat-foot">S/ {{ number_format($resumen['montoVencido'], 2) }} en mora</div>
        </div>
        <div class="stat-card bg-orange">
            <i class="bi bi-calendar-day stat-icon"></i>
            <div class="stat-label">VENCEN HOY</div>
            <div class="stat-value">{{ $resumen['hoy'] }}</div>
        </div>
        <div class="stat-card bg-blue">
            <i class="bi bi-wallet2 stat-icon"></i>
            <div class="stat-label">TOTAL POR COBRAR</div>
            <div class="stat-value">S/ {{ number_format($resumen['porCobrar'], 2) }}</div>
        </div>
        <div class="stat-card bg-teal">
            <i class="bi bi-list-check stat-icon"></i>
            <div class="stat-label">EN BANDEJA</div>
            <div class="stat-value">{{ $cuotas->total() }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card__header" style="gap:12px;flex-wrap:wrap">
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;flex-grow:1">
                <div style="display:flex;gap:6px;flex-wrap:wrap">
                    @foreach (['todas'=>'Todas','vencidas'=>'Vencidas','hoy'=>'Hoy','proximas'=>'Próximos 7 días'] as $v=>$l)
                        <a href="{{ route('cobranzas.index', ['filtro'=>$v, 'q'=>$buscar, 'cobrador_id'=>$cobradorId]) }}"
                           class="btn btn-sm {{ $filtro===$v ? 'btn-primary' : 'btn-light' }}">{{ $l }}</a>
                    @endforeach
                </div>
                
                @if (!$isCobrador)
                    <form method="GET" action="{{ route('cobranzas.index') }}" style="display:flex;gap:6px;align-items:center;margin:0">
                        <input type="hidden" name="filtro" value="{{ $filtro }}">
                        <input type="hidden" name="q" value="{{ $buscar }}">
                        <select name="cobrador_id" class="form-control" style="width:auto;padding:4px 10px;font-size:13px;height:32px;" onchange="this.form.submit()">
                            <option value="">— Todos los Cobradores —</option>
                            @foreach ($cobradores as $cob)
                                <option value="{{ $cob->id }}" @selected($cobradorId == $cob->id)>{{ $cob->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
            <form method="GET" class="topbar__search" style="margin:0">
                <input type="hidden" name="filtro" value="{{ $filtro }}">
                <input type="hidden" name="cobrador_id" value="{{ $cobradorId }}">
                <i class="bi bi-search" style="color:#94a3b8"></i>
                <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar cliente...">
            </form>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Préstamo</th><th>Cliente / Cuota</th><th>Vence</th><th>Atraso</th><th>Pendiente</th><th>Estado</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    @forelse ($cuotas as $c)
                        @php $bmap = ['pendiente'=>'b-yellow','vencido'=>'b-red','parcial'=>'b-blue']; @endphp
                        <tr>
                            <td>
                                <a href="{{ route('prestamos.show', $c->prestamo_id) }}" style="color:var(--primary);font-weight:600">
                                    {{ $c->prestamo->codigo ?? '—' }}
                                </a>
                                @if ($c->prestamo?->orden_ruta > 0)
                                    <br><span class="badge-pill b-gray" style="font-size:10px;padding:1px 6px;" title="Orden de Ruta">Ruta #{{ $c->prestamo->orden_ruta }}</span>
                                @endif
                            </td>
                             <td>
                                <strong>{{ $c->prestamo?->cliente?->nombre_completo ?? '—' }}</strong>
                                <span class="badge-pill b-indigo" style="font-size:11px; padding:2px 8px; margin-left:6px;">Cuota #{{ $c->numero }}</span>
                                @if ($c->prestamo?->cliente?->telefono)
                                    <div style="margin-top:2px; font-size:11px; color:var(--text-muted)">
                                        <i class="bi bi-telephone"></i> {{ $c->prestamo->cliente->telefono }}
                                    </div>
                                @endif
                             </td>
                            <td>{{ \Illuminate\Support\Carbon::parse($c->fecha_vencimiento)->format('d/m/Y') }}</td>
                            <td>
                                @if ($c->dias_atraso > 0)
                                    <span class="badge-pill b-red">{{ $c->dias_atraso }} día(s)</span>
                                @else
                                    <span style="color:var(--text-muted)">—</span>
                                @endif
                            </td>
                            <td><strong>S/ {{ number_format($c->monto - $c->monto_pagado, 2) }}</strong></td>
                            <td><span class="badge-pill {{ $bmap[$c->estado] ?? 'b-gray' }}">{{ ucfirst($c->estado) }}</span></td>
                             <td>
                                <div style="display:flex;gap:4px;flex-wrap:nowrap">
                                    @if ($c->prestamo?->cliente?->telefono)
                                        <a href="https://wa.me/51{{ $c->prestamo->cliente->telefono }}?text=Hola%20{{ urlencode($c->prestamo->cliente->nombres ?? '') }},%20te%20saluda%20Cobranzas.%20Hoy%20toca%20la%20cuota%20%23{{ $c->numero }}%20por%20S/%20{{ number_format($c->monto + $c->mora - $c->monto_pagado, 2) }}.%20¿A%20qué%20hora%20te%20puedo%20visitar?" target="_blank" class="btn btn-success btn-sm" title="Enviar WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('pagos.create', $c->prestamo_id) }}" class="btn btn-primary btn-sm"><i class="bi bi-cash-coin"></i> Cobrar</a>
                                </div>
                             </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:36px">No hay cuotas en esta bandeja. 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:0 20px 18px">{{ $cuotas->links() }}</div>
    </div>
@endsection
