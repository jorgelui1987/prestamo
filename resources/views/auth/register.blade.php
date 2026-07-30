<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar mi Empresa | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="login-page">
    {{-- ===== Panel izquierdo (marca + features) ===== --}}
    <div class="login-hero centered">
        <a href="{{ route('home') }}" class="login-brand-big">
            <div class="logo"><i class="bi bi-cash-coin"></i></div>
            <div class="title">Préstamos Pro</div>
            <div class="subtitle">Plataforma SaaS de Gestión Financiera</div>
        </a>

        <div class="login-feat-list">
            <div class="login-feat">
                <div class="ic"><i class="bi bi-building"></i></div>
                <div><div class="t">Tu Propia Empresa</div><div class="d">Espacio de trabajo 100% aislado y seguro</div></div>
            </div>
            <div class="login-feat">
                <div class="ic"><i class="bi bi-shield-check"></i></div>
                <div><div class="t">Control de Accesos</div><div class="d">Crea usuarios para tus cobradores y operadores</div></div>
            </div>
            <div class="login-feat">
                <div class="ic"><i class="bi bi-gift"></i></div>
                <div><div class="t">Prueba Gratuita</div><div class="d">Prueba todas las funciones gratis por 14 días</div></div>
            </div>
        </div>

        <div class="login-mini-stats">
            <div class="box"><div class="num">100%</div><div class="lbl">Seguro</div></div>
            <div class="box"><div class="num">14 Días</div><div class="lbl">Prueba Gratis</div></div>
            <div class="box"><div class="num">SaaS</div><div class="lbl">Multi-tenant</div></div>
        </div>
    </div>

    {{-- ===== Panel derecho (formulario) ===== --}}
    <div class="login-form-side" style="padding: 40px 20px; overflow-y: auto;">
        <div class="login-card" style="max-width: 520px;">
            <div class="brand-mobile">
                <div class="login-hero logo" style="background:linear-gradient(135deg,#6d28d9,#2563eb);width:56px;height:56px;border-radius:14px;display:grid;place-items:center;font-size:26px;color:#fff;margin-bottom:14px">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <strong>Préstamos Pro</strong>
            </div>

            <h2>Registra tu Empresa 🚀</h2>
            <p class="muted">Crea tu cuenta y comienza a gestionar tus préstamos hoy mismo.</p>

            @if ($errors->any())
                <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf
                
                <h3 style="font-size: 16px; margin: 20px 0 10px 0; color: var(--primary); border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;">
                    <i class="bi bi-building"></i> Datos de la Empresa
                </h3>

                <div class="form-group">
                    <label>Nombre de la Empresa *</label>
                    <div class="input-icon">
                        <i class="bi bi-briefcase"></i>
                        <input type="text" name="empresa_nombre" class="form-control" value="{{ old('empresa_nombre') }}" placeholder="Ej. Inversiones Castro" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Selecciona un Plan *</label>
                    <div class="input-icon">
                        <i class="bi bi-tags"></i>
                        <select name="plan_id" class="form-control" required style="padding-left: 38px;">
                            <option value="">Elige un plan de suscripción</option>
                            @foreach ($planes as $p)
                                <option value="{{ $p->id }}" @selected(old('plan_id', $selectedPlanId ?? null) == $p->id)>
                                    {{ $p->nombre }} - S/ {{ number_format($p->precio, 2) }}/mes (Prueba gratis)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <h3 style="font-size: 16px; margin: 25px 0 10px 0; color: var(--primary); border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;">
                    <i class="bi bi-person-badge"></i> Cuenta del Administrador
                </h3>

                <div class="form-group">
                    <label>Tu Nombre Completo *</label>
                    <div class="input-icon">
                        <i class="bi bi-person"></i>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Ej. Juan Pérez" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Correo electrónico *</label>
                    <div class="input-icon">
                        <i class="bi bi-envelope"></i>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required>
                    </div>
                </div>

                <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Contraseña *</label>
                        <div class="input-icon">
                            <i class="bi bi-lock"></i>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirmar Contraseña *</label>
                        <div class="input-icon">
                            <i class="bi bi-lock-fill"></i>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 15px; width: 100%;">
                    <i class="bi bi-check-circle-fill"></i> Registrarse e Iniciar Prueba Gratis
                </button>
            </form>

            <div class="login-foot" style="margin-top: 25px;">
                ¿Ya tienes una cuenta? <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 600;">Inicia sesión aquí</a>
            </div>
            <div class="login-foot" style="margin-top:10px">
                © {{ date('Y') }} {{ config('app.name') }} · Todos los derechos reservados
            </div>
        </div>
    </div>
</div>
</body>
</html>
