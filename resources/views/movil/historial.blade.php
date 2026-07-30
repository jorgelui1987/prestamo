<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <title>Mis Cobros de Hoy</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #334155;
        }
        .back-btn {
            color: var(--text-main);
            font-size: 20px;
            text-decoration: none;
        }
        .app-title {
            font-size: 16px;
            font-weight: 700;
        }
        .container {
            padding: 16px;
        }
        .payment-card {
            background-color: var(--bg-card);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid #334155;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .payment-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .client-name {
            font-size: 14px;
            font-weight: 700;
        }
        .payment-meta {
            font-size: 11px;
            color: var(--text-muted);
        }
        .payment-amount {
            text-align: right;
        }
        .amount-val {
            font-size: 16px;
            font-weight: 800;
            color: var(--success);
        }
        .btn-anular {
            background: none;
            border: none;
            color: var(--danger);
            font-size: 12px;
            font-weight: 700;
            margin-top: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 0;
        }
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
    </style>
</head>
<body>

    <header class="app-header">
        <a href="{{ route('movil.index') }}" class="back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="app-title">Mis Cobros de Hoy</div>
    </header>

    <div class="container">
        @if (session('ok'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> {{ session('ok') }}
            </div>
        @endif

        {{-- Resumen de Caja Chica --}}
        <div style="background:linear-gradient(135deg, #1e293b, #0f172a); border:1px solid #334155; border-radius:16px; padding:16px; margin-bottom:20px; box-shadow:0 4px 12px rgba(0,0,0,0.2)">
            <h3 style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:12px"><i class="bi bi-wallet2"></i> Resumen de Caja Chica (Hoy)</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px">
                <div>
                    <div style="font-size:11px; color:var(--text-muted)">Cobrado (Efectivo)</div>
                    <div style="font-size:16px; font-weight:700; color:var(--success)">S/ {{ number_format($totalEfectivoCobrado, 2) }}</div>
                </div>
                <div>
                    <div style="font-size:11px; color:var(--text-muted)">Gastos (Efectivo)</div>
                    <div style="font-size:16px; font-weight:700; color:var(--danger)">S/ {{ number_format($totalGastosEfectivo, 2) }}</div>
                </div>
            </div>
            <div style="border-top:1px solid #334155; padding-top:10px; display:flex; justify-content:space-between; align-items:center">
                <span style="font-size:13px; font-weight:600; color:var(--text-main)">Efectivo Neto a Entregar:</span>
                <span style="font-size:18px; font-weight:800; color:var(--primary)">S/ {{ number_format($efectivoNeto, 2) }}</span>
            </div>
        </div>

        <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px;">
            Historial de cobros realizados hoy
        </p>

        @forelse ($pagos as $p)
            <div class="payment-card">
                <div class="payment-info">
                    <div class="client-name">{{ $p->prestamo->cliente->nombre_completo ?? '—' }}</div>
                    <div class="payment-meta">
                        Préstamo: {{ $p->prestamo->codigo ?? '—' }}<br>
                        Método: {{ ucfirst($p->metodo) }} @if($p->referencia) ({{ $p->referencia }}) @endif<br>
                        Hora: {{ $p->created_at->timezone('America/Lima')->format('h:i A') }}
                    </div>
                </div>
                <div class="payment-amount">
                    <div class="amount-val">S/ {{ number_format($p->monto, 2) }}</div>
                    
                    {{-- Botón para anular/corregir el pago --}}
                    <form action="{{ route('pagos.destroy', $p->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas anular este cobro para corregirlo? El cliente volverá a aparecer en tu lista de ruta.')" style="margin:0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-anular">
                            <i class="bi bi-trash3-fill"></i> Anular
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">
                Aún no has registrado ningún cobro el día de hoy.
            </div>
        @endforelse

        {{-- Sección de Visitas sin Éxito --}}
        <p style="font-size: 12px; color: var(--text-muted); margin-top: 24px; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px;">
            Visitas sin Éxito de Hoy
        </p>

        @forelse ($visitasFallidas as $v)
            <div class="payment-card" style="border-color: rgba(239, 68, 68, 0.3);">
                <div class="payment-info">
                    <div class="client-name">{{ $v->prestamo->cliente->nombre_completo ?? '—' }}</div>
                    <div class="payment-meta">
                        Préstamo: {{ $v->prestamo->codigo ?? '—' }}<br>
                        Motivo: <span style="color: var(--danger); font-weight: 600;">
                            {{ [
                                'no_estaba' => 'No se encontraba en su domicilio',
                                'no_dinero' => 'No tenía dinero hoy',
                                'promesa_pago' => 'Promesa de pago para mañana',
                                'local_cerrado' => 'Local o negocio cerrado',
                                'otro' => 'Otro motivo'
                            ][$v->motivo] ?? $v->motivo }}
                        </span><br>
                        Hora: {{ $v->created_at->timezone('America/Lima')->format('h:i A') }}
                    </div>
                </div>
                <div class="payment-amount">
                    <form action="{{ route('movil.no-pago.anular', $v->id) }}" method="POST" onsubmit="return confirm('¿Deseas anular esta visita fallida? El cliente volverá a aparecer en tu lista de ruta para que puedas cobrarle.')" style="margin:0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-anular" style="color: var(--primary);">
                            <i class="bi bi-arrow-counterclockwise"></i> Recuperar
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">
                No has registrado visitas sin éxito hoy.
            </div>
        @endforelse
    </div>

    <nav class="nav-bar">
        <a href="{{ route('movil.index') }}" class="nav-item">
            <i class="bi bi-house-door-fill"></i>
            <span>Inicio</span>
        </a>
        <a href="{{ route('movil.historial') }}" class="nav-item active">
            <i class="bi bi-clock-history"></i>
            <span>Mis Cobros</span>
        </a>
        <a href="{{ route('clientes.create') }}?origen=movil" class="nav-item">
            <i class="bi bi-person-plus-fill"></i>
            <span>+ Cliente</span>
        </a>
    </nav>

</body>
</html>
