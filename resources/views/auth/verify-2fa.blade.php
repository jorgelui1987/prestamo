<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación en dos pasos | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .verify-2fa-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); padding: 20px; }
        .verify-2fa-card { background: #fff; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.08); padding: 40px; max-width: 420px; width: 100%; text-align: center; }
        .verify-2fa-card .icon { width: 64px; height: 64px; background: linear-gradient(135deg, #6d28d9, #2563eb); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 28px; color: #fff; }
        .verify-2fa-card h2 { margin: 0 0 6px; font-size: 20px; font-weight: 700; color: #1e293b; }
        .verify-2fa-card p { color: #64748b; font-size: 14px; margin: 0 0 24px; line-height: 1.5; }
        .code-inputs { display: flex; gap: 10px; justify-content: center; margin-bottom: 24px; }
        .code-inputs input { width: 52px; height: 60px; text-align: center; font-size: 28px; font-weight: 700; border: 2px solid #e2e8f0; border-radius: 12px; outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
        .code-inputs input:focus { border-color: #6d28d9; box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.15); }
        .code-inputs input.error { border-color: #ef4444; }
        .btn-full { width: 100%; padding: 14px; font-size: 15px; font-weight: 600; border: none; border-radius: 12px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .links { margin-top: 20px; display: flex; flex-direction: column; gap: 10px; font-size: 13px; }
        .links a { color: #6d28d9; text-decoration: none; font-weight: 600; }
        .links a:hover { text-decoration: underline; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: 10px 14px; font-size: 13px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>
    <div class="verify-2fa-page">
        <div class="verify-2fa-card">
            <div class="icon"><i class="bi bi-shield-lock-fill"></i></div>
            <h2>Verificación en dos pasos</h2>
            <p>Ingresa el código de 6 dígitos que enviamos a <strong>{{ $emailEnmascarado }}</strong></p>

            @if ($errors->any())
                <div class="alert-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif

            <form action="{{ route('2fa.verify') }}" method="POST" id="verifyForm">
                @csrf
                <div class="code-inputs">
                    <input type="text" name="code" id="codeInput" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" placeholder="------" class="@error('code') error @enderror" autofocus>
                </div>
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="bi bi-check-lg"></i> Verificar identidad
                </button>
            </form>

            <div class="links">
                <form action="{{ route('2fa.resend') }}" method="POST" id="resendForm">
                    @csrf
                    <a href="#" onclick="event.preventDefault(); document.getElementById('resendForm').submit();">
                        <i class="bi bi-arrow-clockwise"></i> Reenviar código
                    </a>
                </form>
                <a href="{{ route('2fa.recovery.form') }}">
                    <i class="bi bi-key"></i> Usar código de respaldo
                </a>
                <a href="{{ route('login') }}" style="color:#94a3b8;">
                    <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('codeInput').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    </script>
</body>
</html>