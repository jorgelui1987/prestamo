@extends('layouts.app')

@section('title', 'Efectividad de Cobradores')
@section('topbar', 'Efectividad')

@section('content')
    <h1 class="page-title">📊 Reporte de Efectividad</h1>
    <p class="page-subtitle">Métricas de rendimiento individual de cada cobrador en un período.</p>

    <div class="card" style="margin-bottom:20px">
        <div class="card__body">
            <form method="GET" action="{{ route('reportes.efectividad') }}" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">
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
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filtrar</button>
            </form>
        </div>
    </div>

    @forelse ($reportes as $r)
        <div class="card" style="margin-bottom:16px;border-left:4px solid {{ $r['porcentaje_meta'] >= 100 ? '#10b981' : ($r['porcentaje_meta'] >= 50 ? '#f59e0b' : '#ef4444') }}">
            <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
                <div style="display:flex;align-items:center;gap:10px">
                    <strong style="font-size:16px">{{ $r['cobrador'] }}</strong>
                    @if ($loop->first && count($reportes) > 1)
                        <span class="badge-pill" style="background:#f59e0b;color:white;font-size:10px">🥇 LÍDER</span>
                    @endif
                </div>
                <div style="display:flex;gap:12px;align-items:center;font-size:13px">
                    <span style="color:var(--text-muted)">Meta: <strong>S/ {{ number_format($r['meta_periodo'], 0) }}</strong></span>
                    <span style="font-weight:800;color:{{ $r['porcentaje_meta'] >= 100 ? '#10b981' : ($r['porcentaje_meta'] >= 50 ? '#f59e0b' : '#ef4444') }}">
                        {{ $r['porcentaje_meta'] }}%
                    </span>
                </div>
            </div>
            <div class="card__body">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px">
                    <div style="text-align:center;padding:12px;background:#f8fafc;border-radius:10px">
                        <div style="font-size:22px;font-weight:800;color:var(--primary)">S/ {{ number_format($r['total_cobrado'], 0) }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">Total Cobrado</div>
                    </div>
                    <div style="text-align:center;padding:12px;background:#f8fafc;border-radius:10px">
                        <div style="font-size:22px;font-weight:800;color:{{ $r['tasa_cobro'] >= 50 ? '#10b981' : '#ef4444' }}">{{ $r['tasa_cobro'] }}%</div>
                        <div style="font-size:11px;color:var(--text-muted)">Tasa de Cobro</div>
                        <div style="font-size:10px;color:#94a3b8">{{ $r['cobros_realizados'] }} cobros / {{ $r['cobros_realizados'] + $r['visitas_sin_exito'] }} gestiones</div>
                    </div>
                    <div style="text-align:center;padding:12px;background:#f8fafc;border-radius:10px">
                        <div style="font-size:22px;font-weight:800;color:#8b5cf6">S/ {{ number_format($r['promedio_por_cobro'], 2) }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">Promedio x Cobro</div>
                    </div>
                    <div style="text-align:center;padding:12px;background:#f8fafc;border-radius:10px">
                        <div style="font-size:22px;font-weight:800;color:#f59e0b">{{ $r['visitas_sin_exito'] }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">Visitas sin Éxito</div>
                    </div>
                    <div style="text-align:center;padding:12px;background:#f8fafc;border-radius:10px">
                        <div style="font-size:22px;font-weight:800;color:#06b6d4">{{ $r['promesas_registradas'] }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">Promesas Registradas</div>
                    </div>
                    <div style="text-align:center;padding:12px;background:#f8fafc;border-radius:10px">
                        <div style="font-size:22px;font-weight:800;color:{{ $r['tasa_promesas'] >= 50 ? '#10b981' : '#ef4444' }}">{{ $r['tasa_promesas'] }}%</div>
                        <div style="font-size:11px;color:var(--text-muted)">% Promesas Cumplidas</div>
                        <div style="font-size:10px;color:#94a3b8">{{ $r['promesas_cumplidas'] }}/{{ $r['promesas_registradas'] }}</div>
                    </div>
                    <div style="text-align:center;padding:12px;background:#f8fafc;border-radius:10px">
                        <div style="font-size:22px;font-weight:800;color:#6366f1">S/ {{ number_format($r['cartera_asignada'], 0) }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">Cartera Asignada</div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card__body" style="text-align:center;padding:40px;color:var(--text-muted)">
                <i class="bi bi-bar-chart" style="font-size:48px;display:block;margin-bottom:12px"></i>
                No hay datos para el período seleccionado.
            </div>
        </div>
    @endforelse
@endsection