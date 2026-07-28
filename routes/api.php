<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;

Route::get('/captcha', [AuthController::class, 'captcha']);
Route::post('/registro', [AuthController::class, 'registro']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verificar-mfa', [AuthController::class, 'verificarMfa']);
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/categorias/{id}', [CategoriaController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    //Comprobacion que sirve los roles
    //Route::middleware('role:admin,superadmin')->get('/test-admin', function () {
    //    return response()->json(['message' => 'Eres admin o superadmin, acceso concedido']);
    // });

    // Solo admin/superadmin pueden crear, editar o eliminar categorías
    Route::middleware('role:admin,superadmin')->group(function () {
        Route::post('/categorias', [CategoriaController::class, 'store']);
        Route::put('/categorias/{id}', [CategoriaController::class, 'update']);
        Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);
    });
});