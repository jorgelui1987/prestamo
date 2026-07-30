<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empenos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('articulo');
            $table->text('descripcion')->nullable();
            $table->decimal('valor_tasacion', 12, 2);
            $table->decimal('monto_prestado', 12, 2);
            $table->decimal('tasa_interes', 5, 2)->default(0);
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento');
            $table->string('estado')->default('vigente'); // vigente / recuperado / vencido / rematado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empenos');
    }
};
