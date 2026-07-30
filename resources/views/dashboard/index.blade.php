@extends('layouts.app')

@section('title', 'Panel de Control')
@section('topbar', 'Panel de Control')

@section('content')
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; gap:12px; flex-wrap:wrap">
        <div>
            <h1 class="page-title" style="margin:0">Panel de Control</h1>
            <p class="page-subtitle" style="margin:0">Resumen general de la operación · {{ now()->translatedFormat('d \d\e F, Y') }}</p>
        </div>

        {{-- Filtro de Cobrador --}}
        @if(auth()->user()->rol !== 'cobrador')
        <form method="GET" action="{{ route('dashboard') }}" style="margin:0">
            <select name="cobrador_id" class="form-control" style="width:auto; padding:8px 16px; font-size:14px; font-weight:600; border-radius:10px; border:1px solid #cbd5e1; background-color:white; cursor:pointer" onchange="this.form.submit()">
                <option value="">— Ver Todo (Global) —</option>
                @foreach ($listaCobradores as $cob)
                    <option value="{{ $cob->id }}" @selected($cobradorId == $cob->id)>
                        Cobrador: {{ $cob->name }}
                    </option>
                @endforeach
            </select>
        </form>
        @endif
    </div>

    {{-- ===== TARJETAS ===== --}}
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="stat-card bg-green" style="background: linear-gradient(135deg, #10b981, #059669);">
            <i class="bi bi-calendar-check-fill stat-icon"></i>
            <div class="stat-label">COBRADO HOY</div>
            <div class="stat-value">S/ {{ number_format($cobradoHoy, 2) }}</div>
            <div class="stat-foot" style="font-size: 11px; opacity: 0.9;">
                Ef: S/ {{ number_format($cobradoHoyEfectivo, 2) }} | Transf: S/ {{ number_format($cobradoHoyTransferencia, 2) }}
            </div>
        </div>
        <div class="stat-card bg-blue">
            <i class="bi bi-people-fill stat-icon"></i>
            <div class="stat-label">CLIENTES</div>
            <div class="stat-value">{{ number_format($totalClientes) }}</div>
            <div class="stat-foot">Registrados en el sistema</div>
        </div>
        <div class="stat-card bg-teal">
            <i class="bi bi-cash-stack stat-icon"></i>
            <div class="stat-label">CAPITAL PRESTADO</div>
            <div class="stat-value">S/ {{ number_format($capitalPrestado, 2) }}</div>
            <div class="stat-foot">Activo en la calle</div>
        </div>
        <div class="stat-card bg-purple">
            <i class="bi bi-graph-up-arrow stat-icon"></i>
            <div class="stat-label">GANANCIA / INTERESES</div>
            <div class="stat-value">S/ {{ number_format($gananciaInteres, 2) }}</div>
            <div class="stat-foot">Interés proyectado</div>
        </div>
        <div class="stat-card bg-orange">
            <i class="bi bi-gem stat-icon"></i>
            <div class="stat-label">EMPEÑOS</div>
            <div class="stat-value">{{ number_format($totalEmpenos) }}</div>
            <div class="stat-foot">Artículos en garantía</div>
        </div>
    </div>

    {{-- ===== META DIARIA ===== --}}
    @php
        $metaDiaria = 5000; // Meta fija, se puede hacer configurable después
        $porcentajeMeta = $metaDiaria > 0 ? min(round(($cobradoHoy / $metaDiaria) * 100), 100) : 0;
        $colorMeta = $porcentajeMeta >= 100 ? '#10b981' : ($porcentajeMeta >= 50 ? '#f59e0b' : '#ef4444');
    @endphp
    <div class="card" style="margin-bottom:24px;background:linear-gradient(135deg,#1e293b,#0f172a);border:1px solid #334155;color:#f8fafc">
        <div class="card__body">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
                <div>
                    <div style="font-size:13px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px">🎯 META DEL DÍA</div>
                    <div style="font-size:24px;font-weight:800;color:{{ $colorMeta }}">
                        S/ {{ number_format($cobradoHoy, 2) }}
                        <span style="font-size:14px;font-weight:400;color:#94a3b8">de S/ {{ number_format($metaDiaria, 2) }}</span>
                    </div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:36px;font-weight:800;color:{{ $colorMeta }}">{{ $porcentajeMeta }}%</div>
                    <div style="font-size:12px;color:#94a3b8">{{ $porcentajeMeta >= 100 ? '🎉 ¡Meta cumplida!' : ($porcentajeMeta >= 50 ? '💪 Vas bien' : '🚀 Aún puedes') }}</div>
                </div>
            </div>
            <div style="width:100%;height:10px;background:#334155;border-radius:6px;overflow:hidden">
                <div style="height:100%;width:{{ $porcentajeMeta }}%;background:linear-gradient(90deg,{{ $colorMeta }},{{ $porcentajeMeta >= 100 ? '#10b981' : ($porcentajeMeta >= 50 ? '#f59e0b' : '#ef4444') }});border-radius:6px;transition:width 0.5s ease"></div>
            </div>
        </div>
    </div>

    {{-- ===== GRÁFICOS ===== --}}
    <div class="grid-2" style="margin-bottom:24px">
        <div class="card">
            <div class="card__header">📊 Estado de la Cartera</div>
            <div class="card__body">
                <div class="chart-wrap"><canvas id="carteraChart"></canvas></div>
            </div>
        </div>
        <div class="card">
            <div class="card__header">📈 Balance: Prestado vs Recuperado</div>
            <div class="card__body">
                <div class="chart-wrap"><canvas id="balanceChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- ===== GRÁFICOS ADICIONALES ===== --}}
    <div class="grid-2" style="margin-bottom:24px">
        <div class="card">
            <div class="card__header">📅 Cobros de los Últimos 7 Días</div>
            <div class="card__body">
                <div class="chart-wrap"><canvas id="cobrosSemanaChart"></canvas></div>
            </div>
        </div>
        <div class="card">
            <div class="card__header">💳 Distribución por Método de Pago</div>
            <div class="card__body">
                <div class="chart-wrap"><canvas id="metodosChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- ===== RENDIMIENTO DE COBRADORES ===== --}}
    <div class="card" style="margin-top:24px">
        <div class="card__header">
            <span>Rendimiento de Cobradores (Hoy)</span>
            <span style="font-size:12px; color:var(--text-muted)">Monitoreo en tiempo real de la ruta diaria</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Cobrador</th>
                        <th>Progreso de Ruta</th>
                        <th style="text-align:center">Visitas Realizadas</th>
                        <th style="text-align:center">Meta Diaria</th>
                        <th style="text-align:center">Pendientes</th>
                        <th style="text-align:right">Total Recaudado Hoy</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rendimientoCobradores as $rc)
                        <tr>
                            <td><strong>{{ $rc['nombre'] }}</strong></td>
                            <td style="width:25%">
                                <div style="display:flex; align-items:center; gap:10px">
                                    <div style="flex-grow:1; height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden">
                                        <div style="height:100%; width:{{ $rc['porcentaje'] }}%; background:linear-gradient(90deg, #10b981, #059669); border-radius:4px;"></div>
                                    </div>
                                    <span style="font-size:12px; font-weight:700; min-width:35px">{{ $rc['porcentaje'] }}%</span>
                                </div>
                            </td>
                            <td style="text-align:center">
                                <span class="badge-pill b-blue" style="font-weight:700">
                                    {{ $rc['cobros_realizados'] }} / {{ $rc['ruta_total'] }}
                                </span>
                            </td>
                            <td style="text-align:center">
                                @php
                                    $colorMeta = $rc['porcentaje_meta'] >= 100 ? '#10b981' : ($rc['porcentaje_meta'] >= 50 ? '#f59e0b' : '#ef4444');
                                @endphp
                                <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                                    <span style="font-size:11px;font-weight:600;color:{{ $colorMeta }}">
                                        S/ {{ number_format($rc['monto_cobrado'], 0) }} / S/ {{ number_format($rc['meta_diaria'], 0) }}
                                    </span>
                                    <div style="width:60px;height:5px;background:#e2e8f0;border-radius:3px;overflow:hidden">
                                        <div style="height:100%;width:{{ $rc['porcentaje_meta'] }}%;background:{{ $colorMeta }};border-radius:3px"></div>
                                    </div>
                                    <span style="font-size:10px;font-weight:700;color:{{ $colorMeta }}">{{ $rc['porcentaje_meta'] }}%</span>
                                </div>
                            </td>
                            <td style="text-align:center">
                                <div style="display:flex;flex-direction:column;gap:2px;font-size:11px">
                                    @if ($rc['visitas_sin_exito'] > 0)
                                        <span style="color:#ef4444;font-weight:600">
                                            <i class="bi bi-x-octagon"></i> {{ $rc['visitas_sin_exito'] }} sin éxito
                                        </span>
                                    @endif
                                    @if ($rc['promesas_hoy'] > 0)
                                        <span style="color:#f59e0b;font-weight:600">
                                            <i class="bi bi-calendar-check"></i> {{ $rc['promesas_hoy'] }} promesas
                                        </span>
                                    @endif
                                    @if ($rc['visitas_sin_exito'] == 0 && $rc['promesas_hoy'] == 0)
                                        <span style="color:#94a3b8">—</span>
                                    @endif
                                </div>
                            </td>
                            <td style="text-align:right; font-weight:800; color:var(--success)">
                                S/ {{ number_format($rc['monto_cobrado'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; color:var(--text-muted); padding:24px">
                                No hay cobradores registrados en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== ACCESOS RÁPIDOS ===== --}}
    <h2 class="section-title">Accesos Rápidos</h2>
    <div class="quick-grid">
        <a href="{{ route('prestamos.create') }}" class="quick-card">
            <div class="q-icon bg-blue"><i class="bi bi-plus-circle"></i></div>
            <div><div class="q-title">Nuevo Préstamo</div><div class="q-desc">Registrar entrega de dinero</div></div>
        </a>
        <a href="{{ route('empenos.create') }}" class="quick-card">
            <div class="q-icon bg-purple"><i class="bi bi-gem"></i></div>
            <div><div class="q-title">Nuevo Empeño</div><div class="q-desc">Registrar artículo en garantía</div></div>
        </a>
        <a href="{{ route('clientes.create') }}" class="quick-card">
            <div class="q-icon bg-teal"><i class="bi bi-person-plus"></i></div>
            <div><div class="q-title">Nuevo Cliente</div><div class="q-desc">Registrar nueva persona</div></div>
        </a>
    </div>

    {{-- ===== PRÉSTAMOS RECIENTES ===== --}}
    <div class="card" style="margin-top:24px">
        <div class="card__header">
            Préstamos Recientes
            <a href="{{ route('prestamos.index') }}" class="btn btn-light btn-sm">Ver todos</a>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Código</th><th>Cliente</th><th>Monto</th><th>Cuotas</th><th>Saldo</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @forelse ($prestamosRecientes as $p)
                        <tr>
                            <td><strong>{{ $p->codigo }}</strong></td>
                            <td>{{ $p->cliente?->nombre_completo ?? '—' }}</td>
                            <td>S/ {{ number_format($p->monto, 2) }}</td>
                            <td>{{ $p->numero_cuotas }}</td>
                            <td>S/ {{ number_format($p->saldo, 2) }}</td>
                            <td>
                                @php
                                    $map = ['activo'=>'b-blue','pagado'=>'b-green','mora'=>'b-red','cancelado'=>'b-gray'];
                                @endphp
                                <span class="badge-pill {{ $map[$p->estado] ?? 'b-gray' }}">{{ ucfirst($p->estado) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px">Sin préstamos registrados aún.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Donut - Estado de la cartera
    new Chart(document.getElementById('carteraChart'), {
        type: 'doughnut',
        data: {
            labels: ['Al día', 'Pagadas', 'Vencidas'],
            datasets: [{
                data: [{{ $cuotasAlDia }}, {{ $cuotasPagadas }}, {{ $cuotasVencidas }}],
                backgroundColor: ['#3b82f6', '#22c55e', '#ef4444'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '62%',
            plugins: { legend: { position: 'bottom', labels: { padding: 18, font: { size: 12 } } } }
        }
    });

    // Barras - Prestado vs Recuperado
    new Chart(document.getElementById('balanceChart'), {
        type: 'bar',
        data: {
            labels: ['Total Prestado', 'Total Recuperado'],
            datasets: [{
                label: 'Soles (S/)',
                data: [{{ $totalPrestado }}, {{ $totalRecuperado }}],
                backgroundColor: ['#2563eb', '#06b6d4'],
                borderRadius: 8, barThickness: 90,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'S/ ' + v.toLocaleString() } } }
        }
    });

    // Líneas - Cobros últimos 7 días
    new Chart(document.getElementById('cobrosSemanaChart'), {
        type: 'line',
        data: {
            labels: [{!! "'" . implode("','", $diasSemana) . "'" !!}],
            datasets: [{
                label: 'Soles cobrados',
                data: [{{ implode(',', $cobrosSemana) }}],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3b82f6',
                pointRadius: 5,
                pointHoverRadius: 8,
                borderWidth: 3,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { 
                y: { beginAtZero: true, ticks: { callback: v => 'S/ ' + v.toLocaleString() } },
                x: { grid: { display: false } }
            }
        }
    });

    @if (!empty($metodosPago))
    // Doughnut - Métodos de pago
    new Chart(document.getElementById('metodosChart'), {
        type: 'doughnut',
        data: {
            labels: [{!! "'" . implode("','", array_column($metodosPago, 'metodo')) . "'" !!}],
            datasets: [{
                data: [{{ implode(',', array_column($metodosPago, 'total')) }}],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '62%',
            plugins: { 
                legend: { position: 'bottom', labels: { padding: 18, font: { size: 12 } } },
                tooltip: { callbacks: { label: ctx => 'S/ ' + ctx.parsed.toLocaleString() } }
            }
        }
    });
    @endif
</script>
@endpush
