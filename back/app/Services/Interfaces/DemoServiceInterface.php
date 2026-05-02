<?php

namespace App\Services\Interfaces;

use App\DTOs\Auth\AuthResponseDTO;

interface DemoServiceInterface
{
    public function isEnabled(): bool;

    /**
     * Mint a Sanctum token for the seeded demo user.
     *
     * @throws \RuntimeException When demo mode is disabled or the user is missing.
     */
    public function loginAsDemo(): AuthResponseDTO;
}
