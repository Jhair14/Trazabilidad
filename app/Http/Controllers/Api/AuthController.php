<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Register a new operator
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'username' => 'required|string|max:60|unique:operator,username',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email|max:100',
            'role_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Get default role if not provided
            $roleId = $request->role_id;
            if (!$roleId) {
                $defaultRole = \App\Models\OperatorRole::where('name', 'Operator')->first();
                $roleId = $defaultRole ? $defaultRole->role_id : 2;
            }

            // Generate ID manually if needed (checking max ID)
            $maxId = Operator::max('operator_id') ?? 0;
            $nextId = $maxId + 1;

            $operator = Operator::create([
                'operator_id' => $nextId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'password_hash' => Hash::make($request->password),
                'email' => $request->email,
                'role_id' => $roleId,
                'active' => true
            ]);

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'operator_id' => $operator->operator_id
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login operator
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $credentials = $request->only('username', 'password');
        
        // Find operator by username
        $operator = Operator::where('username', $credentials['username'])->first();
        
        if (!$operator || !Hash::check($credentials['password'], $operator->password_hash)) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        try {
            $token = JWTAuth::fromUser($operator);
            
            return response()->json([
                'token' => $token,
                'operator' => [
                    'operator_id' => $operator->operator_id,
                    'first_name' => $operator->first_name,
                    'last_name' => $operator->last_name,
                    'username' => $operator->username,
                    'email' => $operator->email,
                    'role' => $operator->role,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al iniciar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated operator
     */
    public function me(): JsonResponse
    {
        try {
            $operator = auth()->user();
            
            if (!$operator) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            return response()->json([
                'operator_id' => $operator->operator_id,
                'first_name' => $operator->first_name,
                'last_name' => $operator->last_name,
                'username' => $operator->username,
                'email' => $operator->email,
                'role' => $operator->role,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener información del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout operator
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            
            return response()->json([
                'message' => 'Sesión cerrada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

