<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (! $user->activo) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Tu cuenta esta desactivada. Contacta al administrador.',
                ]);
            }

            // Si el usuario tiene 2FA activo, NO iniciar sesión aún
            if ($user->two_factor_enabled) {
                Auth::logout();
                $request->session()->put('2fa:user_id', $user->id);
                $request->session()->put('2fa:send_at', now()->timestamp);

                // Enviar el código automáticamente
                $controller = app(\App\Http\Controllers\TwoFactorController::class);
                return $controller->sendCode($request);
            }

            $request->session()->regenerate();
            Auditoria::registrar('inicio sesion', 'Autenticacion', null, 'El usuario inicio sesion.');

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auditoria::registrar('cierre sesion', 'Autenticacion', null, 'El usuario cerro sesion.');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
