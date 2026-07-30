<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->date('fecha');
            $table->string('tipo');        // ingreso / egreso
            $table->string('categoria');   // aporte_capital, gasto_operativo, desembolso, etc.
            $table->string('concepto');
            $table->decimal('monto', 12, 2);
            $table->string('metodo')->default('efectivo');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cortes_caja', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_cobros', 12, 2)->default(0);
            $table->decimal('total_ingresos', 12, 2)->default(0);
            $table->decimal('total_egresos', 12, 2)->default(0);
            $table->decimal('saldo_calculado', 12, 2)->default(0);
            $table->decimal('monto_contado', 12, 2)->default(0);
            $table->decimal('diferencia', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
        Schema::dropIfExists('cortes_caja');
    }
};
