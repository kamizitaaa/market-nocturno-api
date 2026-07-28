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
    Schema::create('carrito_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('carrito_id')->constrained('carritos')->onDelete('cascade');
        $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
        $table->integer('cantidad')->default(1);
        $table->enum('estado', ['pendiente', 'listo_para_entregar', 'entregado', 'cancelado'])->default('pendiente');
        $table->timestamp('actualizado_en')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrito_items');
    }
};
