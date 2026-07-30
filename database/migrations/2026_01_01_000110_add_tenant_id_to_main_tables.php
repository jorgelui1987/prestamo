<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'clientes',
            'prestamos',
            'pagos',
            'empenos',
            'movimientos_caja',
            'cortes_caja',
            'auditorias'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'clientes',
            'prestamos',
            'pagos',
            'empenos',
            'movimientos_caja',
            'cortes_caja',
            'auditorias'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->dropForeign(['tenant_id']);
                $tableBlueprint->dropColumn('tenant_id');
            });
        }
    }
};
