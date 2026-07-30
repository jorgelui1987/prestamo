<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('usuario_nombre')->nullable();
            $table->string('accion');          // creo / actualizo / elimino / inicio sesion / cerro sesion
            $table->string('modulo')->nullable();
            $table->string('referencia')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['modulo', 'accion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
