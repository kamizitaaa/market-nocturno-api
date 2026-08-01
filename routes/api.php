<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\EmprendimientoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\AdminController;

Route::get('/captcha', [AuthController::class, 'captcha']);
Route::post('/registro', [AuthController::class, 'registro']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verificar-mfa', [AuthController::class, 'verificarMfa']);
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/categorias/{id}', [CategoriaController::class, 'show']);
Route::get('/emprendimientos', [EmprendimientoController::class, 'index']);
Route::get('/emprendimientos/{id}', [EmprendimientoController::class, 'show']);
Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);

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
        Route::put('/admin/emprendedores/{id}/mfa', [AdminController::class, 'toggleMfa']);
    });

    // Dashboard admin
    Route::get('/admin/stats', [AdminController::class, 'stats']);
    Route::get('/admin/emprendedores', [AdminController::class, 'emprendedores']);
    Route::post('/admin/emprendedores', [AdminController::class, 'crearEmprendedor']);
    Route::put('/admin/emprendedores/{id}', [AdminController::class, 'actualizarEmprendedor']);
    Route::delete('/admin/emprendedores/{id}', [AdminController::class, 'eliminarEmprendedor']);


    // Carrito
    Route::get('/carrito', [CarritoController::class, 'index']);
    Route::post('/carrito/agregar', [CarritoController::class, 'agregar']);
    Route::put('/carrito/items/{itemId}', [CarritoController::class, 'actualizarCantidad']);
    Route::delete('/carrito/items/{itemId}', [CarritoController::class, 'eliminarItem']);
    Route::delete('/carrito/vaciar', [CarritoController::class, 'vaciar']);

    // Cualquier usuario autenticado puede crear su emprendimiento
    Route::post('/emprendimientos', [EmprendimientoController::class, 'store']);
    Route::put('/emprendimientos/{id}', [EmprendimientoController::class, 'update']);
    Route::delete('/emprendimientos/{id}', [EmprendimientoController::class, 'destroy']);
    Route::get('/mis-emprendimientos', [EmprendimientoController::class, 'misEmprendimientos']);
    Route::post('/emprendimientos/{id}/imagen', [EmprendimientoController::class, 'subirImagen']);

    //agregar productos
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::put('/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);
    Route::post('/productos/{id}/imagen', [ProductoController::class, 'subirImagen']);

    //Configuracion de carrito sobre los pedidos
    Route::post('/pedidos/confirmar', [PedidoController::class, 'confirmar']);
    Route::get('/mis-pedidos', [PedidoController::class, 'misPedidos']);
    Route::get('/pedidos-emprendimiento', [PedidoController::class, 'pedidosDeMiEmprendimiento']);
    Route::put('/pedidos/{subPedidoId}/cancelar', [PedidoController::class, 'cancelarComoCliente']);
    Route::put('/pedidos-emprendimiento/{subPedidoId}/estado', [PedidoController::class, 'actualizarEstadoComoEmprendedor']);
});