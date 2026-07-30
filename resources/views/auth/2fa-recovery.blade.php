<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de respaldo | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .recovery-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); padding: 20px; }
        .recovery-card { background: #fff; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.08); padding: 40px; max-width: 420px; width: 100%; text-align: center; }
        .recovery-card .icon { width: 64px; height: 64px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 28px; color: #fff; }
        .recovery-card h2 { margin: 0 0 6px; font-size: 20px; font-weight: 700; color: #1e293b; }
        .recovery-card p { color: #64748b; font-size: 14px; margin: 0 0 24px; line-height: 1.5; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: 10px 14px; font-size: 13px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>
    <div class="recovery-page">
        <div class="recovery-card">
            <div class="icon"><i class="bi bi-key-fill"></i></div>
            <h2>Código de respaldo</h2>
            <p>Ingresa uno de tus códigos de respaldo para acceder a tu cuenta. Los códigos de respaldo se generan al activar la verificación en dos pasos.</p>

            @if ($errors->any())
                <div class="alert-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif

            <form action="{{ route('2fa.recovery.verify') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Código de respaldo</label>
                    <input type="text" name="recovery_code" class="form-control" placeholder="Ej: ABC123DEF4" required autofocus style="text-align:center;font-size:18px;letter-spacing:2px;text-transform:uppercase;">
                </div>
                <button type="submit" class="btn btn-primary btn-full" style="width:100%;padding:14px;font-size:15px;font-weight:600;border:none;border-radius:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <i class="bi bi-check-lg"></i> Verificar código
                </button>
            </form>

            <div class="links" style="margin-top:20px;display:flex;flex-direction:column;gap:10px;font-size:13px;">
                <a href="{{ route('2fa.verify.form') }}" style="color:#6d28d9;text-decoration:none;font-weight:600;">
                    <i class="bi bi-arrow-left"></i> Volver al código por email
                </a>
            </div>
        </div>
    </div>
</body>
</html>