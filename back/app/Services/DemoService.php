<?php

namespace App\Services;

use App\DTOs\Auth\AuthResponseDTO;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\DemoServiceInterface;
use RuntimeException;

/**
 * Owns the "is demo mode on?" decision and the token issuance for the demo
 * user. Kept separate from AuthService so demo flows don't leak into the
 * normal credential-based path.
 */
class DemoService implements DemoServiceInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function isEnabled(): bool
    {
        return (bool) config('demo.enabled', false);
    }

    public function loginAsDemo(): AuthResponseDTO
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Demo mode is disabled.');
        }

        $email = (string) config('demo.email');
        $user  = $this->userRepository->findByEmail($email);

        if (! $user) {
            throw new RuntimeException("Demo user '{$email}' not found — run `php artisan db:seed --class=DemoUserSeeder`.");
        }

        // Tokens for the demo user are short-lived and generic — we wipe
        // existing ones to keep the demo inbox clean across visits.
        $user->tokens()->delete();
        $token = $user->createToken('demo_token')->plainTextToken;

        return new AuthResponseDTO(
            accessToken: $token,
            tokenType:   'Bearer',
            user:        $user,
            message:     'Logged in as demo user',
        );
    }
}
