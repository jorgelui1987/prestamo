<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->foreignId('cobrador_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->integer('orden_ruta')->default(0)->after('cobrador_id');
        });
    }

    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropForeign(['cobrador_id']);
            $table->dropColumn(['cobrador_id', 'orden_ruta']);
        });
    }
};
