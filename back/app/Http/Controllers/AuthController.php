<?php

namespace App\Http\Controllers;

use App\DTOs\Auth\LoginUserDTO;
use App\DTOs\Auth\RegisterUserDTO;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthServiceInterface $authService) {}

    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $dto = RegisterUserDTO::fromRequest($validated);
        $response = $this->authService->register($dto);

        return response()->json($response->toArray(), 201);
    }

    /**
     * Login user and issue token
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        try {
            $dto = LoginUserDTO::fromRequest($validated);
            $response = $this->authService->login($dto);

            return response()->json($response->toArray(), 200);
        } catch (AuthenticationException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    /**
     * Logout user and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}