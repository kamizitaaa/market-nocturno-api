<?php

namespace App\Http\Controllers;

use App\Models\Emprendimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmprendimientoController extends Controller
{
    // Listar todos (público, cualquiera puede ver el catálogo)
    public function index()
    {
        $emprendimientos = Emprendimiento::with(['categoria', 'emprendedor'])
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return response()->json($emprendimientos);
    }

    // Ver uno específico (público)
    public function show($id)
    {
        $emprendimiento = Emprendimiento::with(['categoria', 'emprendedor', 'productos'])->find($id);

        if (!$emprendimiento) {
            return response()->json(['message' => 'Emprendimiento no encontrado'], 404);
        }

        return response()->json($emprendimiento);
    }

    // Crear (solo emprendedor autenticado, crea el suyo propio)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'precio_desde' => 'nullable|numeric|min:0',
            'precio_hasta' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|string',
            'fecha' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $emprendimiento = Emprendimiento::create([
            'user_id' => $request->user()->id,
            'categoria_id' => $request->categoria_id,
            'nombre' => $request->nombre,
            'precio_desde' => $request->precio_desde,
            'precio_hasta' => $request->precio_hasta,
            'estado' => 'activo',
            'descripcion' => $request->descripcion,
            'imagen' => $request->imagen,
            'fecha' => $request->fecha ?? now(),
        ]);

        return response()->json([
            'message' => 'Emprendimiento creado correctamente',
            'emprendimiento' => $emprendimiento
        ], 201);
    }

    // Editar (solo el dueño del emprendimiento, o admin/superadmin)
    public function update(Request $request, $id)
    {
        $emprendimiento = Emprendimiento::find($id);

        if (!$emprendimiento) {
            return response()->json(['message' => 'Emprendimiento no encontrado'], 404);
        }

        $esDueno = $emprendimiento->user_id === $request->user()->id;
        $esAdmin = in_array($request->user()->role, ['admin', 'superadmin']);

        if (!$esDueno && !$esAdmin) {
            return response()->json(['message' => 'No tienes permiso para esta acción'], 403);
        }

        $validator = Validator::make($request->all(), [
            'categoria_id' => 'sometimes|required|exists:categorias,id',
            'nombre' => 'sometimes|required|string|max:255',
            'precio_desde' => 'nullable|numeric|min:0',
            'precio_hasta' => 'nullable|numeric|min:0',
            'estado' => 'sometimes|in:activo,inactivo',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $emprendimiento->update($request->only([
            'categoria_id', 'nombre', 'precio_desde', 'precio_hasta',
            'estado', 'descripcion', 'imagen'
        ]));

        return response()->json([
            'message' => 'Emprendimiento actualizado correctamente',
            'emprendimiento' => $emprendimiento
        ]);
    }

    // Eliminar (solo el dueño, o admin/superadmin)
    public function destroy(Request $request, $id)
    {
        $emprendimiento = Emprendimiento::find($id);

        if (!$emprendimiento) {
            return response()->json(['message' => 'Emprendimiento no encontrado'], 404);
        }

        $esDueno = $emprendimiento->user_id === $request->user()->id;
        $esAdmin = in_array($request->user()->role, ['admin', 'superadmin']);

        if (!$esDueno && !$esAdmin) {
            return response()->json(['message' => 'No tienes permiso para esta acción'], 403);
        }

        $emprendimiento->delete();

        return response()->json(['message' => 'Emprendimiento eliminado correctamente']);
    }

    // Listar los emprendimientos del usuario autenticado (para "Mi Emprendimiento")
    public function misEmprendimientos(Request $request)
    {
        $emprendimientos = Emprendimiento::with('categoria')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($emprendimientos);
    }
}