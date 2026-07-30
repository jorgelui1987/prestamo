<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    private function soloSuperAdmin(): void
    {
        abort_unless(auth()->user() && auth()->user()->esSuperAdmin(), 403, 'Acceso exclusivo del super administrador.');
    }

    public function index()
    {
        $this->soloSuperAdmin();

        $tenants = Tenant::with(['plan', 'users' => function ($q) {
            $q->where('rol', 'admin');
        }])->latest()->paginate(10);
        $planes = Plan::all();

        return view('superadmin.tenants', compact('tenants', 'planes'));
    }

    public function storeTenant(Request $request)
    {
        $this->soloSuperAdmin();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'plan_id' => ['required', 'exists:planes,id'],
            'estado' => ['required', 'in:prueba,activo,suspendido'],
            'fecha_vencimiento' => ['nullable', 'date'],
        ]);

        $data['slug'] = Str::slug($data['nombre']);

        // Asegurar slug único
        $count = Tenant::where('slug', 'like', $data['slug'] . '%')->count();
        if ($count > 0) {
            $data['slug'] .= '-' . ($count + 1);
        }

        Tenant::create($data);

        return back()->with('ok', 'Empresa (Tenant) registrada correctamente.');
    }

    public function updateTenant(Request $request, Tenant $tenant)
    {
        $this->soloSuperAdmin();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'plan_id' => ['required', 'exists:planes,id'],
            'estado' => ['required', 'in:prueba,activo,suspendido'],
            'fecha_vencimiento' => ['nullable', 'date'],
        ]);

        $tenant->update($data);

        return back()->with('ok', 'Empresa (Tenant) actualizada correctamente.');
    }

    public function storePlan(Request $request)
    {
        $this->soloSuperAdmin();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'limite_usuarios' => ['required', 'integer', 'min:1'],
            'limite_clientes' => ['required', 'integer', 'min:1'],
            'limite_prestamos' => ['required', 'integer', 'min:1'],
        ]);

        Plan::create($data);

        return back()->with('ok', 'Plan de suscripción creado correctamente.');
    }

    public function updatePlan(Request $request, Plan $plan)
    {
        $this->soloSuperAdmin();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'limite_usuarios' => ['required', 'integer', 'min:1'],
            'limite_clientes' => ['required', 'integer', 'min:1'],
            'limite_prestamos' => ['required', 'integer', 'min:1'],
        ]);

        $plan->update($data);

        return back()->with('ok', 'Plan de suscripción actualizado.');
    }

    public function resetAdminPassword(Request $request, Tenant $tenant)
    {
        $this->soloSuperAdmin();

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $adminUser = User::where('tenant_id', $tenant->id)->where('rol', 'admin')->first();

        if (!$adminUser) {
            return back()->withErrors(['error' => 'No se encontró un usuario administrador para esta empresa.']);
        }

        $adminUser->update([
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
        ]);

        return back()->with('ok', 'Contraseña del administrador de ' . $tenant->nombre . ' restablecida correctamente.');
    }

    public function resetTenantData(Request $request, Tenant $tenant)
    {
        $this->soloSuperAdmin();

        $request->validate([
            'confirmacion' => ['required', 'string', 'in:RESET'],
        ], [
            'confirmacion.in' => 'Debes escribir la palabra RESET para confirmar la acción.',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($tenant) {
            // Eliminar datos operativos asociados al tenant_id
            \App\Models\Pago::where('tenant_id', $tenant->id)->delete();
            
            $prestamoIds = \App\Models\Prestamo::where('tenant_id', $tenant->id)->pluck('id');
            \App\Models\Cuota::whereIn('prestamo_id', $prestamoIds)->delete();
            \App\Models\Prestamo::where('tenant_id', $tenant->id)->delete();

            \App\Models\Cliente::where('tenant_id', $tenant->id)->delete();
            \App\Models\Empeno::where('tenant_id', $tenant->id)->delete();
            \App\Models\MovimientoCaja::where('tenant_id', $tenant->id)->delete();
            \App\Models\CorteCaja::where('tenant_id', $tenant->id)->delete();
            \App\Models\Auditoria::where('tenant_id', $tenant->id)->delete();
        });

        return back()->with('ok', 'Todos los datos operativos de la empresa ' . $tenant->nombre . ' han sido reseteados correctamente.');
    }

    public function destroyTenant(Request $request, Tenant $tenant)
    {
        $this->soloSuperAdmin();

        $request->validate([
            'confirmacion' => ['required', 'string', 'in:ELIMINAR'],
        ], [
            'confirmacion.in' => 'Debes escribir la palabra ELIMINAR para confirmar.',
        ]);

        $nombre = $tenant->nombre;

        \Illuminate\Support\Facades\DB::transaction(function () use ($tenant) {
            // Eliminar TODOS los datos del tenant en orden (de hijos a padres)
            $prestamoIds = \App\Models\Prestamo::where('tenant_id', $tenant->id)->pluck('id');
            \App\Models\Cuota::whereIn('prestamo_id', $prestamoIds)->delete();
            \App\Models\Pago::where('tenant_id', $tenant->id)->delete();
            \App\Models\Prestamo::where('tenant_id', $tenant->id)->delete();
            \App\Models\Cliente::where('tenant_id', $tenant->id)->delete();
            \App\Models\Empeno::where('tenant_id', $tenant->id)->delete();
            \App\Models\MovimientoCaja::where('tenant_id', $tenant->id)->delete();
            \App\Models\CorteCaja::where('tenant_id', $tenant->id)->delete();
            \App\Models\Auditoria::where('tenant_id', $tenant->id)->delete();
            \App\Models\VisitaSinExito::where('tenant_id', $tenant->id)->delete();
            \App\Models\Configuracion::where('tenant_id', $tenant->id)->delete();

            // Eliminar usuarios del tenant (incluyendo el admin)
            \App\Models\User::where('tenant_id', $tenant->id)->delete();

            // Finalmente eliminar el tenant
            $tenant->delete();
        });

        return redirect()->route('superadmin.tenants.index')
            ->with('ok', "Empresa '{$nombre}' eliminada completamente del sistema junto con todos sus datos.");
    }

    public function toggleActivo(Tenant $tenant)
    {
        $this->soloSuperAdmin();

        $tenant->update([
            'activo' => !$tenant->activo,
            'estado' => $tenant->activo ? 'activo' : 'suspendido',
        ]);

        $accion = $tenant->activo ? 'activada' : 'desactivada';
        $nombre = $tenant->nombre;

        Auditoria::registrar(
            $tenant->activo ? 'activar empresa' : 'desactivar empresa',
            'Tenants',
            $tenant->id,
            "La empresa {$nombre} fue {$accion}."
        );

        return back()->with('ok', "Empresa '{$nombre}' {$accion} correctamente.");
    }

    public function createAdminUser(Request $request, Tenant $tenant)
    {
        $this->soloSuperAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'rol' => 'admin',
            'activo' => true,
        ]);

        return back()->with('ok', 'Usuario administrador creado con éxito para la empresa ' . $tenant->nombre . '.');
    }
}
