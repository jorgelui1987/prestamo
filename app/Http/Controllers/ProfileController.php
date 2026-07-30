<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\Empeno;
use App\Models\Prestamo;
use App\Models\User;
use App\Support\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        $actividad = Auditoria::where('user_id', $user->id)
            ->latest('id')
            ->take(10)
            ->get();

        $resumen = null;
        if ($user->esAdmin()) {
            $isSuper = $user->esSuperAdmin();
            $resumen = [
                'usuarios' => User::query()->when(!$isSuper, fn($q) => $q->where('rol', '!=', 'superadmin'))->count(),
                'usuarios_activos' => User::query()->when(!$isSuper, fn($q) => $q->where('rol', '!=', 'superadmin'))->where('activo', true)->count(),
                'clientes' => Cliente::count(),
                'prestamos_activos' => Prestamo::whereIn('estado', ['activo', 'mora'])->count(),
                'empenos_vigentes' => Empeno::where('estado', 'vigente')->count(),
                'acciones_hoy' => Auditoria::whereDate('created_at', now()->toDateString())->count(),
            ];
        }

        return view('perfil.show', compact('user', 'actividad', 'resumen'));
    }

    public function updatePerfil(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->id)],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($data);

        return redirect()->route('perfil.show')->with('ok', 'Datos de perfil actualizados correctamente.');
    }

    public function updateFoto(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=150,min_height=150'],
        ], [
            'avatar.required' => 'Selecciona una imagen.',
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.max' => 'La imagen no debe superar los 2 MB.',
            'avatar.dimensions' => 'La imagen debe tener al menos 150x150 píxeles.',
        ]);

        $user = auth()->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = AvatarService::store($request->file('avatar'));
        $user->update(['avatar' => $path]);

        return redirect()->route('perfil.show')->with('ok', 'Foto de perfil actualizada.');
    }

    public function deleteFoto()
    {
        $user = auth()->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return redirect()->route('perfil.show')->with('ok', 'Foto de perfil eliminada.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'current_password.required' => 'Debes ingresar tu contraseña actual.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('perfil.show')->with('ok', 'Contraseña actualizada correctamente.');
    }

    public function updateLogoPlataforma(Request $request)
    {
        abort_unless(auth()->user()->esSuperAdmin(), 403);

        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'logo.required' => 'Selecciona una imagen para el logo.',
            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.max' => 'La imagen no debe superar los 2 MB.',
        ]);

        // Obtener el logo actual para eliminarlo si existe
        $logoActual = \App\Models\Configuracion::get('empresa_logo');
        if ($logoActual && str_starts_with($logoActual, 'logos/')) {
            $rutaAnterior = public_path('img/' . $logoActual);
            if (file_exists($rutaAnterior)) {
                @unlink($rutaAnterior);
            }
        }

        // Guardar el nuevo logo en la carpeta pública (sobrevive a deploys)
        $file = $request->file('logo');
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('img/logos'), $filename);
        $path = 'logos/' . $filename;
        \App\Models\Configuracion::set('empresa_logo', $path, 'general');

        return redirect()->route('perfil.show')->with('ok', 'Logo global de la plataforma actualizado correctamente.');
    }
}
