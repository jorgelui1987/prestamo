<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Cuota;
use App\Models\Empeno;
use App\Models\Pago;
use App\Models\Prestamo;
use App\Models\User;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Planes ----
        $planBasico = Plan::updateOrCreate(
            ['nombre' => 'Plan Básico'],
            [
                'descripcion' => 'Ideal para pequeñas financieras o prestamistas independientes.',
                'precio' => 49.90,
                'limite_usuarios' => 3,
                'limite_clientes' => 50,
                'limite_prestamos' => 100,
            ]
        );

        $planPro = Plan::updateOrCreate(
            ['nombre' => 'Plan Profesional'],
            [
                'descripcion' => 'Para financieras en crecimiento con múltiples cobradores.',
                'precio' => 99.90,
                'limite_usuarios' => 10,
                'limite_clientes' => 250,
                'limite_prestamos' => 500,
            ]
        );

        $planPremium = Plan::updateOrCreate(
            ['nombre' => 'Plan Premium'],
            [
                'descripcion' => 'Acceso ilimitado para grandes financieras.',
                'precio' => 199.90,
                'limite_usuarios' => 999,
                'limite_clientes' => 9999,
                'limite_prestamos' => 9999,
            ]
        );

        // ---- Usuarios ----
        User::updateOrCreate(
            ['email' => 'camila1987chile@gmail.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('Castro16@'),
                'rol' => 'superadmin',
                'telefono' => '999000111',
                'activo' => true,
            ]
        );
    }
}
