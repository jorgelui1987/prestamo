<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin_seguridad', 100)->nullable()->after('password');
            $table->tinyInteger('pin_intentos_fallidos')->default(0)->after('pin_seguridad');
            $table->timestamp('pin_bloqueado_hasta')->nullable()->after('pin_intentos_fallidos');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pin_seguridad', 'pin_intentos_fallidos', 'pin_bloqueado_hasta']);
        });
    }
};