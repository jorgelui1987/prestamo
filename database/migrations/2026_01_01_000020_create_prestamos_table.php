<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->decimal('monto', 12, 2);                 // capital prestado
            $table->decimal('tasa_interes', 5, 2);           // % por periodo
            $table->integer('numero_cuotas');
            $table->string('frecuencia')->default('mensual'); // diario/semanal/quincenal/mensual
            $table->decimal('monto_cuota', 12, 2)->default(0);
            $table->decimal('total_pagar', 12, 2)->default(0);
            $table->decimal('interes_total', 12, 2)->default(0);
            $table->decimal('saldo', 12, 2)->default(0);
            $table->date('fecha_inicio');
            $table->string('estado')->default('activo');     // activo / pagado / mora / cancelado
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
