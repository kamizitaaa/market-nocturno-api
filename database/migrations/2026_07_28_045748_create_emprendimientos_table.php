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
    Schema::create('emprendimientos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
        $table->string('nombre');
        $table->decimal('precio_desde', 8, 2)->nullable();
        $table->decimal('precio_hasta', 8, 2)->nullable();
        $table->enum('estado', ['activo', 'inactivo', 'destacado'])->default('activo');
        $table->text('descripcion')->nullable();
        $table->string('imagen')->nullable();
        $table->date('fecha')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emprendimientos');
    }
};
