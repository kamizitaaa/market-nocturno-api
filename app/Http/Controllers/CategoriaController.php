<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{
    // Listar todas (público, cualquiera puede ver categorías)
    public function index()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        return response()->json($categorias);
    }

    // Ver una categoría específica
    public function show($id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        return response()->json($categoria);
    }

    // Crear (solo admin/superadmin)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
            'activa' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $categoria = Categoria::create([
            'nombre' => $request->nombre,
            'activa' => $request->activa ?? true,
        ]);

        return response()->json([
            'message' => 'Categoría creada correctamente',
            'categoria' => $categoria
        ], 201);
    }

    // Editar (solo admin/superadmin)
    public function update(Request $request, $id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255|unique:categorias,nombre,' . $id,
            'activa' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $categoria->update($request->only(['nombre', 'activa']));

        return response()->json([
            'message' => 'Categoría actualizada correctamente',
            'categoria' => $categoria
        ]);
    }

    // Eliminar (solo admin/superadmin)
    public function destroy($id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada correctamente']);
    }
}