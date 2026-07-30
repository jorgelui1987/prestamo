@extends('layouts.app')

@section('title', 'Seguimiento de Promesas')
@section('topbar', 'Promesas')

@section('content')
    <h1 class="page-title">📅 Seguimiento de Promesas de Pago</h1>
    <p class="page-subtitle">Monitorea las promesas de pago registradas por los cobradores.</p>

    <div class="card" style="margin-bottom:20px">
        <div class="card__body">
            <form method="GET" action="{{ route('reportes.promesas') }}" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">
                <div>
                    <label style="display:block;font-size:12px;color:var(--text-muted);margin-bottom:4px">Cobrador</label>
                    <select name="cobrador_id" class="form-control" style="width:auto;padding:6px 12px;font-size:13px">
                        <option value="">— Todos —</option>
                        @foreach ($cobradores as $cob)
                            <option value="{{ $cob->id }}" @selected($cobradorId == $cob->id)>{{ $cob->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;color:var(--text-muted);margin-bottom:4px">Desde</label>
                    <input type="date" name="desde" value="{{ $desde }}" class="form-control" style="width:auto;padding:6px 12px;font-size:13px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;color:var(--text-muted);margin-bottom:4px">Hasta</label>
                    <input type="date" name="hasta" value="{{ $hasta }}" class="form-control" style="width:auto;padding:6px 12px;font-size:13px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;color:var(--text-muted);margin-bottom:4px">Estado</label>
                    <select name="estado" class="form-control" style="width:auto;padding:6px 12px;font-size:13px">
                        <option value="">— Todos —</option>
                        <option value="pendiente" @selected($estado === 'pendiente')>Pendiente</option>
                        <option value="cumplida" @selected($estado === 'cumplida')>Cumplida</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filtrar</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Cobrador</th>
                        <th>Cliente</th>
                        <th>Préstamo</th>
                        <th>Fecha Promesa</th>
                        <th>Observaciones</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($promesas as $v)
                        <tr>
                            <td>{{ $v->user->name }}</td>
                            <td>{{ $v->prestamo?->cliente?->nombre_completo ?? '—' }}</td>
                            <td>
                                <a href="{{ route('prestamos.show', $v->prestamo_id) }}" style="color:var(--primary)">
                                    {{ $v->prestamo?->codigo ?? '—' }}
                                </a>
                            </td>
                            <td>{{ $v->fecha_promesa ? \Illuminate\Support\Carbon::parse($v->fecha_promesa)->format('d/m/Y') : '—' }}</td>
                            <td style="max-width:200px;font-size:13px;color:var(--text-muted)">{{ $v->observaciones ?? '—' }}</td>
                            <td>
                                @if ($v->promesa_cumplida)
                                    <span class="badge-pill b-green">✅ Cumplida</span>
                                @elseif ($v->fecha_promesa && \Illuminate\Support\Carbon::parse($v->fecha_promesa)->isPast())
                                    <span class="badge-pill b-red">❌ Vencida</span>
                                @else
                                    <span class="badge-pill b-yellow">⏳ Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @if (!$v->promesa_cumplida)
                                    <form action="{{ route('promesas.cumplir', $v) }}" method="POST" style="display:inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Marcar esta promesa como cumplida?')">
                                            <i class="bi bi-check-lg"></i> Cumplida
                                        </button>
                                    </form>
                                @else
                                    <span style="color:var(--text-muted);font-size:12px">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;color:var(--text-muted);padding:36px">
                                No hay promesas de pago registradas en este período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:0 20px 18px">{{ $promesas->links() }}</div>
    </div>

    {{-- Resumen rápido --}}
    @if (count($promesas) > 0)
    <div class="card" style="margin-top:20px">
        <div class="card__header">📊 Resumen de Promesas</div>
        <div class="card__body">
            <div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
                <div class="stat-card bg-teal" style="padding:16px;text-align:center">
                    <div class="stat-label">TOTAL PROMESAS</div>
                    <div class="stat-value">{{ $totales['total'] }}</div>
                </div>
                <div class="stat-card bg-green" style="padding:16px;text-align:center">
                    <div class="stat-label">CUMPLIDAS</div>
                    <div class="stat-value">{{ $totales['cumplidas'] }}</div>
                    <div class="stat-foot">{{ $totales['total'] > 0 ? round(($totales['cumplidas'] / $totales['total']) * 100) : 0 }}% tasa</div>
                </div>
                <div class="stat-card bg-red" style="padding:16px;text-align:center">
                    <div class="stat-label">PENDIENTES / VENCIDAS</div>
                    <div class="stat-value">{{ $totales['pendientes'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection