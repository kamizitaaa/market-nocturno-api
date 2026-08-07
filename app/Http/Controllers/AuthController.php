<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MfaCode;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    // ===== CAPTCHA MATEMÁTICO =====
    public function captcha(Request $request)
    {
        $a = rand(1, 9);
        $b = rand(1, 9);

        $token = Crypt::encryptString(json_encode([
            'respuesta' => $a * $b,
            'expira' => now()->addMinutes(5)->timestamp,
        ]));

        return response()->json([
            'numero1' => $a,
            'numero2' => $b,
            'captcha_token' => $token,
        ]);
    }

    // ===== REGISTRO (clientes) =====
    public function registro(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'cliente',
        ]);

        return response()->json([
            'message' => 'Cuenta creada correctamente',
            'user' => $user
        ], 201);
    }

    // ===== LOGIN (paso 1: valida credenciales + captcha, envía MFA si aplica) =====
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'captcha_respuesta' => 'required|numeric',
            'captcha_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validar captcha (desencriptar token)
        try {
            $datos = json_decode(Crypt::decryptString($request->captcha_token), true);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Captcha inválido'], 422);
        }

        if (now()->timestamp > $datos['expira']) {
            return response()->json(['message' => 'El captcha expiró, solicita uno nuevo'], 422);
        }

        if ((int)$request->captcha_respuesta !== (int)$datos['respuesta']) {
            return response()->json(['message' => 'Verificación humana incorrecta'], 422);
        }

        // Validar credenciales
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        // Si el usuario NO tiene MFA activado, damos el token directo, sin pasar por el paso 2
        if (!$user->mfa_enabled) {
            $tokenResult = $user->createToken('auth_token');
            $token = $tokenResult->plainTextToken;

            if (in_array($user->role, ['admin', 'superadmin', 'emprendedor'])) {
                UserSession::create([
                    'user_id' => $user->id,
                    'token_id' => $tokenResult->accessToken->id,
                    'ip_address' => $request->ip(),
                    'navegador' => $request->userAgent(),
                    'fecha_inicio' => now(),
                    'activa' => true,
                ]);
            }

            return response()->json([
                'message' => 'Login exitoso',
                'mfa_requerido' => false,
                'token' => $token,
                'user' => $user,
            ]);
        }

        // Si SÍ tiene MFA activado, seguimos el flujo normal: generar y enviar código
        $codigo = rand(100000, 999999);

        MfaCode::create([
            'user_id' => $user->id,
            'codigo' => $codigo,
            'expira_en' => now()->addMinutes(10),
            'usado' => false,
        ]);

        Mail::raw("Tu código de verificación es: $codigo\nExpira en 10 minutos.", function ($msg) use ($user) {
            $msg->to($user->email)->subject('Código de verificación - Market Nocturno');
        });

        return response()->json([
            'message' => 'Código enviado a tu correo',
            'mfa_requerido' => true,
            'user_id' => $user->id,
        ]);
    }

    // ===== LOGIN (paso 2: verifica código MFA y da token) =====
    public function verificarMfa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'codigo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $mfa = MfaCode::where('user_id', $request->user_id)
            ->where('codigo', $request->codigo)
            ->where('usado', false)
            ->where('expira_en', '>=', now())
            ->latest()
            ->first();

        if (!$mfa) {
            return response()->json(['message' => 'Código inválido o expirado'], 401);
        }

        $mfa->update(['usado' => true]);

        $user = User::findOrFail($request->user_id);
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->plainTextToken;
        
        // Registrar sesión (solo para admin/superadmin/emprendedor)
        if (in_array($user->role, ['admin', 'superadmin', 'emprendedor'])) {
            UserSession::create([
                'user_id' => $user->id,
                'token_id' => $tokenResult->accessToken->id,
                'ip_address' => $request->ip(),
                'navegador' => $request->userAgent(),
                'fecha_inicio' => now(),
                'activa' => true,
            ]);
        }

        return response()->json([
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => $user,
        ]);
    }

    // ===== LOGOUT =====
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        // Marcar sesión como inactiva
        UserSession::where('user_id', $request->user()->id)
            ->where('activa', true)
            ->latest()
            ->first()?->update(['activa' => false]);

        return response()->json(['message' => 'Sesión cerrada']);
    }

    // ===== USUARIO AUTENTICADO =====
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}