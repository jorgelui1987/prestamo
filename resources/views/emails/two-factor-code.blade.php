<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de verificación</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 0; }
        .container { max-width: 480px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #6d28d9, #2563eb); padding: 30px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 700; }
        .body { padding: 30px; }
        .code { text-align: center; font-size: 42px; font-weight: 800; letter-spacing: 8px; color: #1e293b; background: #f8fafc; border-radius: 12px; padding: 20px; margin: 20px 0; border: 2px dashed #e2e8f0; }
        .info { color: #64748b; font-size: 14px; line-height: 1.6; text-align: center; }
        .footer { text-align: center; padding: 20px; color: #94a3b8; font-size: 12px; border-top: 1px solid #e2e8f0; }
        .warning { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; font-size: 13px; color: #991b1b; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Verificación en dos pasos</h1>
        </div>
        <div class="body">
            <p class="info">Hola <strong>{{ $user->name }}</strong>,</p>
            <p class="info">Ingresa el siguiente código para completar tu inicio de sesión en <strong>{{ config('app.name') }}</strong>:</p>

            <div class="code">{{ $code }}</div>

            <p class="info">Este código expira a las <strong>{{ $expiresAt }}</strong> (10 minutos).</p>

            <div class="warning">
                <strong>⚠️ No compartas este código</strong><br>
                Si no solicitaste este código, ignora este mensaje y cambia tu contraseña inmediatamente.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>