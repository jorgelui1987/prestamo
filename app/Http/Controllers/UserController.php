<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\User;
use App\Support\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public const ROLES = [
        'superadmin' => 'Super Administrador',
        'admin' => 'Administrador',
        'gerente' => 'Gerente',
        'operador' => 'Operador',
        'cobrador' => 'Cobrador',
    ];

    private function soloAdmin(): void
    {
        abort_unless(auth()->user() && auth()->user()->esAdmin(), 403, 'Acceso restringido a administradores.');
    }

    /** Roles que el usuario actual puede asignar (solo un superadmin asigna superadmin). */
    private function rolesAsignables(): array
    {
        $roles = self::ROLES;
        if (! auth()->user()->esSuperAdmin()) {
            unset($roles['superadmin']);
        }

        return $roles;
    }

    /** Bloquea la asignación del rol superadmin a quien no es superadmin. */
    private function protegerRolSuperadmin(string $rol): void
    {
        abort_if($rol === 'superadmin' && ! auth()->user()->esSuperAdmin(), 403, 'Solo un super administrador puede asignar ese rol.');
    }

    public function index(Request $request)
    {
        $this->soloAdmin();
        $buscar = $request->query('q');
        $user = auth()->user();

        $usuarios = User::query()
            ->when($user->esSuperAdmin(), function ($q) {
                // El Super Admin solo gestiona otros Super Admins y los Administradores principales de las empresas
                $q->whereIn('rol', ['superadmin', 'admin']);
            })
            ->when(!$user->esSuperAdmin(), function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id)
                  ->where('rol', '!=', 'superadmin');
            })
            ->when($buscar, function ($q) use ($buscar) {
                $q->where(function ($sub) use ($buscar) {
                    $sub->where('name', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios', 'buscar'));
    }

    public function create()
    {
        $this->soloAdmin();

        return view('usuarios.form', ['usuario' => new User(['rol' => 'operador', 'activo' => true]), 'roles' => $this->rolesAsignables()]);
    }

    public function store(Request $request)
    {
        $this->soloAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'rol' => ['required', Rule::in(array_keys(self::ROLES))],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'activo' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=150,min_height=150'],
        ], [
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.max' => 'La imagen no debe superar los 2 MB.',
            'avatar.dimensions' => 'La imagen debe tener al menos 150x150 píxeles.',
        ]);

        $this->protegerRolSuperadmin($data['rol']);

        unset($data['avatar']);
        $data['activo'] = $request->boolean('activo');
        $data['password'] = Hash::make($data['password']);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = AvatarService::store($request->file('avatar'));
        }

        // Asignar automáticamente el tenant_id de la empresa actual si no es superadmin
        if (!auth()->user()->esSuperAdmin()) {
            $data['tenant_id'] = auth()->user()->tenant_id;
        }

        User::create($data);

        return redirect()->route('usuarios.index')->with('ok', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $this->soloAdmin();
        abort_if($usuario->esSuperAdmin() && ! auth()->user()->esSuperAdmin(), 403, 'No puedes editar a un super administrador.');

        return view('usuarios.form', ['usuario' => $usuario, 'roles' => $this->rolesAsignables()]);
    }

    public function update(Request $request, User $usuario)
    {
        $this->soloAdmin();
        abort_if($usuario->esSuperAdmin() && ! auth()->user()->esSuperAdmin(), 403, 'No puedes editar a un super administrador.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($usuario->id)],
            'rol' => ['required', Rule::in(array_keys(self::ROLES))],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', Password::min(6)],
            'activo' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=150,min_height=150'],
        ], [
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.max' => 'La imagen no debe superar los 2 MB.',
            'avatar.dimensions' => 'La imagen debe tener al menos 150x150 píxeles.',
        ]);

        $this->protegerRolSuperadmin($data['rol']);

        unset($data['avatar']);
        $data['activo'] = $request->boolean('activo');
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($usuario->avatar) {
                Storage::disk('public')->delete($usuario->avatar);
            }
            $data['avatar'] = AvatarService::store($request->file('avatar'));
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('ok', 'Usuario actualizado.');
    }

    public function toggleActivo(User $usuario)
    {
        $this->soloAdmin();
        abort_if($usuario->esSuperAdmin() && ! auth()->user()->esSuperAdmin(), 403, 'No puedes desactivar a un super administrador.');

        if ($usuario->id === auth()->id()) {
            return back()->with('ok', 'No puedes desactivarte a ti mismo.');
        }

        $usuario->update([
            'activo' => ! $usuario->activo,
        ]);

        $accion = $usuario->activo ? 'activado' : 'desactivado';
        $nombre = $usuario->name;

        Auditoria::registrar(
            $usuario->activo ? 'activar usuario' : 'desactivar usuario',
            'Usuarios',
            $usuario->id,
            "El usuario {$nombre} fue {$accion}."
        );

        return back()->with('ok', "Usuario '{$nombre}' {$accion} correctamente.");
    }

    public function destroy(User $usuario)
    {
        $this->soloAdmin();

        if ($usuario->id === auth()->id()) {
            return back()->with('ok', 'No puedes eliminar tu propio usuario.');
        }

        abort_if($usuario->esSuperAdmin() && ! auth()->user()->esSuperAdmin(), 403, 'No puedes eliminar a un super administrador.');

        if ($usuario->avatar) {
            Storage::disk('public')->delete($usuario->avatar);
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('ok', 'Usuario eliminado.');
    }
}
