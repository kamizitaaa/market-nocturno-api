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
    Schema::create('acerca_info', function (Blueprint $table) {
        $table->id();
        $table->string('historia_titulo')->nullable();
        $table->text('historia_texto')->nullable();
        $table->string('historia_imagen')->nullable();
        $table->text('mision')->nullable();
        $table->text('vision')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acerca_info');
    }
};
