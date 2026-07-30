<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — Plataforma #1 de préstamos y cobranzas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/img/icons/icon.svg">
    <meta name="theme-color" content="#0f172a">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    {{-- Critical inline CSS for instant render --}}
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body.landing-page{font-family:'Inter',sans-serif;background:#0f172a;color:#fff;overflow-x:hidden}
        .lp-nav{max-width:1180px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:20px 24px}
        .lp-brand{display:flex;align-items:center;gap:12px;font-weight:800;font-size:20px;letter-spacing:-.5px}
        .lp-brand .logo{width:42px;height:42px;border-radius:12px;background:#2563eb;display:grid;place-items:center;font-size:22px;color:#fff;box-shadow:0 6px 20px rgba(37,99,235,.45)}
        .lp-navlinks{display:flex;align-items:center;gap:32px}
        .lp-navlinks a{color:#cbd5e1;font-weight:500;font-size:14px;text-decoration:none;transition:color .2s}
        .lp-navlinks a:hover{color:#fff}
        .lp-btn{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border-radius:12px;font-weight:600;font-size:14px;text-decoration:none;transition:all .2s;cursor:pointer}
        .lp-btn-primary{background:#2563eb;color:#fff;box-shadow:0 6px 20px rgba(37,99,235,.4)}
        .lp-btn-primary:hover{background:#1d4ed8;transform:translateY(-2px)}
        .lp-btn-ghost{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2)}
        .lp-btn-ghost:hover{background:rgba(255,255,255,.2)}
        .lp-btn-light{background:#fff;color:#0f172a;box-shadow:0 4px 16px rgba(37,99,235,.2)}
        .lp-btn-light:hover{background:#f1f5f9;transform:translateY(-2px)}
        .lp-hero{max-width:880px;margin:0 auto;text-align:center;padding:60px 24px 90px}
        .lp-hero h1{font-size:56px;line-height:1.05;font-weight:900;margin-bottom:22px;letter-spacing:-1.5px}
        .lp-hero h1 .grad{background:linear-gradient(120deg,#fff 0%,#60a5fa 40%,#38bdf8 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
        .lp-hero p{font-size:18px;color:#cbd5e1;max-width:620px;margin:0 auto 34px;line-height:1.7}
        .lp-stats{display:flex;justify-content:center;gap:48px;flex-wrap:wrap}
        .lp-stat{text-align:center}
        .lp-stat .num{font-size:34px;font-weight:800;color:#fff}
        .lp-stat .lbl{font-size:13px;color:#94a3b8;margin-top:2px}
        .lp-section{max-width:1180px;margin:0 auto;padding:80px 24px}
        .lp-section .tag{text-align:center;color:#60a5fa;font-weight:700;font-size:13px;letter-spacing:2px;text-transform:uppercase}
        .lp-section h2{text-align:center;font-size:34px;font-weight:800;margin:12px 0 14px;color:#fff;letter-spacing:-.5px}
        .lp-section .sub{text-align:center;color:#cbd5e1;max-width:560px;margin:0 auto 48px;font-size:16px;line-height:1.6}
    </style>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="landing-page">

    {{-- ==================== NAVBAR ==================== --}}
    <nav class="lp-nav">
        <div class="lp-brand">
            <span class="logo"><i class="bi bi-cash-coin"></i></span>
            Préstamos Pro
        </div>
        <div class="lp-navlinks">
            <a href="#funciones">Funciones</a>
            <a href="#precios">Precios</a>
            <a href="https://wa.me/56982209690" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px"><i class="bi bi-whatsapp" style="color:#25D366"></i> Contacto</a>
            <a href="{{ route('login') }}">Iniciar sesión</a>
            <a href="{{ route('register') }}" class="lp-btn lp-btn-primary"><i class="bi bi-rocket-takeoff"></i> Prueba gratis</a>
        </div>
    </nav>

    {{-- ==================== HERO ==================== --}}
    <section class="lp-hero">
        <span style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,rgba(59,130,246,.2),rgba(37,99,235,.2));border:1px solid rgba(59,130,246,.3);color:#93c5fd;font-size:13px;font-weight:600;padding:8px 18px;border-radius:30px;margin-bottom:28px">
            🚀 Plataforma #1 de gestión de préstamos y cobranzas
        </span>
        <h1>Gestiona tu cartera<br><span class="grad">de forma inteligente</span></h1>
        <p>Todo lo que necesitas para administrar clientes, préstamos, cuotas, cobranzas, mora y empeños. Sin complicaciones, desde cualquier dispositivo.</p>
        <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin-bottom:60px">
            <a href="{{ route('register') }}" class="lp-btn lp-btn-primary" style="padding:15px 28px;font-size:15px"><i class="bi bi-rocket-takeoff"></i> Comenzar gratis — 14 días</a>
            <a href="#precios" class="lp-btn lp-btn-ghost" style="padding:15px 28px;font-size:15px"><i class="bi bi-tag"></i> Ver precios</a>
        </div>
        <div class="lp-stats">
            <div class="lp-stat"><div class="num">500+</div><div class="lbl">Financieras activas</div></div>
            <div class="lp-stat"><div class="num">50k+</div><div class="lbl">Préstamos gestionados</div></div>
            <div class="lp-stat"><div class="num">99.9%</div><div class="lbl">Uptime garantizado</div></div>
            <div class="lp-stat"><div class="num">14 días</div><div class="lbl">Prueba gratuita</div></div>
        </div>
    </section>

    {{-- ==================== FUNCIONES ==================== --}}
    <section class="lp-section" id="funciones">
        <div class="tag">Todo en un solo lugar</div>
        <h2>Funciones que impulsan tu negocio</h2>
        <p class="sub">Una plataforma completa para controlar cada etapa del ciclo de crédito y cobranza.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px">
            @php
                $features = [
                    ['bg-blue', 'bi-people-fill', 'Gestión de clientes', 'Registra y consulta el historial crediticio completo de cada cliente en segundos.'],
                    ['bg-teal', 'bi-cash-stack', 'Préstamos y cuotas', 'Cálculo automático de intereses, cronogramas de pago y saldos siempre actualizados.'],
                    ['bg-orange', 'bi-exclamation-triangle-fill', 'Control de mora', 'Alertas de vencimientos y seguimiento de cartera vencida para no perder ningún pago.'],
                    ['bg-purple', 'bi-gem', 'Empeños', 'Administra garantías y empeños con estados, vencimientos y valuación integrada.'],
                    ['bg-cyan', 'bi-cash-coin', 'Caja y arqueo', 'Movimientos de caja, corte diario y conciliación en tiempo real.'],
                    ['bg-red', 'bi-file-earmark-spreadsheet', 'Reportes avanzados', 'Métricas del negocio y exportación a Excel para decisiones basadas en datos.'],
                ];
            @endphp
            @foreach($features as [$color, $icon, $title, $desc])
                <div style="background:linear-gradient(145deg,#1e293b,#0f172a);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:32px;transition:transform .25s,background .25s,border-color .25s,box-shadow .25s;position:relative;overflow:hidden"
                     onmouseover="this.style.transform='translateY(-5px)';this.style.background='linear-gradient(145deg,#334155,#1e293b)';this.style.borderColor='rgba(59,130,246,.4)';this.style.boxShadow='0 20px 60px rgba(59,130,246,.2)'"
                     onmouseout="this.style.transform='';this.style.background='linear-gradient(145deg,#1e293b,#0f172a)';this.style.borderColor='rgba(255,255,255,.12)';this.style.boxShadow=''">
                    <div class="{{ $color }}" style="width:54px;height:54px;border-radius:14px;display:grid;place-items:center;font-size:24px;color:#fff;margin-bottom:18px">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                    <h3 style="font-size:18px;font-weight:700;margin-bottom:10px;color:#fff">{{ $title }}</h3>
                    <p style="color:#cbd5e1;font-size:14px;line-height:1.6">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ==================== PRECIOS ==================== --}}
    <section class="lp-section" id="precios">
        <div class="tag">Precios simples</div>
        <h2>Empieza hoy, escala cuando quieras</h2>
        <p class="sub">Prueba todas las funciones durante 14 días gratis. Sin tarjeta de crédito.</p>

        @if (isset($planes) && $planes->isNotEmpty())
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));gap:24px;max-width:1100px;margin:40px auto;padding:0 20px">
                @foreach ($planes as $plan)
                    <div style="background:linear-gradient(145deg,#1e293b,#0f172a);border:1px solid rgba(255,255,255,.12);border-radius:24px;padding:40px 32px 32px;text-align:center;display:flex;flex-direction:column;justify-content:space-between;transition:transform .3s,background .3s,border-color .3s,box-shadow .3s;position:relative;overflow:hidden"
                     onmouseover="this.style.transform='translateY(-8px)';this.style.background='linear-gradient(145deg,#334155,#1e293b)';this.style.borderColor='rgba(59,130,246,.4)';this.style.boxShadow='0 24px 64px rgba(59,130,246,.2)'"
                     onmouseout="this.style.transform='';this.style.background='linear-gradient(145deg,#1e293b,#0f172a)';this.style.borderColor='rgba(255,255,255,.12)';this.style.boxShadow=''">
                        <div>
                            <h3 style="font-size:24px;font-weight:800;color:#fff;margin-bottom:10px">{{ $plan->nombre }}</h3>
                            <p style="font-size:14px;color:#cbd5e1;margin-bottom:28px;min-height:42px;line-height:1.6">{{ $plan->descripcion ?: 'Plan ideal para tu negocio.' }}</p>
                            <div style="font-size:44px;font-weight:800;background:linear-gradient(135deg,#fff 0%,#93c5fd 55%,#60a5fa 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:4px;line-height:1">
                                S/ {{ number_format($plan->precio, 2) }} <span style="font-size:14px;font-weight:400;color:#94a3b8;-webkit-text-fill-color:#94a3b8">/ mes</span>
                            </div>
                            <ul style="list-style:none;padding:0;margin:28px 0 36px;text-align:left;display:flex;flex-direction:column;gap:14px">
                                <li style="display:flex;align-items:center;gap:10px;font-size:14px;color:#cbd5e1"><i class="bi bi-check-circle-fill" style="background:linear-gradient(135deg,#10b981,#34d399);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;font-size:18px"></i> Hasta <strong>{{ $plan->limite_usuarios }}</strong> usuarios</li>
                                <li style="display:flex;align-items:center;gap:10px;font-size:14px;color:#cbd5e1"><i class="bi bi-check-circle-fill" style="background:linear-gradient(135deg,#10b981,#34d399);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;font-size:18px"></i> Hasta <strong>{{ $plan->limite_clientes }}</strong> clientes</li>
                                <li style="display:flex;align-items:center;gap:10px;font-size:14px;color:#cbd5e1"><i class="bi bi-check-circle-fill" style="background:linear-gradient(135deg,#10b981,#34d399);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;font-size:18px"></i> Hasta <strong>{{ $plan->limite_prestamos }}</strong> préstamos</li>
                                <li style="display:flex;align-items:center;gap:10px;font-size:14px;color:#cbd5e1"><i class="bi bi-check-circle-fill" style="background:linear-gradient(135deg,#10b981,#34d399);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;font-size:18px"></i> Soporte técnico incluido</li>
                            </ul>
                        </div>
                        <a href="{{ route('register', ['plan' => $plan->id]) }}" class="lp-btn lp-btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:15px"><i class="bi bi-rocket-takeoff"></i> Probar gratis</a>
                    </div>
                @endforeach
            </div>
        @else
            <p style="text-align:center;color:#94a3b8;padding:60px 20px;font-size:16px">Próximamente se publicarán los planes de suscripción.</p>
        @endif

        {{-- CTA --}}
        <div style="background:linear-gradient(145deg,rgba(59,130,246,.12),rgba(37,99,235,.08));border:1px solid rgba(59,130,246,.2);border-radius:24px;text-align:center;padding:64px 40px;max-width:1080px;margin:80px auto 0;position:relative;overflow:hidden">
            <h2 style="font-size:34px;font-weight:800;margin-bottom:14px;color:#fff;letter-spacing:-.5px">Lleva el control total de tu cartera</h2>
            <p style="color:#cbd5e1;max-width:520px;margin:0 auto 30px;font-size:16px;line-height:1.6">Únete a cientos de financieras que ya gestionan sus préstamos y cobranzas con Préstamos Pro.</p>
            <a href="{{ route('login') }}" class="lp-btn lp-btn-light" style="padding:14px 28px;font-size:15px"><i class="bi bi-box-arrow-in-right"></i> Acceder al sistema</a>
        </div>
    </section>

    {{-- ==================== FOOTER ==================== --}}
    <footer style="border-top:1px solid rgba(255,255,255,.06);color:#94a3b8;text-align:center;padding:36px 24px;font-size:13px">
        <div style="display:flex;align-items:center;justify-content:center;gap:12px;font-weight:800;color:#e2e8f0;margin-bottom:12px;font-size:16px">
            <span style="width:32px;height:32px;border-radius:10px;background:linear-gradient(135deg,#3b82f6,#2563eb);display:grid;place-items:center;font-size:16px;color:#fff;box-shadow:0 4px 12px rgba(37,99,235,.35)"><i class="bi bi-cash-coin"></i></span>
            Préstamos Pro
        </div>
        <div style="display:flex;justify-content:center;gap:24px;margin-bottom:10px;flex-wrap:wrap">
            <a href="https://wa.me/56982209690" target="_blank" rel="noopener" style="color:#25D366;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-size:13px"><i class="bi bi-whatsapp"></i> +56 9 8220 9690</a>
            <a href="mailto:luitechserena@gmail.com" style="color:#60a5fa;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-size:13px"><i class="bi bi-envelope-fill"></i> luitechserena@gmail.com</a>
        </div>
        © {{ date('Y') }} {{ config('app.name') }} · Todos los derechos reservados
    </footer>

    {{-- PWA Install --}}
    <div id="pwa-banner" style="position:fixed;bottom:20px;left:16px;right:16px;z-index:1000;background:linear-gradient(135deg,#1e293b,#0f172a);border:1px solid #3b82f6;border-radius:16px;padding:14px 18px;box-shadow:0 8px 30px rgba(59,130,246,.3);display:none;align-items:center;justify-content:space-between;max-width:500px;margin:0 auto">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:44px;height:44px;background:#1e293b;border-radius:12px;display:flex;align-items:center;justify-content:center;border:1px solid #334155"><i class="bi bi-cash-coin" style="color:#3b82f6;font-size:22px"></i></div>
            <div><div style="font-size:13px;font-weight:700;color:#f8fafc">Instala {{ config('app.name') }}</div><div style="font-size:11px;color:#94a3b8">Accede rápido desde tu pantalla de inicio</div></div>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <button onclick="document.getElementById('pwa-banner').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:18px;cursor:pointer"><i class="bi bi-x"></i></button>
            <button id="btn-instalar-pwa" onclick="instalarPWA()" style="background:#3b82f6;color:white;border:none;border-radius:10px;padding:8px 16px;font-size:12px;font-weight:700;cursor:pointer"><i class="bi bi-download"></i> Instalar</button>
        </div>
    </div>

    <script>
        let deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; document.getElementById('pwa-banner').style.display = 'flex'; });
        function instalarPWA() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(() => { deferredPrompt = null; document.getElementById('pwa-banner').style.display = 'none'; });
        }
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {}
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js').catch(() => {});
            });
        }
    </script>
</body>
</html>