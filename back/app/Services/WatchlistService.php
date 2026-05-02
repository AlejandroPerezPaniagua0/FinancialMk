<?php

namespace App\Services;

use App\DTOs\Watchlist\WatchlistDTO;
use App\Models\User;
use App\Repositories\Interfaces\WatchlistRepositoryInterface;
use App\Services\Interfaces\WatchlistServiceInterface;
use Illuminate\Support\Facades\DB;

/**
 * Owns the "exactly one default watchlist per user" invariant. Controllers
 * stay thin; everything that touches watchlists goes through here.
 */
class WatchlistService implements WatchlistServiceInterface
{
    public function __construct(private readonly WatchlistRepositoryInterface $watchlists) {}

    public function listFor(User $user): array
    {
        return $this->watchlists->listForUser($user->id)
            ->map(fn ($w) => WatchlistDTO::fromModel($w))
            ->all();
    }

    public function show(User $user, int $watchlistId): ?WatchlistDTO
    {
        $watchlist = $this->watchlists->findForUser($watchlistId, $user->id);

        return $watchlist ? WatchlistDTO::fromModel($watchlist) : null;
    }

    public function create(User $user, string $name, bool $isDefault = false): WatchlistDTO
    {
        return DB::transaction(function () use ($user, $name, $isDefault) {
            if ($isDefault) {
                $this->watchlists->clearDefaultFlagForUser($user->id);
            }

            $watchlist = $this->watchlists->create($user->id, $name, $isDefault);

            return WatchlistDTO::fromModel($watchlist);
        });
    }

    public function rename(User $user, int $watchlistId, string $name): ?WatchlistDTO
    {
        $watchlist = $this->watchlists->findForUser($watchlistId, $user->id);
        if (! $watchlist) {
            return null;
        }

        $renamed = $this->watchlists->rename($watchlist, $name);

        return WatchlistDTO::fromModel($renamed);
    }

    public function delete(User $user, int $watchlistId): bool
    {
        $watchlist = $this->watchlists->findForUser($watchlistId, $user->id);
        if (! $watchlist) {
            return false;
        }

        $this->watchlists->delete($watchlist);

        return true;
    }

    public function setInstruments(User $user, int $watchlistId, array $instrumentIds): ?WatchlistDTO
    {
        $watchlist = $this->watchlists->findForUser($watchlistId, $user->id);
        if (! $watchlist) {
            return null;
        }

        $synced = $this->watchlists->syncInstruments($watchlist, array_values($instrumentIds));

        return WatchlistDTO::fromModel($synced);
    }
}
