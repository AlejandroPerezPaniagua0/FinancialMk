<?php

namespace App\Repositories;

use App\Models\HistoricalPrice;
use App\Repositories\Interfaces\HistoricalPriceRepositoryInterface;
use Illuminate\Support\Collection;

class HistoricalPriceRepository implements HistoricalPriceRepositoryInterface
{
    public function getByInstrument(int $instrumentId, ?string $from = null, ?string $to = null): Collection
    {
        $query = HistoricalPrice::where('instrument_id', $instrumentId);

        if ($from !== null) {
            $query->where('date', '>=', $from);
        }

        if ($to !== null) {
            $query->where('date', '<=', $to);
        }

        return $query->orderBy('date')->get();
    }
}
