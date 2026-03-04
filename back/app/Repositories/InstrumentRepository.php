<?php

namespace App\Repositories;

use App\Models\HistoricalPrice;
use App\Models\Instrument;
use App\Repositories\Interfaces\InstrumentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InstrumentRepository implements InstrumentRepositoryInterface
{
    /**
     * Get all instruments.
     *
     * @return Collection<int, Instrument>
     */
    public function getAll(): Collection
    {
        return Instrument::all();
    }

    /**
     * Get the latest historical price date for a given instrument.
     *
     * @param int $instrumentId
     * @return Carbon|null
     */
    public function getLatestPriceDate(int $instrumentId): ?Carbon
    {
        $latestDate = HistoricalPrice::where('instrument_id', $instrumentId)
            ->max('date');

        return $latestDate ?Carbon::parse($latestDate) : null;
    }
}