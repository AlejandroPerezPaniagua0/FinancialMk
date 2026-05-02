<?php

namespace App\Services\Interfaces;

use App\DTOs\Watchlist\WatchlistDTO;
use App\Models\User;

interface WatchlistServiceInterface
{
    /** @return array<int, WatchlistDTO> */
    public function listFor(User $user): array;

    public function show(User $user, int $watchlistId): ?WatchlistDTO;

    public function create(User $user, string $name, bool $isDefault = false): WatchlistDTO;

    public function rename(User $user, int $watchlistId, string $name): ?WatchlistDTO;

    public function delete(User $user, int $watchlistId): bool;

    /**
     * Replace the instruments of a watchlist (idempotent).
     *
     * @param array<int, int> $instrumentIds
     */
    public function setInstruments(User $user, int $watchlistId, array $instrumentIds): ?WatchlistDTO;
}
