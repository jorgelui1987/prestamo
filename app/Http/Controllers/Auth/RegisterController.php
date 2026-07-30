<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegister(Request $request)
    {
        $planes = Plan::all();
        $selectedPlanId = $request->query('plan');
        return view('auth.register', compact('planes', 'selectedPlanId'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'empresa_nombre' => ['required', 'string', 'max:150'],
            'plan_id' => ['required', 'exists:planes,id'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $tenant = DB::transaction(function () use ($request) {
            // 1. Crear la empresa (Tenant)
            $slug = Str::slug($request->empresa_nombre);
            $count = Tenant::where('slug', 'like', $slug . '%')->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }

            $tenant = Tenant::create([
                'nombre' => $request->empresa_nombre,
                'slug' => $slug,
                'plan_id' => $request->plan_id,
                'estado' => 'prueba', // Inicia en prueba gratuita
                'fecha_vencimiento' => now()->addDays(14), // 14 días de prueba
            ]);

            // 2. Crear el usuario administrador de la empresa
            User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'rol' => 'admin',
                'activo' => true,
            ]);

            return $tenant;
        });

        // Iniciar sesión automáticamente
        $user = User::where('email', $request->email)->first();
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('ok', '¡Bienvenido! Tu empresa ' . $tenant->nombre . ' ha sido registrada con éxito. Tienes 14 días de prueba gratuita.');
    }
}
