<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login de empleados (roles 3 y 4).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        // Solo Manager (3) y Empleado (4)
        if (! in_array($user->role_id, [3, 4])) {
            return response()->json([
                'message' => 'No autorizado para esta app',
            ], 403);
        }

        // Opcional: eliminar tokens anteriores
        $user->tokens()->delete();

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role_id'    => $user->role_id,
                'company_id' => $user->company_id,
                'branch_id'  => $user->branch_id,
            ],
        ]);
    }

    /**
     * Logout: elimina el token actual.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout correcto',
        ]);
    }

    /**
     * Información del usuario autenticado.
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
