<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Emprendimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    // Listar todos los productos (público) — con filtro opcional por emprendimiento
    public function index(Request $request)
    {
        $query = Producto::with('emprendimiento')->where('disponible', true);

        if ($request->has('emprendimiento_id')) {
            $query->where('emprendimiento_id', $request->emprendimiento_id);
        }

        return response()->json($query->orderBy('nombre')->get());
    }

    // Ver un producto específico (público)
    public function show($id)
    {
        $producto = Producto::with('emprendimiento')->find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        return response()->json($producto);
    }

    // Crear (solo el dueño del emprendimiento, o admin/superadmin)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emprendimiento_id' => 'required|exists:emprendimientos,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|string',
            'disponible' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $emprendimiento = Emprendimiento::find($request->emprendimiento_id);

        $esDueno = $emprendimiento->user_id === $request->user()->id;
        $esAdmin = in_array($request->user()->role, ['admin', 'superadmin']);

        if (!$esDueno && !$esAdmin) {
            return response()->json(['message' => 'No tienes permiso para esta acción'], 403);
        }

        $producto = Producto::create([
            'emprendimiento_id' => $request->emprendimiento_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'imagen' => $request->imagen,
            'disponible' => $request->disponible ?? true,
        ]);

        return response()->json([
            'message' => 'Producto creado correctamente',
            'producto' => $producto
        ], 201);
    }

    // Editar (solo el dueño del emprendimiento, o admin/superadmin)
    public function update(Request $request, $id)
    {
        $producto = Producto::with('emprendimiento')->find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $esDueno = $producto->emprendimiento->user_id === $request->user()->id;
        $esAdmin = in_array($request->user()->role, ['admin', 'superadmin']);

        if (!$esDueno && !$esAdmin) {
            return response()->json(['message' => 'No tienes permiso para esta acción'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|required|numeric|min:0',
            'imagen' => 'nullable|string',
            'disponible' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $producto->update($request->only(['nombre', 'descripcion', 'precio', 'imagen', 'disponible']));

        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'producto' => $producto
        ]);
    }

    // Eliminar (solo el dueño del emprendimiento, o admin/superadmin)
    public function destroy(Request $request, $id)
    {
        $producto = Producto::with('emprendimiento')->find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $esDueno = $producto->emprendimiento->user_id === $request->user()->id;
        $esAdmin = in_array($request->user()->role, ['admin', 'superadmin']);

        if (!$esDueno && !$esAdmin) {
            return response()->json(['message' => 'No tienes permiso para esta acción'], 403);
        }

        $producto->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}