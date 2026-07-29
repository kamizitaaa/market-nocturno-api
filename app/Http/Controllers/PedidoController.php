<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoEmprendimiento;
use App\Models\PedidoItem;
use App\Models\Carrito;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    // Cliente confirma su carrito → se convierte en pedido(s), y el carrito se elimina
    public function confirmar(Request $request)
    {
        $carrito = Carrito::where('cliente_id', $request->user()->id)
            ->where('estado', 'activo')
            ->with('items.producto.emprendimiento')
            ->first();

        if (!$carrito || $carrito->items->isEmpty()) {
            return response()->json(['message' => 'Tu carrito está vacío'], 400);
        }

        $pedido = Pedido::create([
            'cliente_id' => $request->user()->id,
        ]);

        // Agrupa los items del carrito por emprendimiento
        $itemsPorEmprendimiento = $carrito->items->groupBy(fn($item) => $item->producto->emprendimiento_id);

        foreach ($itemsPorEmprendimiento as $emprendimientoId => $items) {
            $subPedido = PedidoEmprendimiento::create([
                'pedido_id' => $pedido->id,
                'emprendimiento_id' => $emprendimientoId,
                'estado' => 'pendiente',
            ]);

            foreach ($items as $item) {
                PedidoItem::create([
                    'pedido_emprendimiento_id' => $subPedido->id,
                    'producto_id' => $item->producto_id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->producto->precio,
                ]);
            }
        }

        // Elimina el carrito por completo (items + carrito)
        $carrito->items()->delete();
        $carrito->delete();

        $pedido->load('subPedidos.emprendimiento', 'subPedidos.items.producto');

        return response()->json([
            'message' => 'Pedido confirmado correctamente',
            'pedido' => $pedido
        ], 201);
    }

    // Cliente ve todos sus pedidos
    public function misPedidos(Request $request)
    {
        $pedidos = Pedido::where('cliente_id', $request->user()->id)
            ->with('subPedidos.emprendimiento', 'subPedidos.items.producto')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($pedidos);
    }

    // Emprendedor ve los sub-pedidos que le corresponden
    public function pedidosDeMiEmprendimiento(Request $request)
    {
        $emprendimientoIds = $request->user()->emprendimientos()->pluck('id');

        $subPedidos = PedidoEmprendimiento::whereIn('emprendimiento_id', $emprendimientoIds)
            ->with('pedido.cliente', 'items.producto', 'emprendimiento')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($subPedidos);
    }

    // Cliente cancela un sub-pedido específico (una parte de su pedido)
    public function cancelarComoCliente(Request $request, $subPedidoId)
    {
        $subPedido = PedidoEmprendimiento::with('pedido')->find($subPedidoId);

        if (!$subPedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        if ($subPedido->pedido->cliente_id !== $request->user()->id) {
            return response()->json(['message' => 'No tienes permiso para esta acción'], 403);
        }

        $subPedido->estado = 'cancelado';
        $subPedido->save();

        return response()->json(['message' => 'Pedido cancelado correctamente', 'sub_pedido' => $subPedido]);
    }

    // Emprendedor cancela (o actualiza estado de) su propio sub-pedido
    public function actualizarEstadoComoEmprendedor(Request $request, $subPedidoId)
    {
        $subPedido = PedidoEmprendimiento::with('emprendimiento')->find($subPedidoId);

        if (!$subPedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        if ($subPedido->emprendimiento->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No tienes permiso para esta acción'], 403);
        }

        $request->validate([
            'estado' => 'required|in:pendiente,listo_para_entregar,entregado,cancelado',
        ]);

        $subPedido->estado = $request->estado;
        $subPedido->save();

        return response()->json(['message' => 'Estado actualizado correctamente', 'sub_pedido' => $subPedido]);
    }
}