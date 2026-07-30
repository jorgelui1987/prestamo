<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'activo')) {
                $table->boolean('activo')->default(true)->after('estado');
            }
        });

        // Sincronizar campo activo con estado actual
        \Illuminate\Support\Facades\DB::table('tenants')
            ->whereIn('estado', ['activo', 'prueba'])
            ->update(['activo' => true]);

        \Illuminate\Support\Facades\DB::table('tenants')
            ->where('estado', 'suspendido')
            ->update(['activo' => false]);
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};