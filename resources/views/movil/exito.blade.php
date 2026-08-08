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
    <title>¡Cobro Exitoso!</title>
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
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }
        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }
        .success-card {
            background-color: var(--bg-card);
            border: 1px solid #334155;
            border-radius: 24px;
            padding: 32px 24px;
            text-align: center;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .success-icon {
            font-size: 64px;
            color: var(--success);
            margin-bottom: 16px;
            display: block;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); }
            80% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .title {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }
        .receipt-details {
            background-color: #0f172a;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
            font-size: 13px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            border: 1px solid #1e293b;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
        }
        .detail-lbl { color: var(--text-muted); }
        .detail-val { font-weight: 700; }
        .btn-wa {
            width: 100%;
            background-color: #25d366;
            color: white;
            border: none;
            border-radius: 14px;
            padding: 16px;
            font-size: 15px;
            font-weight: 800;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.2);
        }
        .btn-wa:active {
            background-color: #128c7e;
        }
        .btn-back {
            width: 100%;
            background-color: #334155;
            color: var(--text-main);
            border: none;
            border-radius: 14px;
            padding: 16px;
            font-size: 15px;
            font-weight: 700;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
        }
        .btn-back:active {
            background-color: #475569;
        }
    </style>
</head>
<body>

    <div class="success-card">
        <i class="bi bi-check-circle-fill success-icon"></i>
        <h1 class="title">¡Cobro Registrado!</h1>
        <p class="subtitle">El pago se ha aplicado correctamente a las cuotas pendientes.</p>

        <div class="receipt-details">
            <div class="detail-row">
                <span class="detail-lbl">Cliente:</span>
                <span class="detail-val">{{ $prestamo->cliente->nombre_completo }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-lbl">Monto Cobrado:</span>
                <span class="detail-val" style="color: var(--success)">S/ {{ number_format($monto, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-lbl">Saldo Restante:</span>
                <span class="detail-val">S/ {{ number_format($saldo, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-lbl">Préstamo:</span>
                <span class="detail-val">{{ $prestamo->codigo }}</span>
            </div>
        </div>

        @php
            $mensaje = "TECNICELL - RECIBO DE PAGO DIGITAL 🧾\n\n"
                     . "Hola " . $prestamo->cliente->nombres . ", confirmamos el recibo de tu pago de hoy:\n"
                     . "💵 Monto: S/ " . number_format($monto, 2) . "\n"
                     . "📅 Fecha: " . now()->format('d/m/Y') . "\n"
                     . "🔢 Préstamo: " . $prestamo->codigo . "\n"
                     . "💰 Saldo Restante: S/ " . number_format($saldo, 2) . "\n\n"
                     . "¡Muchas gracias por tu puntualidad! 👍";
        @endphp

        @if ($prestamo->cliente->telefono)
            <a href="https://wa.me/51{{ $prestamo->cliente->telefono }}?text={{ urlencode($mensaje) }}" target="_blank" class="btn-wa">
                <i class="bi bi-whatsapp"></i> ENVIAR RECIBO POR WHATSAPP
            </a>
        @endif

        <a href="{{ route('movil.index') }}" class="btn-back">
            VOLVER A LA RUTA
        </a>
    </div>

    <script>
        // Registrar Service Worker para PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(reg => console.log('Service Worker registrado con éxito', reg))
                    .catch(err => console.warn('Error al registrar el Service Worker', err));
            });
        }

        // Proteger botón WhatsApp contra doble clic
        document.querySelectorAll('.btn-wa').forEach(btn => {
            btn.addEventListener('click', function(e) {
                this.style.opacity = '0.6';
                this.style.pointerEvents = 'none';
            });
        });
    </script>
</body>
</html>
