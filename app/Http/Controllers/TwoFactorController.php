<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\TwoFactorCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TwoFactorController extends Controller
{
    /**
     * Enviar código 2FA al email del usuario (para el login).
     */
    public function sendCode(Request $request)
    {
        $userId = $request->session()->get('2fa:user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['email' => 'Sesión expirada. Inicia sesión nuevamente.']);
        }

        $user = User::find($userId);
        if (!$user || !$user->two_factor_enabled) {
            return redirect()->route('login');
        }

        // Generar código de 6 dígitos
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);

        // Eliminar códigos anteriores del usuario
        TwoFactorCode::where('user_id', $user->id)->delete();

        // Guardar nuevo código
        TwoFactorCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        // Enviar email
        try {
            Mail::send('emails.two-factor-code', [
                'user' => $user,
                'code' => $code,
                'expiresAt' => $expiresAt->format('H:i'),
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('🔐 Código de verificación - ' . config('app.name'));
            });
        } catch (\Exception $e) {
            // Si falla el email, mostrar error
            return back()->withErrors(['code' => 'No se pudo enviar el código al correo. Verifica la configuración SMTP.']);
        }

        $request->session()->put('2fa:send_at', now()->timestamp);

        return redirect()->route('2fa.verify.form');
    }

    /**
     * Mostrar formulario de verificación 2FA.
     */
    public function showVerifyForm(Request $request)
    {
        $userId = $request->session()->get('2fa:user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $emailEnmascarado = '*';
        $user = User::find($userId);
        if ($user) {
            $partes = explode('@', $user->email);
            $emailEnmascarado = substr($partes[0], 0, 2) . str_repeat('*', max(2, strlen($partes[0]) - 2)) . '@' . $partes[1];
        }

        return view('auth.verify-2fa', [
            'emailEnmascarado' => $emailEnmascarado,
        ]);
    }

    /**
     * Verificar el código 2FA ingresado.
     */
    public function verify(Request $request)
    {
        $userId = $request->session()->get('2fa:user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::find($userId);
        if (!$user || !$user->two_factor_enabled) {
            return redirect()->route('login');
        }

        // Buscar código válido
        $twoFactorCode = TwoFactorCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$twoFactorCode) {
            return back()->withErrors(['code' => 'Código inválido o expirado. Solicita uno nuevo.']);
        }

        // Eliminar código usado
        $twoFactorCode->delete();

        // Completar autenticación
        Auth::login($user);
        $request->session()->forget(['2fa:user_id', '2fa:send_at']);
        $request->session()->regenerate();

        Auditoria::registrar('inicio sesion 2FA', 'Autenticacion', null, 'El usuario inicio sesion con verificación 2FA.');

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Reenviar código 2FA.
     */
    public function resend(Request $request)
    {
        $userId = $request->session()->get('2fa:user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        // Evitar reenvío en menos de 30 segundos
        $sendAt = $request->session()->get('2fa:send_at', 0);
        if (now()->timestamp - $sendAt < 30) {
            return back()->withErrors(['code' => 'Espera al menos 30 segundos para solicitar un nuevo código.']);
        }

        return $this->sendCode($request);
    }

    /**
     * Mostrar formulario de códigos de respaldo.
     */
    public function showRecoveryForm(Request $request)
    {
        $userId = $request->session()->get('2fa:user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        return view('auth.2fa-recovery');
    }

    /**
     * Validar código de respaldo.
     */
    public function verifyRecovery(Request $request)
    {
        $userId = $request->session()->get('2fa:user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'recovery_code' => ['required', 'string'],
        ]);

        $user = User::find($userId);
        if (!$user || !$user->two_factor_enabled || !$user->two_factor_backup_codes) {
            return redirect()->route('login');
        }

        $backupCodes = json_decode($user->two_factor_backup_codes, true) ?? [];

        // Buscar el código en los códigos de respaldo
        $codeIndex = array_search($request->recovery_code, $backupCodes);

        if ($codeIndex === false) {
            return back()->withErrors(['recovery_code' => 'Código de respaldo inválido.']);
        }

        // Eliminar el código usado
        unset($backupCodes[$codeIndex]);
        $user->two_factor_backup_codes = json_encode(array_values($backupCodes));
        $user->save();

        // Completar autenticación
        Auth::login($user);
        $request->session()->forget(['2fa:user_id', '2fa:send_at']);
        $request->session()->regenerate();

        Auditoria::registrar('inicio sesion 2FA (respaldo)', 'Autenticacion', null, 'El usuario inicio sesion con código de respaldo 2FA.');

        return redirect()->route('perfil.show')->with('warning', 'Usaste un código de respaldo. Genera nuevos códigos desde tu perfil.');
    }

    /**
     * Activar 2FA (desde el perfil).
     */
    public function enable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->two_factor_enabled) {
            return back()->withErrors(['two_factor' => 'La verificación 2FA ya está activa.']);
        }

        // Generar 10 códigos de respaldo
        $backupCodes = [];
        for ($i = 0; $i < 10; $i++) {
            $backupCodes[] = strtoupper(Str::random(10));
        }

        $user->two_factor_enabled = true;
        $user->two_factor_backup_codes = json_encode($backupCodes);
        $user->save();

        Auditoria::registrar('activar 2FA', 'Seguridad', $user->id, 'El usuario activó la verificación en dos pasos.');

        return back()->with([
            '2fa_backup_codes' => $backupCodes,
            'ok' => '✅ Verificación en dos pasos activada. Guarda tus códigos de respaldo en un lugar seguro.',
        ]);
    }

    /**
     * Desactivar 2FA (desde el perfil).
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if (!$user->two_factor_enabled) {
            return back()->withErrors(['two_factor' => 'La verificación 2FA no está activa.']);
        }

        $user->two_factor_enabled = false;
        $user->two_factor_backup_codes = null;
        $user->save();

        // Limpiar códigos pendientes
        TwoFactorCode::where('user_id', $user->id)->delete();

        Auditoria::registrar('desactivar 2FA', 'Seguridad', $user->id, 'El usuario desactivó la verificación en dos pasos.');

        return back()->with('ok', '✅ Verificación en dos pasos desactivada correctamente.');
    }

    /**
     * Regenerar códigos de respaldo.
     */
    public function regenerateBackupCodes(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        if (!$user->two_factor_enabled) {
            return back()->withErrors(['two_factor' => 'Primero activa la verificación 2FA.']);
        }

        // Generar nuevos códigos
        $backupCodes = [];
        for ($i = 0; $i < 10; $i++) {
            $backupCodes[] = strtoupper(Str::random(10));
        }

        $user->two_factor_backup_codes = json_encode($backupCodes);
        $user->save();

        Auditoria::registrar('regenerar códigos 2FA', 'Seguridad', $user->id, 'El usuario regeneró sus códigos de respaldo 2FA.');

        return back()->with([
            '2fa_backup_codes' => $backupCodes,
            'ok' => '🔄 Códigos de respaldo regenerados. Guarda los nuevos en un lugar seguro.',
        ]);
    }
}