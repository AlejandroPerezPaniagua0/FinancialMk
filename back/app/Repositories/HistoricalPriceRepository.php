<?php

namespace App\Repositories;

use App\Models\HistoricalPrice;
use App\Repositories\Interfaces\HistoricalPriceRepositoryInterface;
use Illuminate\Support\Collection;

class HistoricalPriceRepository implements HistoricalPriceRepositoryInterface
{
    /**
     * Bulk upsert historical prices for a given instrument.
     *
     * @param int $instrumentId
     * @param Collection<int, array{date: string, open: float, high: float, low: float, close: float, adjusted_close: float, volume: int}> $prices
     * @return int Number of rows affected.
     */
    public function upsertPrices(int $instrumentId, Collection $prices): int
    {
        if ($prices->isEmpty()) {
            return 0;
        }

        $rows = $prices->map(fn(array $price) => [
            'instrument_id' => $instrumentId,
            'date' => $price['date'],
            'open' => $price['open'],
            'high' => $price['high'],
            'low' => $price['low'],
            'close' => $price['close'],
            'adjusted_close' => $price['adjusted_close'],
            'volume' => $price['volume'],
        ])->toArray();

        return HistoricalPrice::upsert(
            $rows,
            ['instrument_id', 'date'],
            ['open', 'high', 'low', 'close', 'adjusted_close', 'volume']
        );
    }
}