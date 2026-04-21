<?php

namespace App\Services\Interfaces;

use Illuminate\Support\Collection;

interface MarketPollingServiceInterface
{
    /**
     * Fetch a real-time quote for every provided instrument id, honoring
     * the per-ticker throttle. Tickers whose cached quote is still fresh
     * are served from cache; stale entries trigger a single upstream call.
     *
     * @param  int[]  $instrumentIds
     * @return Collection<int, array{
     *   instrument_id: int,
     *   ticker: string,
     *   price: float|null,
     *   previous_close: float|null,
     *   change: float|null,
     *   change_percent: float|null,
     *   currency: string|null,
     *   fetched_at: string,
     *   cached: bool,
     *   next_refresh_in: int
     * }>
     */
    public function quotesFor(array $instrumentIds): Collection;
}
