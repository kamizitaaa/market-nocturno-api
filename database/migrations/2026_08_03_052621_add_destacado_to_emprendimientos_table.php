<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emprendimientos', function (Blueprint $table) {
            $table->boolean('destacado')->default(false)->after('estado');
        });

        // Migra los datos existentes: los que tenían estado='destacado' pasan a activo + destacado=true
        DB::table('emprendimientos')
            ->where('estado', 'destacado')
            ->update(['estado' => 'activo', 'destacado' => true]);

        // Ahora sí, limita el enum de estado a solo activo/inactivo
        DB::statement("ALTER TABLE emprendimientos MODIFY estado ENUM('activo', 'inactivo') DEFAULT 'activo'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE emprendimientos MODIFY estado ENUM('activo', 'inactivo', 'destacado') DEFAULT 'activo'");

        Schema::table('emprendimientos', function (Blueprint $table) {
            $table->dropColumn('destacado');
        });
    }
};