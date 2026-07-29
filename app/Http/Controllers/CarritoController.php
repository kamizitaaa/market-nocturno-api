<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarritoController extends Controller
{
    // Obtiene (o crea si no existe) el carrito activo del cliente autenticado
    private function obtenerOCrearCarrito($clienteId)
    {
        $carrito = Carrito::where('cliente_id', $clienteId)
            ->where('estado', 'activo')
            ->first();

        if (!$carrito) {
            $carrito = Carrito::create([
                'cliente_id' => $clienteId,
                'estado' => 'activo',
            ]);
        }

        return $carrito;
    }

    // Ver el carrito actual del cliente autenticado, con sus productos
    public function index(Request $request)
    {
        $carrito = $this->obtenerOCrearCarrito($request->user()->id);
        $carrito->load('items.producto');

        return response()->json($carrito);
    }

    // Agregar un producto al carrito (o sumarle cantidad si ya está)
    public function agregar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $carrito = $this->obtenerOCrearCarrito($request->user()->id);

        $item = CarritoItem::where('carrito_id', $carrito->id)
            ->where('producto_id', $request->producto_id)
            ->first();

        if ($item) {
            $item->cantidad += $request->cantidad;
            $item->actualizado_en = now();
            $item->save();
        } else {
            $item = CarritoItem::create([
                'carrito_id' => $carrito->id,
                'producto_id' => $request->producto_id,
                'cantidad' => $request->cantidad,
                'estado' => 'pendiente',
                'actualizado_en' => now(),
            ]);
        }

        $carrito->load('items.producto');

        return response()->json([
            'message' => 'Producto agregado al carrito',
            'carrito' => $carrito
        ], 201);
    }

    // Actualizar la cantidad de un item específico del carrito
    public function actualizarCantidad(Request $request, $itemId)
    {
        $item = CarritoItem::find($itemId);

        if (!$item) {
            return response()->json(['message' => 'Item no encontrado'], 404);
        }

        // Verifica que el item pertenezca al carrito del usuario autenticado
        if ($item->carrito->cliente_id !== $request->user()->id) {
            return response()->json(['message' => 'No tienes permiso para esta acción'], 403);
        }

        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item->cantidad = $request->cantidad;
        $item->actualizado_en = now();
        $item->save();

        return response()->json([
            'message' => 'Cantidad actualizada',
            'item' => $item->load('producto')
        ]);
    }

    // Eliminar un item del carrito
    public function eliminarItem(Request $request, $itemId)
    {
        $item = CarritoItem::find($itemId);

        if (!$item) {
            return response()->json(['message' => 'Item no encontrado'], 404);
        }

        if ($item->carrito->cliente_id !== $request->user()->id) {
            return response()->json(['message' => 'No tienes permiso para esta acción'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Producto eliminado del carrito']);
    }

    // Vaciar el carrito completo
    public function vaciar(Request $request)
    {
        $carrito = $this->obtenerOCrearCarrito($request->user()->id);
        $carrito->items()->delete();

        return response()->json(['message' => 'Carrito vaciado correctamente']);
    }
}