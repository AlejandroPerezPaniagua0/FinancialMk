<?php

namespace App\Repositories\Interfaces;

use App\Models\Watchlist;
use Illuminate\Support\Collection;

interface WatchlistRepositoryInterface
{
    /** @return Collection<int, Watchlist> */
    public function listForUser(int $userId): Collection;

    public function findForUser(int $watchlistId, int $userId): ?Watchlist;

    public function create(int $userId, string $name, bool $isDefault = false): Watchlist;

    public function rename(Watchlist $watchlist, string $name): Watchlist;

    public function delete(Watchlist $watchlist): void;

    /**
     * Replace the set of instruments on a watchlist.
     *
     * @param array<int, int> $instrumentIds Ordered list — index drives `position`.
     */
    public function syncInstruments(Watchlist $watchlist, array $instrumentIds): Watchlist;

    public function clearDefaultFlagForUser(int $userId): void;
}
