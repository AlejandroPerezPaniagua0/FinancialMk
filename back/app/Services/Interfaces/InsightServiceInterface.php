<?php

namespace App\Services\Interfaces;

use App\DTOs\Insight\InsightDTO;

interface InsightServiceInterface
{
    /**
     * Compute key risk/return statistics for an instrument from its
     * locally-stored historical prices. Returns null when the instrument
     * doesn't exist.
     */
    public function forInstrument(int $instrumentId, ?string $benchmarkTicker = null): ?InsightDTO;

    /**
     * Build an N×N Pearson correlation matrix for the given instruments,
     * computed from the overlapping date range of their daily log returns.
     *
     * @param  array<int, int>  $instrumentIds
     * @return array{
     *   tickers:        array<int, string>,
     *   matrix:         array<int, array<int, float|null>>,
     *   range_start:    string|null,
     *   range_end:      string|null,
     *   samples:        int
     * }
     */
    public function correlationMatrix(array $instrumentIds): array;
}
