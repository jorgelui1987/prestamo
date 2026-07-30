<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->dropUnique('configuraciones_clave_unique');
            $table->unique(['clave', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->dropUnique(['clave', 'tenant_id']);
            $table->unique('clave');
        });
    }
};
