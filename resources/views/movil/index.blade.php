<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Tecnicell">
    <link rel="apple-touch-icon" href="/img/icons/icon.svg">
    <link rel="apple-touch-icon" sizes="192x192" href="/img/icons/icon.svg">
    <link rel="icon" type="image/svg+xml" href="/img/icons/icon.svg">
    <title>Tecnicell Cobranzas</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <script src="/js/offline-sync.js"></script>
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
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            padding-bottom: 80px;
        }
        .app-header {
            background-color: var(--bg-card);
            padding: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #334155;
        }
        .app-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .container {
            padding: 16px;
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }
        .stat-box {
            background-color: var(--bg-card);
            padding: 12px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #334155;
        }
        .stat-num {
            font-size: 18px;
            font-weight: 700;
        }
        .stat-num.red { color: var(--danger); }
        .stat-num.green { color: var(--success); }
        .stat-lbl {
            font-size: 10px;
            color: var(--text-muted);
            margin-top: 4px;
            text-transform: uppercase;
        }
        .search-bar {
            width: 100%;
            background-color: var(--bg-card);
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 12px 16px;
            color: var(--text-main);
            font-size: 14px;
            margin-bottom: 16px;
            outline: none;
        }
        .search-bar:focus {
            border-color: var(--primary);
        }
        .client-card {
            background-color: var(--bg-card);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid #334155;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .client-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .client-name {
            font-size: 15px;
            font-weight: 700;
        }
        .route-badge {
            background-color: #334155;
            color: var(--text-main);
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 8px;
            font-weight: 600;
        }
        .client-info {
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .client-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 4px;
        }
        .action-btn {
            background-color: #334155;
            color: var(--text-main);
            border: none;
            border-radius: 10px;
            padding: 10px;
            font-size: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .action-btn:active {
            background-color: #475569;
        }
        .action-btn.btn-cobrar {
            grid-column: span 2;
            background-color: var(--primary);
            font-weight: 700;
            font-size: 13px;
            gap: 6px;
        }
        .action-btn.btn-cobrar:active {
            background-color: #2563eb;
        }
        .action-btn.btn-call { color: #38bdf8; }
        .action-btn.btn-wa { color: #4ade80; }
        .action-btn.btn-map { color: #fb7185; }
        
        .nav-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: var(--bg-card);
            border-top: 1px solid #334155;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            z-index: 100;
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 11px;
            gap: 4px;
        }
        .nav-item.active {
            color: var(--primary);
        }
        .nav-item i {
            font-size: 20px;
        }
        .alert-success {
            background-color: rgba(16, 185, 129, 0.15);
            border: 1px solid var(--success);
            color: #34d399;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        /* Badge flotante de pendientes */
        .offline-badge {
            position: fixed;
            top: 16px;
            right: 60px;
            background: #f59e0b;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 8px;
            z-index: 101;
            display: none;
            align-items: center;
            gap: 3px;
            box-shadow: 0 2px 8px rgba(245,158,11,0.4);
        }
    </style>
</head>
<body>

    <div id="offline-badge" class="offline-badge">
        <i class="bi bi-cloud-arrow-up"></i>
        <span id="offline-count">0</span>
    </div>

    <header class="app-header">
        @php
            $logo = \App\Models\Configuracion::get('empresa_logo');
            $nombreEmpresa = \App\Models\Configuracion::get('empresa_nombre', 'TECNICELL APP');
        @endphp
        <div class="app-title">
            @if (!empty($logo))
                <div style="width: 32px; height: 32px; overflow: hidden; border-radius: 6px; display: inline-block; margin-right: 4px;">
                    <img src="{{ \App\Support\StorageHelper::url($logo) }}" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
            @else
                <i class="bi bi-cash-coin"></i>
            @endif
            <span style="font-size: 15px; font-weight: 800; text-transform: uppercase;">{{ $nombreEmpresa }}</span>
        </div>
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: var(--text-muted); font-size: 20px;">
            <i class="bi bi-box-arrow-right"></i>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </header>

    <div class="container">
        @if (session('ok'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> {{ session('ok') }}
            </div>
        @endif

        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-num">{{ $resumen['total_clientes'] }}</div>
                <div class="stat-lbl">Clientes</div>
            </div>
            <div class="stat-box">
                <div class="stat-num red">{{ $resumen['vencidas'] }}</div>
                <div class="stat-lbl">Vencidas</div>
            </div>
            <div class="stat-box">
                <div class="stat-num green">{{ $resumen['hoy'] }}</div>
                <div class="stat-lbl">Hoy</div>
            </div>
        </div>

        <form method="GET" action="{{ route('movil.index') }}">
            <input type="text" name="q" value="{{ $buscar }}" class="search-bar" placeholder="Buscar cliente por nombre o DNI..." autocomplete="off">
        </form>

        <div id="banner-offline" style="display:none; background:rgba(239,68,68,0.15); border:1px solid var(--danger); border-radius:12px; padding:10px 14px; margin-bottom:12px; font-size:12px; color:var(--danger); align-items:center; gap:8px;">
            <i class="bi bi-wifi-off"></i> <strong>Sin conexión</strong> — Los cobros se guardarán en tu celular y se sincronizarán automáticamente cuando recuperes señal.
        </div>
        <div id="banner-online" style="display:none; background:rgba(16,185,129,0.15); border:1px solid var(--success); border-radius:12px; padding:10px 14px; margin-bottom:12px; font-size:12px; color:var(--success); align-items:center; gap:8px;">
            <i class="bi bi-wifi"></i> <strong>Conectado</strong> — Puedes cobrar normalmente.
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h2 style="font-size: 14px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin:0">Ruta de Cobranza</h2>
            <div style="display:flex; gap:6px; align-items:center;">
                <button id="btn-sync-manual" onclick="sincronizarManual()" style="display:none; background-color:#10b981; color:white; border:none; border-radius:8px; padding:6px 10px; font-size:11px; font-weight:700; align-items:center; gap:4px; cursor:pointer" title="Sincronizar datos pendientes">
                    <i class="bi bi-cloud-arrow-up"></i> <span class="sync-label">0</span>
                </button>
                <button onclick="abrirModalGasto()" class="btn btn-sm" style="background-color:var(--danger); color:white; border:none; border-radius:8px; padding:6px 12px; font-size:12px; font-weight:700; display:flex; align-items:center; gap:4px">
                    <i class="bi bi-dash-circle-fill"></i> + Gasto
                </button>
            </div>
        </div>

        @forelse ($cuotas as $c)
            <div class="client-card">
                <div class="client-header">
                    <div>
                        <div class="client-name" style="display:flex;align-items:center;gap:8px">
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $c->prestamo->cliente->semaforo_color }};display:inline-block;flex-shrink:0" title="{{ $c->prestamo->cliente->semaforo_label }}"></span>
                            {{ $c->prestamo->cliente->nombre_completo }}
                        </div>
                        <a href="{{ route('movil.detalle', $c->prestamo_id) }}" style="text-decoration:none">
                            <div style="font-size: 11px; color: var(--primary); font-weight: 600; margin-top: 2px; display:flex;align-items:center;gap:4px">
                                <i class="bi bi-info-circle"></i> Préstamo: {{ $c->prestamo->codigo }} · Cuota #{{ $c->numero }}
                            </div>
                        </a>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px">
                        @if ($c->prestamo->orden_ruta > 0)
                            <span class="route-badge">Ruta #{{ $c->prestamo->orden_ruta }}</span>
                        @endif
                        <div style="display:flex; gap:4px">
                            <form action="{{ route('movil.ruta') }}" method="POST" style="margin:0">
                                @csrf
                                <input type="hidden" name="prestamo_id" value="{{ $c->prestamo_id }}">
                                <input type="hidden" name="direccion" value="subir">
                                <button type="submit" class="btn btn-sm" style="background:#334155; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:10px" title="Subir en ruta">
                                    <i class="bi bi-chevron-up"></i>
                                </button>
                            </form>
                            <form action="{{ route('movil.ruta') }}" method="POST" style="margin:0">
                                @csrf
                                <input type="hidden" name="prestamo_id" value="{{ $c->prestamo_id }}">
                                <input type="hidden" name="direccion" value="bajar">
                                <button type="submit" class="btn btn-sm" style="background:#334155; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:10px" title="Bajar en ruta">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="client-info">
                    <div><i class="bi bi-calendar-event"></i> Vence: {{ \Illuminate\Support\Carbon::parse($c->fecha_vencimiento)->format('d/m/Y') }}</div>
                    <div><i class="bi bi-cash"></i> Monto Cuota: {{ \App\Models\Configuracion::get('moneda', 'S/') }} {{ number_format($c->monto, 2) }}</div>
                    @if ($c->mora > 0)
                        <div style="color: var(--danger); font-weight: 600;"><i class="bi bi-exclamation-triangle"></i> Mora: {{ \App\Models\Configuracion::get('moneda', 'S/') }} {{ number_format($c->mora, 2) }}</div>
                    @endif
                    <div style="font-weight: 700; color: var(--text-main); margin-top: 4px;">
                        <i class="bi bi-wallet2"></i> Total Pendiente: {{ \App\Models\Configuracion::get('moneda', 'S/') }} {{ number_format($c->monto + $c->mora - $c->monto_pagado, 2) }}
                    </div>
                </div>

                <div class="client-actions" style="grid-template-columns: repeat(5, 1fr);">
                    @if ($c->prestamo->cliente->telefono)
                        <a href="tel:{{ $c->prestamo->cliente->telefono }}" class="action-btn btn-call" title="Llamar">
                            <i class="bi bi-telephone-fill"></i>
                        </a>
                        <a href="https://wa.me/51{{ $c->prestamo->cliente->telefono }}?text=Hola%20{{ urlencode($c->prestamo->cliente->nombres) }},%20te%20saluda%20tu%20cobrador%20de%20Tecnicell.%20Hoy%20toca%20la%20cuota%20%23{{ $c->numero }}%20por%20un%20monto%20de%20{{ \App\Models\Configuracion::get('moneda', 'S/') }}%20{{ number_format($c->monto + $c->mora - $c->monto_pagado, 2) }}.%20¿A%20qué%20hora%20te%20puedo%20visitar?" target="_blank" class="action-btn btn-wa" title="WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    @else
                        <button class="action-btn btn-call" disabled style="opacity: 0.3;"><i class="bi bi-telephone-fill"></i></button>
                        <button class="action-btn btn-wa" disabled style="opacity: 0.3;"><i class="bi bi-whatsapp"></i></button>
                    @endif

                    @if ($c->prestamo->cliente->direccion)
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($c->prestamo->cliente->direccion) }}" target="_blank" class="action-btn btn-map" title="Ver Mapa">
                            <i class="bi bi-geo-alt-fill"></i>
                        </a>
                    @else
                        <button class="action-btn btn-map" disabled style="opacity: 0.3;"><i class="bi bi-geo-alt-fill"></i></button>
                    @endif

                    <button onclick="abrirModalNoPago({{ $c->prestamo_id }})" class="action-btn" style="color: var(--danger);" title="Visita sin Éxito">
                        <i class="bi bi-x-octagon-fill"></i>
                    </button>

                    <a href="{{ route('movil.cobrar', $c->prestamo_id) }}" class="action-btn btn-cobrar">
                        <i class="bi bi-cash-coin"></i> COBRAR
                    </a>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                <i class="bi bi-emoji-smile" style="font-size: 48px; color: var(--success); display: block; margin-bottom: 12px;"></i>
                <strong>¡Felicidades!</strong><br>No tienes cuotas pendientes de cobro para hoy.
            </div>
        @endforelse
    </div>

    <nav class="nav-bar">
        <a href="{{ route('movil.index') }}" class="nav-item active">
            <i class="bi bi-house-door-fill"></i>
            <span>Inicio</span>
        </a>
        <a href="{{ route('movil.historial') }}" class="nav-item">
            <i class="bi bi-clock-history"></i>
            <span>Mis Cobros</span>
        </a>
        <a href="{{ route('clientes.create') }}?origen=movil" class="nav-item">
            <i class="bi bi-person-plus-fill"></i>
            <span>+ Cliente</span>
        </a>
        <a href="{{ route('prestamos.create') }}?origen=movil" class="nav-item">
            <i class="bi bi-cash-stack"></i>
            <span>+ Préstamo</span>
        </a>
    </nav>

    {{-- Modal de Registro de Gasto --}}
    <div id="modalGasto" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.85); z-index:1000; align-items:center; justify-content:center; padding:16px;">
        <div style="background:var(--bg-card); border:1px solid #334155; border-radius:16px; width:100%; max-width:400px; padding:20px; box-shadow:0 10px 25px rgba(0,0,0,0.5)">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px">
                <h3 style="font-size:16px; font-weight:700; color:var(--text-main)"><i class="bi bi-dash-circle-fill" style="color:var(--danger)"></i> Registrar Gasto</h3>
                <button onclick="cerrarModalGasto()" style="background:none; border:none; color:var(--text-muted); font-size:20px"><i class="bi bi-x"></i></button>
            </div>
            <form action="{{ route('movil.gasto') }}" method="POST" id="formGasto">
                @csrf
                <div style="margin-bottom:12px">
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px">Concepto / Descripción *</label>
                    <input type="text" name="concepto" required style="width:100%; background:#0f172a; border:1px solid #334155; border-radius:8px; padding:10px; color:white; font-size:14px" placeholder="Ej: Gasolina para la moto">
                </div>
                <div style="margin-bottom:12px">
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px">Monto (S/) *</label>
                    <input type="number" step="0.01" min="0.01" name="monto" required style="width:100%; background:#0f172a; border:1px solid #334155; border-radius:8px; padding:10px; color:white; font-size:14px" placeholder="0.00">
                </div>
                <div style="margin-bottom:16px">
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px">Método de Pago *</label>
                <select name="metodo" required style="width:100%; background:#0f172a; border:1px solid #334155; border-radius:8px; padding:10px; color:white; font-size:14px">
                    <option value="efectivo">Efectivo</option>
                    <option value="yape">Yape</option>
                    <option value="plin">Plin</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>
            <button type="submit" style="width:100%; background:var(--danger); color:white; border:none; border-radius:10px; padding:12px; font-weight:700; font-size:14px">Guardar Gasto</button>
        </form>
    </div>
</div>

    {{-- Modal de Visita sin Éxito --}}
    <div id="modalNoPago" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.85); z-index:1000; align-items:center; justify-content:center; padding:16px;">
        <div style="background:var(--bg-card); border:1px solid #334155; border-radius:16px; width:100%; max-width:400px; padding:20px; box-shadow:0 10px 25px rgba(0,0,0,0.5)">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px">
                <h3 style="font-size:16px; font-weight:700; color:var(--text-main)"><i class="bi bi-x-octagon-fill" style="color:var(--danger)"></i> Visita sin Éxito</h3>
                <button onclick="cerrarModalNoPago()" style="background:none; border:none; color:var(--text-muted); font-size:20px"><i class="bi bi-x"></i></button>
            </div>
            <form action="{{ route('movil.no-pago') }}" method="POST" id="formNoPago">
                @csrf
                <input type="hidden" name="prestamo_id" id="no_pago_prestamo_id">
                <div style="margin-bottom:12px">
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px">Motivo de la Visita sin Éxito *</label>
                    <select name="motivo" required style="width:100%; background:#0f172a; border:1px solid #334155; border-radius:8px; padding:10px; color:white; font-size:14px">
                        <option value="no_estaba">No se encontraba en su domicilio</option>
                        <option value="no_dinero">No tenía dinero hoy</option>
                        <option value="promesa_pago">Promesa de pago para mañana</option>
                        <option value="local_cerrado">Local o negocio cerrado</option>
                        <option value="otro">Otro motivo</option>
                    </select>
                </div>
                <div style="margin-bottom:12px">
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px">Observaciones adicionales (Opcional)</label>
                    <textarea name="observaciones" rows="2" style="width:100%; background:#0f172a; border:1px solid #334155; border-radius:8px; padding:10px; color:white; font-size:14px" placeholder="Escribe algún detalle..."></textarea>
                </div>
                <div id="div_fecha_promesa" style="margin-bottom:16px; display:none;">
                    <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px">📅 Fecha de Promesa de Pago</label>
                    <input type="date" name="fecha_promesa" id="input_fecha_promesa" style="width:100%; background:#0f172a; border:1px solid #334155; border-radius:8px; padding:10px; color:white; font-size:14px">
                </div>
                <button type="submit" style="width:100%; background:var(--danger); color:white; border:none; border-radius:10px; padding:12px; font-weight:700; font-size:14px">Registrar y Ocultar por Hoy</button>
            </form>
        </div>
    </div>

    <div id="btn-instalar-pwa" style="display:none; position:fixed; bottom:90px; left:16px; right:16px; z-index:200; background:linear-gradient(135deg,#1e293b,#0f172a); border:1px solid #3b82f6; border-radius:16px; padding:14px 18px; box-shadow:0 8px 30px rgba(59,130,246,0.3); display:none; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:44px; height:44px; background:var(--bg-card); border-radius:12px; display:flex; align-items:center; justify-content:center; border:1px solid #334155;">
                <i class="bi bi-cash-coin" style="color:var(--primary); font-size:22px;"></i>
            </div>
            <div>
                <div style="font-size:13px; font-weight:700; color:var(--text-main);">Instalar Tecnicell</div>
                <div style="font-size:11px; color:var(--text-muted);">Agrega a tu pantalla de inicio</div>
            </div>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <button onclick="cerrarInstalacionPWA()" style="background:none; border:none; color:var(--text-muted); font-size:18px; padding:4px;"><i class="bi bi-x"></i></button>
            <button id="btn-instalar-action" onclick="instalarPWA()" style="background:var(--primary); color:white; border:none; border-radius:10px; padding:8px 16px; font-size:12px; font-weight:700; white-space:nowrap;">
                <i class="bi bi-download"></i> Instalar
            </button>
        </div>
    </div>

    <script>
        let deferredPrompt = null;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            document.getElementById('btn-instalar-pwa').style.display = 'flex';
        });

        function instalarPWA() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('Usuario instaló la PWA');
                }
                deferredPrompt = null;
                document.getElementById('btn-instalar-pwa').style.display = 'none';
            });
        }

        function cerrarInstalacionPWA() {
            document.getElementById('btn-instalar-pwa').style.display = 'none';
        }

        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {}

        function actualizarBannerConectado() {
            const bannerOffline = document.getElementById('banner-offline');
            const bannerOnline = document.getElementById('banner-online');
            if (navigator.onLine) {
                bannerOffline.style.display = 'none';
                bannerOnline.style.display = 'flex';
                setTimeout(() => { bannerOnline.style.display = 'none'; }, 4000);
            } else {
                bannerOffline.style.display = 'flex';
                bannerOnline.style.display = 'none';
            }
        }
        window.addEventListener('online', actualizarBannerConectado);
        window.addEventListener('offline', actualizarBannerConectado);
        if (!navigator.onLine) {
            document.getElementById('banner-offline').style.display = 'flex';
        }

        function abrirModalGasto() {
            document.getElementById('modalGasto').style.display = 'flex';
            const btn = document.querySelector('#modalGasto button[type="submit"]');
            btn.disabled = false;
            btn.innerHTML = 'Guardar Gasto';
            btn.style.opacity = '1';
        }
        function cerrarModalGasto() {
            document.getElementById('modalGasto').style.display = 'none';
        }
        function abrirModalNoPago(prestamoId) {
            document.getElementById('no_pago_prestamo_id').value = prestamoId;
            document.getElementById('modalNoPago').style.display = 'flex';
            const btn = document.querySelector('#modalNoPago button[type="submit"]');
            btn.disabled = false;
            btn.innerHTML = 'Registrar y Ocultar por Hoy';
            btn.style.opacity = '1';
            document.getElementById('div_fecha_promesa').style.display = 'none';
            document.getElementById('input_fecha_promesa').value = '';
            document.getElementById('input_fecha_promesa').required = false;
        }
        function cerrarModalNoPago() {
            document.getElementById('modalNoPago').style.display = 'none';
        }

        let ubicacionNoPago = { lat: null, lng: null };

        function capturarUbicacionNoPago() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    ubicacionNoPago.lat = pos.coords.latitude;
                    ubicacionNoPago.lng = pos.coords.longitude;
                    const form = document.getElementById('formNoPago');
                    let inpLat = document.querySelector('#formNoPago input[name="latitud"]');
                    let inpLng = document.querySelector('#formNoPago input[name="longitud"]');
                    if (!inpLat) {
                        inpLat = document.createElement('input');
                        inpLat.type = 'hidden';
                        inpLat.name = 'latitud';
                        form.appendChild(inpLat);
                    }
                    if (!inpLng) {
                        inpLng = document.createElement('input');
                        inpLng.type = 'hidden';
                        inpLng.name = 'longitud';
                        form.appendChild(inpLng);
                    }
                    inpLat.value = ubicacionNoPago.lat;
                    inpLng.value = ubicacionNoPago.lng;
                },
                function(err) { console.warn('GPS no disponible:', err.message); },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        const originalAbrirModal = abrirModalNoPago;
        abrirModalNoPago = function(prestamoId) {
            originalAbrirModal(prestamoId);
            capturarUbicacionNoPago();
        };

        document.querySelector('#modalNoPago select[name="motivo"]').addEventListener('change', function() {
            const divPromesa = document.getElementById('div_fecha_promesa');
            const inputPromesa = document.getElementById('input_fecha_promesa');
            if (this.value === 'promesa_pago') {
                divPromesa.style.display = 'block';
                inputPromesa.required = true;
                inputPromesa.value = new Date(Date.now() + 86400000).toISOString().split('T')[0];
            } else {
                divPromesa.style.display = 'none';
                inputPromesa.required = false;
                inputPromesa.value = '';
            }
        });

        // Manejo offline
        document.getElementById('formGasto').addEventListener('submit', function(e) {
            if (navigator.onLine) return true;
            e.preventDefault();
            const concepto = this.querySelector('input[name="concepto"]').value;
            const monto = this.querySelector('input[name="monto"]').value;
            const metodo = this.querySelector('select[name="metodo"]').value;
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-cloud-arrow-down"></i> GUARDANDO OFFLINE...';
            btn.style.opacity = '0.7';
            if (typeof guardarOffline === 'function') {
                guardarOffline('gasto', { concepto, monto, metodo }).then(() => {
                    cerrarModalGasto();
                    if (typeof mostrarNotificacionOffline === 'function') mostrarNotificacionOffline('gasto');
                }).catch(() => {
                    alert('Error al guardar offline.');
                    btn.disabled = false;
                    btn.innerHTML = 'Guardar Gasto';
                    btn.style.opacity = '1';
                });
            } else {
                alert('Sistema offline no disponible.');
                btn.disabled = false;
                btn.innerHTML = 'Guardar Gasto';
                btn.style.opacity = '1';
            }
        });

        document.getElementById('formNoPago').addEventListener('submit', function(e) {
            if (navigator.onLine) return true;
            e.preventDefault();
            const prestamo_id = document.getElementById('no_pago_prestamo_id').value;
            const motivo = this.querySelector('select[name="motivo"]').value;
            const observaciones = this.querySelector('textarea[name="observaciones"]').value || '';
            const fecha_promesa = document.getElementById('input_fecha_promesa').value || '';
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-cloud-arrow-down"></i> GUARDANDO OFFLINE...';
            btn.style.opacity = '0.7';
            if (typeof guardarOffline === 'function') {
                const datos = { prestamo_id, motivo, observaciones };
                if (fecha_promesa) datos.fecha_promesa = fecha_promesa;
                guardarOffline('visita', datos).then(() => {
                    cerrarModalNoPago();
                    if (typeof mostrarNotificacionOffline === 'function') mostrarNotificacionOffline('visita');
                    setTimeout(() => window.location.reload(), 1500);
                }).catch(() => {
                    alert('Error al guardar offline.');
                    btn.disabled = false;
                    btn.innerHTML = 'Registrar y Ocultar por Hoy';
                    btn.style.opacity = '1';
                });
            } else {
                alert('Sistema offline no disponible.');
                btn.disabled = false;
                btn.innerHTML = 'Registrar y Ocultar por Hoy';
                btn.style.opacity = '1';
            }
        });

        // Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js').then(reg => {
                    console.log('Service Worker registrado', reg);
                    reg.addEventListener('updatefound', () => {
                        const newWorker = reg.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                if (confirm('¡Nueva versión disponible! ¿Recargar para actualizar?')) {
                                    window.location.reload();
                                }
                            }
                        });
                    });
                }).catch(err => console.warn('Error SW:', err));
            });
        }
    </script>
</body>
</html>