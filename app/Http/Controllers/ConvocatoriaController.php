<?php

namespace App\Http\Controllers;

use App\Models\Convocatoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\ConvocatoriaParticipante;

class ConvocatoriaController extends Controller
{
    // Listar convocatorias activas (público)
    public function index()
    {
        $convocatorias = Convocatoria::where('activa', true)
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return response()->json($convocatorias);
    }

    // Listar TODAS (admin/superadmin, activas e inactivas)
    public function adminIndex()
    {
        $convocatorias = Convocatoria::orderBy('created_at', 'desc')->get();

        return response()->json($convocatorias);
    }

    // Ver una específica
    public function show($id)
    {
        $convocatoria = Convocatoria::find($id);

        if (!$convocatoria) {
            return response()->json(['message' => 'Convocatoria no encontrada'], 404);
        }

        return response()->json($convocatoria);
    }

    // Crear (admin/superadmin)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'imagen' => 'nullable|string',
            'activa' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $convocatoria = Convocatoria::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'imagen' => $request->imagen,
            'activa' => $request->activa ?? false,
        ]);

        return response()->json([
            'message' => 'Convocatoria creada correctamente',
            'convocatoria' => $convocatoria
        ], 201);
    }

    // Editar (admin/superadmin)
    public function update(Request $request, $id)
    {
        $convocatoria = Convocatoria::find($id);

        if (!$convocatoria) {
            return response()->json(['message' => 'Convocatoria no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin' => 'sometimes|required|date|after_or_equal:fecha_inicio',
            'imagen' => 'nullable|string',
            'activa' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $convocatoria->update($request->only([
            'titulo', 'descripcion', 'fecha_inicio', 'fecha_fin', 'imagen', 'activa'
        ]));

        return response()->json([
            'message' => 'Convocatoria actualizada correctamente',
            'convocatoria' => $convocatoria
        ]);
    }

    // Eliminar (admin/superadmin)
    public function destroy($id)
    {
        $convocatoria = Convocatoria::find($id);

        if (!$convocatoria) {
            return response()->json(['message' => 'Convocatoria no encontrada'], 404);
        }

        if ($convocatoria->imagen) {
            $ruta = str_replace('/storage/', '', parse_url($convocatoria->imagen, PHP_URL_PATH));
            Storage::disk('public')->delete($ruta);
        }

        $convocatoria->delete();

        return response()->json(['message' => 'Convocatoria eliminada correctamente']);
    }

    // Activar/desactivar rápido
    public function toggleActiva($id)
    {
        $convocatoria = Convocatoria::find($id);

        if (!$convocatoria) {
            return response()->json(['message' => 'Convocatoria no encontrada'], 404);
        }

        $convocatoria->activa = !$convocatoria->activa;
        $convocatoria->save();

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'convocatoria' => $convocatoria
        ]);
    }

    // Subir imagen
    public function subirImagen(Request $request, $id)
    {
        $convocatoria = Convocatoria::find($id);

        if (!$convocatoria) {
            return response()->json(['message' => 'Convocatoria no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'imagen' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($convocatoria->imagen) {
            $rutaAnterior = str_replace('/storage/', '', parse_url($convocatoria->imagen, PHP_URL_PATH));
            Storage::disk('public')->delete($rutaAnterior);
        }

        $ruta = $request->file('imagen')->store('convocatorias', 'public');
        $url = asset('storage/' . $ruta);

        $convocatoria->imagen = $url;
        $convocatoria->save();

        return response()->json([
            'message' => 'Imagen actualizada correctamente',
            'convocatoria' => $convocatoria
        ]);
    }

    // Inscribirse a una convocatoria (público, cualquiera)
    public function inscribirse(Request $request, $id)
    {
        $convocatoria = Convocatoria::find($id);

        if (!$convocatoria) {
            return response()->json(['message' => 'Convocatoria no encontrada'], 404);
        }

        if (!$convocatoria->activa) {
            return response()->json(['message' => 'Esta convocatoria ya no está disponible'], 422);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email',
            'tipo_negocio' => 'required|string|max:255',
            'mensaje' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $participante = ConvocatoriaParticipante::create([
            'convocatoria_id' => $convocatoria->id,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'tipo_negocio' => $request->tipo_negocio,
            'mensaje' => $request->mensaje,
        ]);

        return response()->json([
            'message' => 'Te inscribiste correctamente, pronto nos pondremos en contacto contigo',
            'participante' => $participante
        ], 201);
    }

    // Listar participantes de una convocatoria (admin/superadmin)
    public function participantes($id)
    {
        $convocatoria = Convocatoria::find($id);

        if (!$convocatoria) {
            return response()->json(['message' => 'Convocatoria no encontrada'], 404);
        }

        $participantes = ConvocatoriaParticipante::where('convocatoria_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($participantes);
    }
}