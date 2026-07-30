<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div style="min-height:100vh;display:grid;place-items:center;padding:24px;background:var(--content-bg)">
    <div class="login-card" style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:34px;box-shadow:var(--shadow-md);max-width:420px;width:100%">
        <div class="brand-mobile" style="display:flex;flex-direction:column;align-items:center;margin-bottom:18px">
            <div style="background:linear-gradient(135deg,#6d28d9,#2563eb);width:56px;height:56px;border-radius:14px;display:grid;place-items:center;font-size:26px;color:#fff;margin-bottom:12px">
                <i class="bi bi-key-fill"></i>
            </div>
            <strong style="font-size:18px">Recuperar acceso</strong>
        </div>

        <h2 style="font-size:22px;font-weight:800;margin-bottom:4px">¿Olvidaste tu contraseña?</h2>
        <p class="muted" style="margin-bottom:22px">Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>

        @if (session('ok'))
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> {{ session('ok') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom:18px">
                <label>Correo electrónico</label>
                <div class="input-icon">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required autofocus>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px"><i class="bi bi-send"></i> Enviar enlace</button>
        </form>

        <div style="text-align:center;margin-top:20px;font-size:13px">
            <a href="{{ route('login') }}" style="color:var(--primary);font-weight:600"><i class="bi bi-arrow-left"></i> Volver a iniciar sesión</a>
        </div>
    </div>
</div>
</body>
</html>
