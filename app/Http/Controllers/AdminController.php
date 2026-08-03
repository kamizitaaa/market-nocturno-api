<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Emprendimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Convocatoria;

class AdminController extends Controller
{
    // Estadísticas para el dashboard
    public function stats()
    {
        $totalEmprendimientos = Emprendimiento::count();
        $activos = Emprendimiento::where('estado', 'activo')->count();
        $destacados = Emprendimiento::where('destacado', true)->count();
        $nuevosEmprendimientos = Emprendimiento::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalEmprendedores = User::where('role', 'emprendedor')->count();
        $nuevosEmprendedores = User::where('role', 'emprendedor')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalClientes = User::where('role', 'cliente')->count();

        $convocatoriasActivas = Convocatoria::where('activa', true)->count();

        return response()->json([
            'total' => $totalEmprendimientos,
            'activos' => $activos,
            'nuevos' => $nuevosEmprendimientos,
            'destacados' => $destacados,
            'total_emprendedores' => $totalEmprendedores,
            'nuevos_emprendedores' => $nuevosEmprendedores,
            'total_clientes' => $totalClientes,
            'convocatorias_activas' => $convocatoriasActivas,
        ]);
    }

    // Lista de emprendedores (usuarios con rol emprendedor)
    public function emprendedores()
    {
        $emprendedores = User::where('role', 'emprendedor')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($emprendedores);
    }

    // Crear un nuevo emprendedor (o admin) desde el panel
    public function crearEmprendedor(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'nombre' => 'required|string|max:255',
        'apellido_paterno' => 'required|string|max:255',
        'apellido_materno' => 'nullable|string|max:255',
        'telefono' => 'required|string|max:20',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
        'role' => 'required|in:emprendedor,admin,superadmin',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Solo superadmin puede crear cuentas admin o superadmin
    if (in_array($request->role, ['admin', 'superadmin']) && $request->user()->role !== 'superadmin') {
        return response()->json(['message' => 'Solo un superadmin puede crear cuentas de administrador'], 403);
    }

    $usuario = User::create([
        'nombre' => $request->nombre,
        'apellido_paterno' => $request->apellido_paterno,
        'apellido_materno' => $request->apellido_materno,
        'telefono' => $request->telefono,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
    ]);

    return response()->json([
        'message' => 'Usuario creado correctamente',
        'usuario' => $usuario
    ], 201);
    }

    // Editar un emprendedor
    public function actualizarEmprendedor(Request $request, $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'apellido_paterno' => 'sometimes|required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'telefono' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $usuario->update($request->only([
            'nombre', 'apellido_paterno', 'apellido_materno', 'telefono', 'email'
        ]));

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'usuario' => $usuario
        ]);
    }

    // Eliminar un emprendedor
    public function eliminarEmprendedor($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }

    public function toggleMfa($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $usuario->mfa_enabled = !$usuario->mfa_enabled;
        $usuario->save();

        return response()->json([
            'message' => 'MFA actualizado correctamente',
            'usuario' => $usuario
        ]);
    }
}