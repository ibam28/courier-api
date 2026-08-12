<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     *
     * Buat user baru + return Sanctum token. Endpoint publik.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed otomatis via cast
            'role' => $data['role'] ?? User::ROLE_STAFF,
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Registered',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * POST /api/auth/login
     *
     * Validasi credentials + return Sanctum token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Authenticated',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * POST /api/auth/logout
     *
     * Revoke token saat ini. Butuh `auth:sanctum` (lihat routes/api.php).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    /**
     * GET /api/auth/me
     *
     * Return data user yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }
}
