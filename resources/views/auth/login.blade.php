<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@php
    $logoGlobal = \App\Models\Configuracion::get('empresa_logo');
    $nombreGlobal = \App\Models\Configuracion::get('empresa_nombre', 'Préstamos Pro');
@endphp
<div class="login-page">
    {{-- ===== Panel izquierdo (marca + features) ===== --}}
    <div class="login-hero centered">
        <a href="{{ route('home') }}" class="login-brand-big">
            @if ($logoGlobal)
                <div class="logo" style="background: none; border: none; width: 64px; height: 64px; overflow: hidden; border-radius: 12px; margin-bottom: 14px; display: grid; place-items: center;">
                    <img src="{{ \App\Support\StorageHelper::url($logoGlobal) }}" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
            @else
                <div class="logo"><i class="bi bi-cash-coin"></i></div>
            @endif
            <div class="title">{{ $nombreGlobal }}</div>
            <div class="subtitle">Sistema de Gestión Premium</div>
        </a>

        <div class="login-feat-list">
            <div class="login-feat">
                <div class="ic"><i class="bi bi-people-fill"></i></div>
                <div><div class="t">Gestión de Clientes</div><div class="d">Control total de cartera y créditos</div></div>
            </div>
            <div class="login-feat">
                <div class="ic"><i class="bi bi-cash-stack"></i></div>
                <div><div class="t">Préstamos y Cuotas</div><div class="d">Cálculo automático de intereses</div></div>
            </div>
            <div class="login-feat">
                <div class="ic"><i class="bi bi-graph-up-arrow"></i></div>
                <div><div class="t">Reportes Avanzados</div><div class="d">Métricas y caja en tiempo real</div></div>
            </div>
            <div class="login-feat">
                <div class="ic"><i class="bi bi-phone"></i></div>
                <div><div class="t">Acceso Multiplataforma</div><div class="d">Disponible en cualquier dispositivo</div></div>
            </div>
        </div>

        <div class="login-mini-stats">
            <div class="box"><div class="num">500+</div><div class="lbl">Financieras</div></div>
            <div class="box"><div class="num">98%</div><div class="lbl">Satisfacción</div></div>
            <div class="box"><div class="num">24/7</div><div class="lbl">Soporte</div></div>
        </div>
    </div>

    {{-- ===== Panel derecho (formulario) ===== --}}
    <div class="login-form-side">
        <div class="login-card">
            <div class="brand-mobile">
                @if ($logoGlobal)
                    <div class="logo" style="background: none; border: none; width: 56px; height: 56px; overflow: hidden; border-radius: 14px; margin-bottom: 14px; display: grid; place-items: center;">
                        <img src="{{ \App\Support\StorageHelper::url($logoGlobal) }}" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                @else
                    <div class="login-hero logo" style="background:linear-gradient(135deg,#6d28d9,#2563eb);width:56px;height:56px;border-radius:14px;display:grid;place-items:center;font-size:26px;color:#fff;margin-bottom:14px">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                @endif
                <strong>{{ $nombreGlobal }}</strong>
            </div>

            <h2>Bienvenido de vuelta 👋</h2>
            <p class="muted">Ingresa tus credenciales para acceder al sistema.</p>

            @if ($errors->any())
                <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Correo electrónico</label>
                    <div class="input-icon">
                        <i class="bi bi-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <div class="input-icon">
                        <i class="bi bi-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="check-row">
                    <label><input type="checkbox" name="remember"> Recordarme</label>
                    <a href="{{ route('password.request') }}" style="color:var(--primary);font-weight:600">¿Olvidaste tu contraseña?</a>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión</button>
            </form>

            <div class="login-foot">
                ¿Quieres usar el sistema para tu negocio? <a href="{{ route('register') }}" style="color:var(--primary);font-weight:600">Regístrate aquí</a>
            </div>
            <div class="login-foot" style="margin-top:10px">
                © {{ date('Y') }} {{ config('app.name') }} · Todos los derechos reservados
            </div>
        </div>
    </div>
</div>

</body>
</html>
