<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->decimal('latitud', 10, 7)->nullable()->after('referencia');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
        });

        Schema::table('visitas_sin_exito', function (Blueprint $table) {
            $table->decimal('latitud', 10, 7)->nullable()->after('promesa_cumplida');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud']);
        });

        Schema::table('visitas_sin_exito', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud']);
        });
    }
};