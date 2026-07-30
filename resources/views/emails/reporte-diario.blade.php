<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Diario</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; margin: 0; padding: 0; background: #f1f5f9; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #1e3a8a, #2563eb); color: white; padding: 30px; text-align: center; border-radius: 16px 16px 0 0; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 5px 0 0; opacity: 0.9; font-size: 14px; }
        .body-card { background: white; padding: 24px; border-radius: 0 0 16px 16px; }
        .section { margin-bottom: 24px; }
        .section-title { font-size: 14px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #e2e8f0; }
        .stat-row { display: flex; gap: 12px; margin-bottom: 12px; }
        .stat-card { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; text-align: center; }
        .stat-value { font-size: 22px; font-weight: 800; color: #1e293b; }
        .stat-label { font-size: 11px; color: #64748b; text-transform: uppercase; margin-top: 4px; }
        .stat-card.green .stat-value { color: #10b981; }
        .stat-card.red .stat-value { color: #ef4444; }
        .stat-card.blue .stat-value { color: #3b82f6; }
        .stat-card.orange .stat-value { color: #f59e0b; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #f1f5f9; color: #475569; font-weight: 600; padding: 8px 10px; text-align: left; }
        td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; }
        .rank-1 { color: #f59e0b; font-weight: 700; }
        .rank-2 { color: #94a3b8; font-weight: 600; }
        .rank-3 { color: #92400e; font-weight: 600; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #94a3b8; }
        .btn { display: inline-block; background: #3b82f6; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 13px; }
        .mora-badge { display: inline-block; background: #fef2f2; color: #dc2626; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .alerta { background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 14px; margin-bottom: 16px; font-size: 13px; color: #991b1b; }
        .alerta strong { display: block; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>📊 {{ $empresa }}</h1>
            <p>Resumen diario - {{ now()->format('d/m/Y') }}</p>
        </div>

        <div class="body-card">
            {{-- Alertas --}}
            @if (($datos['vencidas'] ?? 0) > 0)
                <div class="alerta">
                    <strong>⚠️ {{ $datos['vencidas'] }} cliente(s) en mora</strong>
                    Se requiere gestión de cobranza urgente.
                </div>
            @endif

            {{-- Cobros del día --}}
            <div class="section">
                <div class="section-title">💰 Cobros del Día</div>
                <div class="stat-row">
                    <div class="stat-card green">
                        <div class="stat-value">S/ {{ number_format($datos['total_cobros'] ?? 0, 2) }}</div>
                        <div class="stat-label">Total Cobrado</div>
                    </div>
                    <div class="stat-card blue">
                        <div class="stat-value">{{ $datos['cantidad_cobros'] ?? 0 }}</div>
                        <div class="stat-label">Cobros Realizados</div>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-card">
                        <div class="stat-value">S/ {{ number_format($datos['efectivo'] ?? 0, 2) }}</div>
                        <div class="stat-label">Efectivo</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">S/ {{ number_format($datos['transferencia'] ?? 0, 2) }}</div>
                        <div class="stat-label">Transferencia</div>
                    </div>
                </div>
            </div>

            {{-- Gastos --}}
            @if (($datos['total_gastos'] ?? 0) > 0)
            <div class="section">
                <div class="section-title">💸 Gastos del Día</div>
                <div class="stat-row">
                    <div class="stat-card red">
                        <div class="stat-value">- S/ {{ number_format($datos['total_gastos'] ?? 0, 2) }}</div>
                        <div class="stat-label">Total Gastos</div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-value">S/ {{ number_format($datos['neto'] ?? 0, 2) }}</div>
                        <div class="stat-label">Neto del Día</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Clientes visitados --}}
            <div class="section">
                <div class="section-title">👥 Clientes Visitados</div>
                <div class="stat-row">
                    <div class="stat-card green">
                        <div class="stat-value">{{ $datos['pagaron'] ?? 0 }}</div>
                        <div class="stat-label">Pagaron</div>
                    </div>
                    <div class="stat-card red">
                        <div class="stat-value">{{ $datos['no_pagaron'] ?? 0 }}</div>
                        <div class="stat-label">No Pagaron</div>
                    </div>
                </div>
            </div>

            {{-- Ranking de Cobradores --}}
            @if (!empty($datos['ranking']))
            <div class="section">
                <div class="section-title">🏆 Ranking de Cobradores</div>
                <table>
                    <thead>
                        <tr><th>#</th><th>Cobrador</th><th style="text-align:right">Cobrado</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($datos['ranking'] as $i => $r)
                            <tr>
                                <td class="{{ 'rank-' . min($i + 1, 3) }}">
                                    {{ $i + 1 === 1 ? '🥇' : ($i + 1 === 2 ? '🥈' : ($i + 1 === 3 ? '🥉' : $i + 1)) }}
                                </td>
                                <td>{{ $r['nombre'] }}</td>
                                <td style="text-align:right;font-weight:600">S/ {{ number_format($r['total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Cartera en Mora --}}
            @if (!empty($datos['mora']))
            <div class="section">
                <div class="section-title">⚠️ Cartera en Mora</div>
                <table>
                    <thead>
                        <tr><th>Cliente</th><th>Cuota</th><th>Días</th><th style="text-align:right">Deuda</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($datos['mora'] as $m)
                            <tr>
                                <td>{{ $m['cliente'] }}</td>
                                <td>#{{ $m['cuota'] }}</td>
                                <td><span class="mora-badge">{{ $m['dias'] }} días</span></td>
                                <td style="text-align:right;color:#dc2626;font-weight:600">S/ {{ number_format($m['deuda'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Enlace al sistema --}}
            <div style="text-align:center;margin-top:20px">
                <a href="{{ config('app.url') }}" class="btn">📌 Ver Detalle Completo</a>
            </div>
        </div>

        <div class="footer">
            <p>Este correo fue generado automáticamente por {{ config('app.name') }}.</p>
            <p>© {{ date('Y') }} {{ $empresa }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>