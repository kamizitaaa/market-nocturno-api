<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use Illuminate\Http\Request;

class ContactoController extends Controller
{
    // Público — guarda el mensaje del formulario de contacto
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'  => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'asunto'  => 'required|string|max:255',
            'mensaje' => 'required|string',
        ]);

        $contacto = Contacto::create($validated);

        return response()->json([
            'message' => 'Mensaje enviado correctamente',
            'data'    => $contacto,
        ], 201);
    }

    // Admin — lista todos los mensajes, más recientes primero
    public function adminIndex()
    {
        $contactos = Contacto::orderBy('created_at', 'desc')->get();

        return response()->json($contactos);
    }

    // Admin — elimina un mensaje
    public function destroy($id)
    {
        $contacto = Contacto::find($id);

        if (!$contacto) {
            return response()->json(['message' => 'Mensaje no encontrado'], 404);
        }

        $contacto->delete();

        return response()->json(['message' => 'Mensaje eliminado correctamente']);
    }
}