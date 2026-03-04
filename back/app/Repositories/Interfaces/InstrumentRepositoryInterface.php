<?php

namespace App\Repositories\Interfaces;

use Carbon\Carbon;
use Illuminate\Support\Collection;

interface InstrumentRepositoryInterface
{
    /**
     * Get all instruments.
     *
     * @return Collection<int, \App\Models\Instrument>
     */
    public function getAll(): Collection;

    /**
     * Get the latest historical price date for a given instrument.
     *
     * @param int $instrumentId
     * @return Carbon|null
     */
    public function getLatestPriceDate(int $instrumentId): ?Carbon;
}