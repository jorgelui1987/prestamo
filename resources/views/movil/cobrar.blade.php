<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <title>Registrar Cobro Express</title>
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
            padding-bottom: 40px;
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
        .info-card {
            background-color: var(--bg-card);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid #334155;
        }
        .info-title {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .info-value {
            font-size: 18px;
            font-weight: 800;
            color: var(--primary);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        .form-control {
            width: 100%;
            background-color: var(--bg-card);
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 14px 16px;
            color: var(--text-main);
            font-size: 16px;
            font-weight: 700;
            outline: none;
            text-align: center;
        }
        .form-control:focus {
            border-color: var(--primary);
        }
        .quick-amounts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 8px;
        }
        .quick-btn {
            background-color: #334155;
            color: var(--text-main);
            border: 1px solid #475569;
            border-radius: 10px;
            padding: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .quick-btn:active {
            background-color: #475569;
        }
        .methods-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }
        .method-card {
            background-color: var(--bg-card);
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 12px 8px;
            text-align: center;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .method-card i {
            font-size: 20px;
            color: var(--text-muted);
        }
        .method-card.active {
            border-color: var(--primary);
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--primary);
        }
        .method-card.active i {
            color: var(--primary);
        }
        .btn-submit {
            width: 100%;
            background-color: var(--success);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 16px;
            font-size: 16px;
            font-weight: 800;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
        .btn-submit:active {
            background-color: #059669;
        }
    </style>
</head>
<body>

    <header class="app-header">
        <a href="{{ route('movil.index') }}" class="back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="app-title">Registrar Cobro Express</div>
    </header>

    <div class="container">
        <div class="info-card">
            <div class="info-title">Cliente</div>
            <div class="info-value" style="color: var(--text-main); font-size: 16px; margin-bottom: 12px;">
                {{ $prestamo->cliente->nombre_completo }}
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--text-muted);">
                <span>Saldo Total: {{ \App\Models\Configuracion::get('moneda', 'S/') }} {{ number_format($saldo, 2) }}</span>
                <span>Préstamo: {{ $prestamo->codigo }}</span>
            </div>
        </div>

        <form action="{{ route('pagos.store', $prestamo) }}" method="POST" id="formCobro">
            @csrf
            <input type="hidden" name="metodo" id="metodo_pago" value="efectivo">

            {{-- Fecha de Pago --}}
            <div class="form-group">
                <label class="form-label">Fecha de Pago *</label>
                <input type="date" name="fecha_pago" class="form-control" value="{{ now()->format('Y-m-d') }}" required style="text-align: center;">
            </div>

            {{-- Monto a Cobrar --}}
            <div class="form-group">
                <label class="form-label">Monto a Cobrar ({{ \App\Models\Configuracion::get('moneda', 'S/') }}) *</label>
                <input type="number" step="0.01" min="0.01" max="{{ $saldo }}" name="monto" id="monto_cobro" class="form-control" value="{{ $totalSugerido }}" required>
                
                <div class="quick-amounts">
                    <button type="button" class="quick-btn" onclick="setMonto({{ $totalSugerido }})">
                        Cuota + Mora ({{ \App\Models\Configuracion::get('moneda', 'S/') }} {{ number_format($totalSugerido, 2) }})
                    </button>
                    <button type="button" class="quick-btn" onclick="setMonto({{ $saldo }})">
                        Saldo Total ({{ \App\Models\Configuracion::get('moneda', 'S/') }} {{ number_format($saldo, 2) }})
                    </button>
                </div>
            </div>

            {{-- Método de Pago --}}
            <div class="form-group">
                <label class="form-label">Método de Pago *</label>
                <div class="methods-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="method-card active" onclick="setMetodo('efectivo', this)">
                        <i class="bi bi-cash-coin"></i>
                        <span>Efectivo</span>
                    </div>
                    <div class="method-card" onclick="setMetodo('transferencia', this)">
                        <i class="bi bi-bank" style="color: #3b82f6;"></i>
                        <span>Transferencia</span>
                    </div>
                </div>
            </div>

            {{-- Campo de Transferencia Dinámico --}}
            <div class="form-group" id="div_referencia" style="display: none;">
                <label class="form-label">Número de Transferencia *</label>
                <input type="text" name="referencia" id="input_referencia" class="form-control" style="text-align: left; font-weight: normal; font-size: 14px;" placeholder="Escribe el número de operación...">
            </div>

            {{-- Botón de Confirmar --}}
            <button type="submit" class="btn-submit">
                <i class="bi bi-check-circle-fill"></i> CONFIRMAR COBRO
            </button>
        </form>
    </div>

    <script>
        // ============================================================
        // GEOLOCALIZACIÓN - Capturar ubicación al cobrar
        // ============================================================
        let ubicacionCapturada = { lat: null, lng: null };

        function capturarUbicacion() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    ubicacionCapturada.lat = pos.coords.latitude;
                    ubicacionCapturada.lng = pos.coords.longitude;
                    const form = document.getElementById('formCobro');
                    let inpLat = document.querySelector('input[name="latitud"]');
                    let inpLng = document.querySelector('input[name="longitud"]');
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
                    inpLat.value = ubicacionCapturada.lat;
                    inpLng.value = ubicacionCapturada.lng;
                },
                function(err) {
                    console.warn('No se pudo obtener ubicación:', err.message);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        capturarUbicacion();

        // ============================================================
        // FUNCIONES EXISTENTES DEL FORMULARIO
        // ============================================================
        function setMonto(val) {
            document.getElementById('monto_cobro').value = val.toFixed(2);
        }

        function setMetodo(metodo, element) {
            document.getElementById('metodo_pago').value = metodo;
            
            document.querySelectorAll('.method-card').forEach(card => {
                card.classList.remove('active');
            });
            
            element.classList.add('active');

            const divRef = document.getElementById('div_referencia');
            const inputRef = document.getElementById('input_referencia');
            
            if (metodo === 'transferencia') {
                divRef.style.display = 'block';
                inputRef.required = true;
                inputRef.focus();
            } else {
                divRef.style.display = 'none';
                inputRef.required = false;
                inputRef.value = '';
            }
        }

        // Prevenir doble envío Y manejo offline (se ejecuta DESPUÉS de la verificación de PIN)
        document.getElementById('formCobro').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            if (btn.disabled) {
                e.preventDefault();
                return;
            }

            if (!navigator.onLine) {
                e.preventDefault();
                const monto = document.getElementById('monto_cobro').value;
                const metodo = document.getElementById('metodo_pago').value;
                const referencia = document.getElementById('input_referencia')?.value || '';
                const fechaPago = document.querySelector('input[name="fecha_pago"]').value;
                const prestamoId = {{ $prestamo->id }};

                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-cloud-arrow-down"></i> GUARDANDO OFFLINE...';
                btn.style.opacity = '0.7';

                if (typeof guardarOffline === 'function') {
                    guardarOffline('cobro', {
                        prestamo_id: prestamoId,
                        monto: monto,
                        metodo: metodo,
                        referencia: referencia,
                        fecha_pago: fechaPago
                    }).then(() => {
                        window.location.href = '{{ route("movil.exito", ["prestamo" => $prestamo->id, "monto" => $totalSugerido]) }}?offline=1';
                    }).catch((error) => {
                        alert('Error al guardar offline. Intenta de nuevo.');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> CONFIRMAR COBRO';
                        btn.style.opacity = '1';
                    });
                } else {
                    alert('No hay conexión y el sistema offline no está disponible. Usa el respaldo en Google Sheets.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> CONFIRMAR COBRO';
                    btn.style.opacity = '1';
                }
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> PROCESANDO...';
            btn.style.opacity = '0.7';
        });

        if (!navigator.onLine) {
            const container = document.querySelector('.container');
            const warning = document.createElement('div');
            warning.style.cssText = 'background:rgba(239,68,68,0.15);border:1px solid #ef4444;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#ef4444;display:flex;align-items:center;gap:8px;';
            warning.innerHTML = '<i class="bi bi-wifi-off"></i> <strong>Sin conexión</strong> — El cobro se guardará en tu celular y se sincronizará automáticamente cuando tengas señal.';
            container.insertBefore(warning, container.firstChild);
        }
    </script>
</body>
</html>