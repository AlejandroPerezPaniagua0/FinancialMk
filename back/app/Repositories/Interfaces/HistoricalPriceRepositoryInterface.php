<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface HistoricalPriceRepositoryInterface
{
    /**
     * Bulk upsert historical prices for a given instrument.
     * Uses the ['instrument_id', 'date'] unique constraint to avoid duplicates.
     *
     * @param int $instrumentId
     * @param Collection<int, array{date: string, open: float, high: float, low: float, close: float, adjusted_close: float, volume: int}> $prices
     * @return int Number of rows affected.
     */
    public function upsertPrices(int $instrumentId, Collection $prices): int;
}