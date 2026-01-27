<?php

namespace App\DTOs\Auth;

use App\Models\User;

class AuthResponseDTO
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $tokenType,
        public readonly User $user,
        public readonly string $message,
    ) {}

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'user' => $this->user->toArray(),
        ];
    }
}
