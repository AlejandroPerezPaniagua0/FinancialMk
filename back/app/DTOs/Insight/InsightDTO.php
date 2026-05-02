<?php

namespace App\DTOs\Insight;

/**
 * Outgoing shape for /api/instruments/{id}/insights.
 *
 * Every metric is nullable because we may not have enough history to
 * compute it (e.g. a newly added instrument). The frontend renders "—"
 * for null fields rather than guessing.
 */
class InsightDTO
{
    public function __construct(
        public readonly int $instrumentId,
        public readonly string $ticker,
        public readonly ?float $volatility30dAnnualized,
        public readonly ?float $maxDrawdown1y,
        public readonly ?float $correlationWithBenchmark,
        public readonly ?string $benchmarkTicker,
        public readonly int $samples,
        public readonly ?string $rangeStart,
        public readonly ?string $rangeEnd,
    ) {}

    public function toArray(): array
    {
        return [
            'instrument_id' => $this->instrumentId,
            'ticker' => $this->ticker,
            'volatility_30d_annualized' => $this->round($this->volatility30dAnnualized, 4),
            'max_drawdown_1y' => $this->round($this->maxDrawdown1y, 4),
            'correlation_with_benchmark' => $this->round($this->correlationWithBenchmark, 4),
            'benchmark_ticker' => $this->benchmarkTicker,
            'samples' => $this->samples,
            'range_start' => $this->rangeStart,
            'range_end' => $this->rangeEnd,
        ];
    }

    private function round(?float $value, int $precision): ?float
    {
        return $value === null ? null : round($value, $precision);
    }
}
