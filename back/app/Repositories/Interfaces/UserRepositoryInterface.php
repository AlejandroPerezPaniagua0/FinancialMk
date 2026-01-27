<?php

namespace App\Repositories\Interfaces;

use App\DTOs\Auth\RegisterUserDTO;
use App\Models\User;

interface UserRepositoryInterface
{
    public function create(RegisterUserDTO $dto): User;
    
    public function findByEmail(string $email): ?User;
    
    public function findById(int $id): ?User;
}
