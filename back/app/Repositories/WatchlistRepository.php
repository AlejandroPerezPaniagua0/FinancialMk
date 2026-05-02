<?php

namespace App\Repositories;

use App\Models\Watchlist;
use App\Repositories\Interfaces\WatchlistRepositoryInterface;
use Illuminate\Support\Collection;

class WatchlistRepository implements WatchlistRepositoryInterface
{
    public function listForUser(int $userId): Collection
    {
        return Watchlist::with(['instruments.assetClass', 'instruments.currency'])
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function findForUser(int $watchlistId, int $userId): ?Watchlist
    {
        return Watchlist::with(['instruments.assetClass', 'instruments.currency'])
            ->where('user_id', $userId)
            ->find($watchlistId);
    }

    public function create(int $userId, string $name, bool $isDefault = false): Watchlist
    {
        return Watchlist::create([
            'user_id'    => $userId,
            'name'       => $name,
            'is_default' => $isDefault,
        ])->load(['instruments.assetClass', 'instruments.currency']);
    }

    public function rename(Watchlist $watchlist, string $name): Watchlist
    {
        $watchlist->update(['name' => $name]);
        return $watchlist->load(['instruments.assetClass', 'instruments.currency']);
    }

    public function delete(Watchlist $watchlist): void
    {
        $watchlist->delete();
    }

    public function syncInstruments(Watchlist $watchlist, array $instrumentIds): Watchlist
    {
        // Build [instrument_id => ['position' => N]] payload so the pivot
        // ordering matches the array order the caller sent.
        $payload = [];
        foreach (array_values($instrumentIds) as $position => $id) {
            $payload[(int) $id] = ['position' => $position];
        }

        $watchlist->instruments()->sync($payload);

        return $watchlist->load(['instruments.assetClass', 'instruments.currency']);
    }

    public function clearDefaultFlagForUser(int $userId): void
    {
        Watchlist::where('user_id', $userId)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }
}
