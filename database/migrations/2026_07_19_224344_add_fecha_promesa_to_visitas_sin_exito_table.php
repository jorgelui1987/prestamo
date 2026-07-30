<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitas_sin_exito', function (Blueprint $table) {
            $table->date('fecha_promesa')->nullable()->after('observaciones');
            $table->boolean('promesa_cumplida')->default(false)->after('fecha_promesa');
        });
    }

    public function down(): void
    {
        Schema::table('visitas_sin_exito', function (Blueprint $table) {
            $table->dropColumn(['fecha_promesa', 'promesa_cumplida']);
        });
    }
};