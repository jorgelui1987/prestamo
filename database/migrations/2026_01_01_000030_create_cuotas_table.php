<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestamo_id')->constrained('prestamos')->cascadeOnDelete();
            $table->integer('numero');
            $table->date('fecha_vencimiento');
            $table->decimal('monto', 12, 2);
            $table->decimal('capital', 12, 2)->default(0);
            $table->decimal('interes', 12, 2)->default(0);
            $table->decimal('mora', 12, 2)->default(0);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->date('fecha_pago')->nullable();
            $table->string('estado')->default('pendiente'); // pendiente / pagado / vencido / parcial
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};
