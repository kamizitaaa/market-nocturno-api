<?php

namespace App\Http\Controllers;

use App\Models\AcercaInfo;
use App\Models\Equipo;
use App\Models\Galeria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AcercaController extends Controller
{
    // ===== INFO (historia, misión, visión) =====

    public function getInfo()
    {
        $info = AcercaInfo::first();

        if (!$info) {
            $info = AcercaInfo::create([
                'historia_titulo' => 'Nuestra Historia',
                'historia_texto' => '',
                'mision' => '',
                'vision' => '',
            ]);
        }

        return response()->json($info);
    }

    public function updateInfo(Request $request)
    {
        $info = AcercaInfo::first();
        if (!$info) {
            $info = AcercaInfo::create([]);
        }

        $validator = Validator::make($request->all(), [
            'historia_titulo' => 'nullable|string|max:255',
            'historia_texto' => 'nullable|string',
            'mision' => 'nullable|string',
            'vision' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $info->update($request->only(['historia_titulo', 'historia_texto', 'mision', 'vision']));

        return response()->json([
            'message' => 'Información actualizada correctamente',
            'info' => $info
        ]);
    }

    public function subirImagenHistoria(Request $request)
    {
        $info = AcercaInfo::first();
        if (!$info) {
            $info = AcercaInfo::create([]);
        }

        $validator = Validator::make($request->all(), [
            'imagen' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($info->historia_imagen) {
            $rutaAnterior = str_replace('/storage/', '', parse_url($info->historia_imagen, PHP_URL_PATH));
            Storage::disk('public')->delete($rutaAnterior);
        }

        $ruta = $request->file('imagen')->store('acerca', 'public');
        $info->historia_imagen = asset('storage/' . $ruta);
        $info->save();

        return response()->json([
            'message' => 'Imagen actualizada correctamente',
            'info' => $info
        ]);
    }

    // ===== EQUIPO =====

    public function equipoIndex()
    {
        return response()->json(Equipo::orderBy('orden')->get());
    }

    public function equipoStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'puesto' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $miembro = Equipo::create($request->only(['nombre', 'puesto', 'descripcion', 'orden']));

        return response()->json([
            'message' => 'Integrante agregado correctamente',
            'miembro' => $miembro
        ], 201);
    }

    public function equipoUpdate(Request $request, $id)
    {
        $miembro = Equipo::find($id);
        if (!$miembro) {
            return response()->json(['message' => 'Integrante no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'puesto' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $miembro->update($request->only(['nombre', 'puesto', 'descripcion', 'orden']));

        return response()->json([
            'message' => 'Integrante actualizado correctamente',
            'miembro' => $miembro
        ]);
    }

    public function equipoDestroy($id)
    {
        $miembro = Equipo::find($id);
        if (!$miembro) {
            return response()->json(['message' => 'Integrante no encontrado'], 404);
        }

        if ($miembro->foto) {
            $ruta = str_replace('/storage/', '', parse_url($miembro->foto, PHP_URL_PATH));
            Storage::disk('public')->delete($ruta);
        }

        $miembro->delete();

        return response()->json(['message' => 'Integrante eliminado correctamente']);
    }

    public function equipoSubirFoto(Request $request, $id)
    {
        $miembro = Equipo::find($id);
        if (!$miembro) {
            return response()->json(['message' => 'Integrante no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($miembro->foto) {
            $rutaAnterior = str_replace('/storage/', '', parse_url($miembro->foto, PHP_URL_PATH));
            Storage::disk('public')->delete($rutaAnterior);
        }

        $ruta = $request->file('foto')->store('equipo', 'public');
        $miembro->foto = asset('storage/' . $ruta);
        $miembro->save();

        return response()->json([
            'message' => 'Foto actualizada correctamente',
            'miembro' => $miembro
        ]);
    }

    // ===== GALERÍA =====

    public function galeriaIndex()
    {
        return response()->json(Galeria::orderBy('orden')->get());
    }

    public function galeriaStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'nullable|string|max:255',
            'orden' => 'nullable|integer',
            'imagen' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ruta = $request->file('imagen')->store('galeria', 'public');

        $item = Galeria::create([
            'url' => asset('storage/' . $ruta),
            'titulo' => $request->titulo,
            'orden' => $request->orden ?? 0,
        ]);

        return response()->json([
            'message' => 'Imagen agregada correctamente',
            'item' => $item
        ], 201);
    }

    public function galeriaDestroy($id)
    {
        $item = Galeria::find($id);
        if (!$item) {
            return response()->json(['message' => 'Imagen no encontrada'], 404);
        }

        if ($item->url) {
            $ruta = str_replace('/storage/', '', parse_url($item->url, PHP_URL_PATH));
            Storage::disk('public')->delete($ruta);
        }

        $item->delete();

        return response()->json(['message' => 'Imagen eliminada correctamente']);
    }
}