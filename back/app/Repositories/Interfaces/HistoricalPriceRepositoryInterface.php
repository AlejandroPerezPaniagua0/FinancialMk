<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface HistoricalPriceRepositoryInterface
{
    /**
     * Return historical prices for an instrument, optionally filtered by date range.
     *
     * @return Collection<int, \App\Models\HistoricalPrice>
     */
    public function getByInstrument(int $instrumentId, ?string $from = null, ?string $to = null): Collection;
}
