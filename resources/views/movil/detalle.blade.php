<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <title>Detalle del Cliente</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --primary: #3b82f6;
            --success: #10b981;
            --danger: #ef4444;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        body { background-color: var(--bg-dark); color: var(--text-main); padding-bottom: 40px; }
        .app-header {
            background-color: var(--bg-card);
            padding: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #334155;
        }
        .back-btn { color: var(--text-main); font-size: 20px; text-decoration: none; }
        .app-title { font-size: 16px; font-weight: 700; }
        .container { padding: 16px; }
        .section { margin-bottom: 20px; }
        .section-title {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .info-card {
            background-color: var(--bg-card);
            border-radius: 16px;
            padding: 16px;
            border: 1px solid #334155;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #1e293b;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-size: 12px; color: var(--text-muted); }
        .info-value { font-size: 14px; font-weight: 600; text-align: right; }
        .info-value.green { color: var(--success); }
        .info-value.red { color: var(--danger); }
        .info-value.blue { color: var(--primary); }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-green { background: rgba(16,185,129,0.2); color: #4ade80; }
        .badge-red { background: rgba(239,68,68,0.2); color: #f87171; }
        .badge-yellow { background: rgba(245,158,11,0.2); color: #fbbf24; }
        .badge-blue { background: rgba(59,130,246,0.2); color: #60a5fa; }
        .action-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 16px;
        }
        .action-btn {
            background-color: #334155;
            color: var(--text-main);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .action-btn:active { background-color: #475569; }
        .action-btn.primary { background-color: var(--primary); }
        .action-btn.primary:active { background-color: #2563eb; }
        .action-btn.success { background-color: var(--success); }
        .action-btn.success:active { background-color: #059669; }
        .action-btn.danger { background-color: var(--danger); }
        .action-btn.danger:active { background-color: #dc2626; }
        .action-btn.full { grid-column: span 2; }
        .semaforo-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }
        .cuota-item {
            background-color: #0f172a;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cuota-num { font-weight: 700; font-size: 14px; }
        .cuota-info { text-align: right; font-size: 13px; }
        .cuota-info small { color: var(--text-muted); font-size: 11px; }
    </style>
</head>
<body>
    <header class="app-header">
        <a href="{{ route('movil.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i></a>
        <div class="app-title">Detalle del Cliente</div>
    </header>

    <div class="container">
        {{-- SECCIÓN: DATOS DEL CLIENTE --}}
        <div class="section">
            <div class="section-title"><i class="bi bi-person-fill"></i> Datos del Cliente</div>
            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Nombre</span>
                    <span class="info-value" style="display:flex;align-items:center;gap:8px">
                        <span class="semaforo-dot" style="background:{{ $prestamo->cliente->semaforo_color }}" title="{{ $prestamo->cliente->semaforo_label }}"></span>
                        {{ $prestamo->cliente->nombre_completo }}
                    </span>
                </div>
                @if ($prestamo->cliente->documento)
                <div class="info-row">
                    <span class="info-label">Documento</span>
                    <span class="info-value">{{ $prestamo->cliente->tipo_documento }} {{ $prestamo->cliente->documento }}</span>
                </div>
                @endif
                @if ($prestamo->cliente->telefono)
                <div class="info-row">
                    <span class="info-label">Teléfono</span>
                    <span class="info-value blue">{{ $prestamo->cliente->telefono }}</span>
                </div>
                @endif
                @if ($prestamo->cliente->direccion)
                <div class="info-row">
                    <span class="info-label">Dirección</span>
                    <span class="info-value" style="font-size:13px">{{ $prestamo->cliente->direccion }}</span>
                </div>
                @endif
                @if ($prestamo->cliente->ocupacion)
                <div class="info-row">
                    <span class="info-label">Ocupación</span>
                    <span class="info-value">{{ $prestamo->cliente->ocupacion }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Semáforo</span>
                    <span class="info-value">
                        <span class="badge {{ $prestamo->cliente->semaforo_color === '#ef4444' ? 'badge-red' : ($prestamo->cliente->semaforo_color === '#10b981' ? 'badge-green' : 'badge-yellow') }}">
                            {{ $prestamo->cliente->semaforo_label }}
                        </span>
                    </span>
                </div>
            </div>
        </div>

        {{-- SECCIÓN: DATOS DEL PRÉSTAMO --}}
        <div class="section">
            <div class="section-title"><i class="bi bi-cash-stack"></i> Datos del Préstamo</div>
            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Código</span>
                    <span class="info-value blue">{{ $prestamo->codigo }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Monto Prestado</span>
                    <span class="info-value">{{ \App\Models\Configuracion::get('moneda', 'S/') }} {{ number_format($prestamo->monto, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Interés</span>
                    <span class="info-value">{{ $prestamo->tasa_interes }}%</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total a Pagar</span>
                    <span class="info-value">{{ \App\Models\Configuracion::get('moneda', 'S/') }} {{ number_format($prestamo->total_pagar, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Saldo Pendiente</span>
                    <span class="info-value {{ $saldo > 0 ? 'red' : 'green' }}">{{ \App\Models\Configuracion::get('moneda', 'S/') }} {{ number_format($saldo, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cuotas</span>
                    <span class="info-value">{{ $prestamo->numero_cuotas }} ({{ $prestamo->frecuencia }})</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Valor Cuota</span>
                    <span class="info-value">{{ \App\Models\Configuracion::get('moneda', 'S/') }} {{ number_format($prestamo->monto_cuota, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estado</span>
                    <span class="info-value">
                        <span class="badge {{ $prestamo->estado === 'activo' ? 'badge-green' : ($prestamo->estado === 'mora' ? 'badge-red' : 'badge-blue') }}">
                            {{ ucfirst($prestamo->estado) }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Inicio</span>
                    <span class="info-value">{{ \Illuminate\Support\Carbon::parse($prestamo->fecha_inicio)->format('d/m/Y') }}</span>
                </div>
                @if ($prestamo->observaciones)
                <div class="info-row">
                    <span class="info-label">Observaciones</span>
                    <span class="info-value" style="font-size:13px;color:var(--text-muted)">{{ $prestamo->observaciones }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- SECCIÓN: CRONOGRAMA DE CUOTAS --}}
        <div class="section">
            <div class="section-title"><i class="bi bi-list-check"></i> Cronograma de Cuotas</div>
            <div class="info-card" style="padding:12px">
                @foreach ($prestamo->cuotas as $cuota)
                    @php
                        $estadoCuota = $cuota->estado;
                        $badgeClass = $estadoCuota === 'pagado' ? 'badge-green' : ($estadoCuota === 'vencido' ? 'badge-red' : ($estadoCuota === 'parcial' ? 'badge-yellow' : 'badge-blue'));
                    @endphp
                    <div class="cuota-item">
                        <div>
                            <div class="cuota-num">Cuota #{{ $cuota->numero }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">
                                Vence: {{ \Illuminate\Support\Carbon::parse($cuota->fecha_vencimiento)->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="cuota-info">
                            <div>{{ \App\Models\Configuracion::get('moneda', 'S/') }} {{ number_format($cuota->monto, 2) }}</div>
                            <div>
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($estadoCuota) }}</span>
                                @if ($cuota->monto_pagado > 0)
                                    <small>Pag: {{ \App\Models\Configuracion::get('moneda', 'S/') }} {{ number_format($cuota->monto_pagado, 2) }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- BOTONES DE ACCIÓN --}}
        <div class="action-grid">
            @if ($prestamo->cliente->telefono)
                <a href="tel:{{ $prestamo->cliente->telefono }}" class="action-btn" style="color:#38bdf8">
                    <i class="bi bi-telephone-fill"></i> Llamar
                </a>
                <a href="https://wa.me/51{{ $prestamo->cliente->telefono }}?text=Hola%20{{ urlencode($prestamo->cliente->nombres) }},%20te%20saluda%20tu%20cobrador%20de%20Tecnicell." target="_blank" class="action-btn" style="color:#4ade80">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
            @endif
            @if ($prestamo->cliente->direccion)
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($prestamo->cliente->direccion) }}" target="_blank" class="action-btn" style="color:#fb7185">
                    <i class="bi bi-geo-alt-fill"></i> Maps
                </a>
            @endif
            <a href="{{ route('movil.cobrar', $prestamo->id) }}" class="action-btn success full">
                <i class="bi bi-cash-coin"></i> COBRAR AHORA
            </a>
        </div>
    </div>
</body>
</html>