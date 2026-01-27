<?php

namespace App\Services;

use App\DTOs\Auth\AuthResponseDTO;
use App\DTOs\Auth\LoginUserDTO;
use App\DTOs\Auth\RegisterUserDTO;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class AuthService implements AuthServiceInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function register(RegisterUserDTO $dto): AuthResponseDTO
    {
        $user = $this->userRepository->create($dto);
        $token = $this->generateToken($user);

        return new AuthResponseDTO(
            accessToken: $token,
            tokenType: 'Bearer',
            user: $user,
            message: 'User created successfully',
        );
    }

    public function login(LoginUserDTO $dto): AuthResponseDTO
    {
        if (!Auth::attempt($dto->toArray())) {
            throw new AuthenticationException('Invalid credentials');
        }

        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        $token = $this->generateToken($user);

        return new AuthResponseDTO(
            accessToken: $token,
            tokenType: 'Bearer',
            user: $user,
            message: 'Logged in successfully',
        );
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    private function generateToken(User $user, string $tokenName = 'auth_token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }
}
