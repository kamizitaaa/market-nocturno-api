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
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('apellido_paterno')->nullable();
        $table->string('apellido_materno')->nullable();
        $table->string('email')->unique();
        $table->string('telefono')->nullable();
        $table->string('password');
        $table->enum('role', ['superadmin', 'admin', 'emprendedor', 'cliente'])->default('cliente');
        $table->boolean('mfa_enabled')->default(false);
        $table->rememberToken();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
