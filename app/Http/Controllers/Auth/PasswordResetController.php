<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    /** Formulario para solicitar el enlace de restablecimiento. */
    public function showLinkRequest()
    {
        return view('auth.forgot-password');
    }

    /** Envía el enlace de restablecimiento al correo. */
    public function sendLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('ok', 'Te enviamos un enlace para restablecer tu contraseña.')
            : back()->withErrors(['email' => __($status)]);
    }

    /** Formulario para definir la nueva contraseña. */
    public function showReset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    /** Guarda la nueva contraseña. */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(6)],
        ], [
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('ok', 'Tu contraseña fue restablecida. Ya puedes iniciar sesión.')
            : back()->withErrors(['email' => __($status)]);
    }
}
