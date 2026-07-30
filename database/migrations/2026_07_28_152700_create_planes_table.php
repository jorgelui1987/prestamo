<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('planes')) {
            Schema::create('planes', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->text('descripcion')->nullable();
                $table->decimal('precio', 10, 2)->default(0.00);
                $table->integer('limite_usuarios')->default(5);
                $table->integer('limite_clientes')->default(50);
                $table->integer('limite_prestamos')->default(100);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};