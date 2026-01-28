<?php

namespace App\Services\Interfaces;

use App\DTOs\Auth\AuthResponseDTO;
use App\DTOs\Auth\LoginUserDTO;
use App\DTOs\Auth\RegisterUserDTO;
use App\Models\User;

interface AuthServiceInterface
{
    public function register(RegisterUserDTO $dto): AuthResponseDTO;

    public function login(LoginUserDTO $dto): AuthResponseDTO;

    public function logout(User $user): void;
}