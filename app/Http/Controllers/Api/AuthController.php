<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Auth\InactiveAccountException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\LoginUserService;
use App\Services\Auth\RegisterUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserService $registerUser): JsonResponse
    {
        $result = $registerUser->handle(
            $request->validated('name'),
            $request->validated('email'),
            $request->validated('password'),
        );

        return response()->json([
            'token' => $result->token,
            'user' => $result->user,
        ], 201);
    }

    public function login(LoginRequest $request, LoginUserService $loginUser): JsonResponse
    {
        try {
            $result = $loginUser->handle(
                $request->validated('email'),
                $request->validated('password'),
            );
        } catch (InactiveAccountException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json([
            'token' => $result->token,
            'user' => $result->user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
