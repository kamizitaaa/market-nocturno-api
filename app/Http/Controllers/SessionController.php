<?php

namespace App\Http\Controllers;

use App\Models\UserSession;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    // Lista las sesiones activas de admin y emprendedor (solo superadmin puede ver esto)
    public function index()
    {
        $sesiones = UserSession::with('user')
            ->where('activa', true)
            ->whereHas('user', function ($query) {
                $query->whereIn('role', ['admin', 'emprendedor']);
            })
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return response()->json($sesiones);
    }

    // Cierra una sesión específica a la fuerza (revoca el token real)
    public function cerrar($id)
    {
        $sesion = UserSession::find($id);

        if (!$sesion) {
            return response()->json(['message' => 'Sesión no encontrada'], 404);
        }

        if ($sesion->token_id) {
            $token = PersonalAccessToken::find($sesion->token_id);
            if ($token) {
                $token->delete();
            }
        }

        $sesion->activa = false;
        $sesion->save();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
}