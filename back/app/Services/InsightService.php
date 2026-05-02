<?php

namespace App\Services;

use App\DTOs\Insight\InsightDTO;
use App\Models\Instrument;
use App\Repositories\Interfaces\HistoricalPriceRepositoryInterface;
use App\Repositories\Interfaces\InstrumentRepositoryInterface;
use App\Services\Interfaces\InsightServiceInterface;
use Carbon\CarbonImmutable;

/**
 * Computes risk/return statistics from locally-stored OHLCV. Pure math,
 * zero network — that's the point: when we say "no black box" we mean it.
 *
 * Definitions
 * -----------
 * • Volatility (30d annualized): stddev of the last 30 daily log-returns,
 *   scaled by sqrt(252) to annualize. Returned as a fraction (0.18 = 18%).
 *
 * • Max drawdown (1y): largest peak-to-trough decline in adjusted close
 *   over the trailing 365 days. Expressed as a non-positive fraction
 *   (-0.27 = -27%).
 *
 * • Correlation with benchmark: Pearson correlation of daily log-returns
 *   between the instrument and the benchmark, computed over the
 *   overlapping date range of their data.
 */
class InsightService implements InsightServiceInterface
{
    private const DEFAULT_BENCHMARK_TICKER = 'SPY';

    private const TRADING_DAYS_PER_YEAR = 252;

    private const VOL_WINDOW_DAYS = 30;

    public function __construct(
        private readonly InstrumentRepositoryInterface $instruments,
        private readonly HistoricalPriceRepositoryInterface $prices,
    ) {}

    public function forInstrument(int $instrumentId, ?string $benchmarkTicker = null): ?InsightDTO
    {
        $instrument = $this->instruments->findById($instrumentId);
        if (! $instrument) {
            return null;
        }

        $oneYearAgo = CarbonImmutable::now()->subYear()->toDateString();
        $today = CarbonImmutable::now()->toDateString();

        $rows = $this->prices->getByInstrument($instrumentId, $oneYearAgo, $today);
        if ($rows->isEmpty()) {
            return new InsightDTO(
                instrumentId: $instrumentId,
                ticker: $instrument->ticker,
                volatility30dAnnualized: null,
                maxDrawdown1y: null,
                correlationWithBenchmark: null,
                benchmarkTicker: $benchmarkTicker ?? self::DEFAULT_BENCHMARK_TICKER,
                samples: 0,
                rangeStart: null,
                rangeEnd: null,
            );
        }

        $closes = $rows->pluck('adjusted_close')->map(fn ($v) => (float) $v)->all();
        $returns = $this->logReturns($closes);

        $volatility = $this->annualizedVolatility(array_slice($returns, -self::VOL_WINDOW_DAYS));
        $drawdown = $this->maxDrawdown($closes);

        $benchmark = $benchmarkTicker ?? self::DEFAULT_BENCHMARK_TICKER;
        $correlation = $this->correlationVsBenchmark($instrument->ticker, $benchmark, $oneYearAgo, $today);

        return new InsightDTO(
            instrumentId: $instrumentId,
            ticker: $instrument->ticker,
            volatility30dAnnualized: $volatility,
            maxDrawdown1y: $drawdown,
            correlationWithBenchmark: $correlation,
            benchmarkTicker: $benchmark,
            samples: count($closes),
            rangeStart: (string) $rows->first()->date,
            rangeEnd: (string) $rows->last()->date,
        );
    }

    public function correlationMatrix(array $instrumentIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $instrumentIds)));
        if (count($ids) < 2) {
            return ['tickers' => [], 'matrix' => [], 'range_start' => null, 'range_end' => null, 'samples' => 0];
        }

        $oneYearAgo = CarbonImmutable::now()->subYear()->toDateString();
        $today = CarbonImmutable::now()->toDateString();

        // Build aligned series keyed by date so we can find overlap cheaply.
        $tickers = [];
        $closesByDay = [];
        foreach ($ids as $id) {
            $instrument = $this->instruments->findById($id);
            if (! $instrument) {
                continue;
            }
            $tickers[$id] = $instrument->ticker;
            $closesByDay[$id] = $this->prices->getByInstrument($id, $oneYearAgo, $today)
                ->mapWithKeys(fn ($row) => [(string) $row->date => (float) $row->adjusted_close])
                ->all();
        }

        $commonDates = $this->intersectDates($closesByDay);
        sort($commonDates);

        $returnsById = [];
        foreach ($closesByDay as $id => $byDay) {
            $aligned = array_map(fn ($d) => $byDay[$d], $commonDates);
            $returnsById[$id] = $this->logReturns($aligned);
        }

        $orderedIds = array_values(array_keys($tickers));
        $size = count($orderedIds);
        $matrix = array_fill(0, $size, array_fill(0, $size, null));

        for ($i = 0; $i < $size; $i++) {
            for ($j = 0; $j < $size; $j++) {
                if ($i === $j) {
                    $matrix[$i][$j] = 1.0;

                    continue;
                }
                $matrix[$i][$j] = $this->pearson(
                    $returnsById[$orderedIds[$i]] ?? [],
                    $returnsById[$orderedIds[$j]] ?? [],
                );
            }
        }

        return [
            'tickers' => array_map(fn ($id) => $tickers[$id], $orderedIds),
            'matrix' => $matrix,
            'range_start' => $commonDates[0] ?? null,
            'range_end' => end($commonDates) ?: null,
            'samples' => count($commonDates),
        ];
    }

    /**
     * Pearson correlation between this instrument's returns and the
     * benchmark's, over their overlapping date range.
     */
    private function correlationVsBenchmark(string $ticker, string $benchmarkTicker, string $from, string $to): ?float
    {
        if (strcasecmp($ticker, $benchmarkTicker) === 0) {
            return 1.0;
        }

        $benchmark = Instrument::where('ticker', $benchmarkTicker)->first();
        if (! $benchmark) {
            return null;
        }

        $instrument = Instrument::where('ticker', $ticker)->first();
        if (! $instrument) {
            return null;
        }

        $aSeries = $this->prices->getByInstrument($instrument->id, $from, $to)
            ->mapWithKeys(fn ($row) => [(string) $row->date => (float) $row->adjusted_close])
            ->all();
        $bSeries = $this->prices->getByInstrument($benchmark->id, $from, $to)
            ->mapWithKeys(fn ($row) => [(string) $row->date => (float) $row->adjusted_close])
            ->all();

        $commonDates = array_values(array_intersect(array_keys($aSeries), array_keys($bSeries)));
        sort($commonDates);
        if (count($commonDates) < 30) {
            return null;
        }

        $aAligned = array_map(fn ($d) => $aSeries[$d], $commonDates);
        $bAligned = array_map(fn ($d) => $bSeries[$d], $commonDates);

        return $this->pearson($this->logReturns($aAligned), $this->logReturns($bAligned));
    }

    /**
     * @param  array<int, float>  $closes  In chronological order.
     * @return array<int, float> length = count($closes) - 1
     */
    private function logReturns(array $closes): array
    {
        $returns = [];
        for ($i = 1; $i < count($closes); $i++) {
            if ($closes[$i - 1] <= 0 || $closes[$i] <= 0) {
                continue;
            }
            $returns[] = log($closes[$i] / $closes[$i - 1]);
        }

        return $returns;
    }

    /** Sample stddev (n-1) of the slice, scaled by sqrt(252). */
    private function annualizedVolatility(array $returns): ?float
    {
        $n = count($returns);
        if ($n < 2) {
            return null;
        }

        $mean = array_sum($returns) / $n;
        $sumSq = 0.0;
        foreach ($returns as $r) {
            $sumSq += ($r - $mean) ** 2;
        }
        $stddev = sqrt($sumSq / ($n - 1));

        return $stddev * sqrt(self::TRADING_DAYS_PER_YEAR);
    }

    /**
     * Largest peak-to-trough decline in the price series. Negative or zero.
     */
    private function maxDrawdown(array $closes): ?float
    {
        if (count($closes) < 2) {
            return null;
        }

        $peak = $closes[0];
        $max = 0.0;
        foreach ($closes as $price) {
            if ($price > $peak) {
                $peak = $price;
            }
            if ($peak > 0) {
                $drawdown = ($price - $peak) / $peak;
                if ($drawdown < $max) {
                    $max = $drawdown;
                }
            }
        }

        return $max;
    }

    /**
     * Pearson correlation coefficient. Returns null when either series is
     * too short or has no variance.
     *
     * @param  array<int, float>  $a
     * @param  array<int, float>  $b
     */
    private function pearson(array $a, array $b): ?float
    {
        $n = min(count($a), count($b));
        if ($n < 2) {
            return null;
        }

        $a = array_slice($a, 0, $n);
        $b = array_slice($b, 0, $n);

        $meanA = array_sum($a) / $n;
        $meanB = array_sum($b) / $n;

        $covariance = 0.0;
        $varA = 0.0;
        $varB = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $da = $a[$i] - $meanA;
            $db = $b[$i] - $meanB;
            $covariance += $da * $db;
            $varA += $da * $da;
            $varB += $db * $db;
        }

        if ($varA <= 0.0 || $varB <= 0.0) {
            return null;
        }

        return $covariance / sqrt($varA * $varB);
    }

    /**
     * Intersect the date keys of multiple [date => price] maps.
     *
     * @param  array<int, array<string, float>>  $closesByDay
     * @return array<int, string>
     */
    private function intersectDates(array $closesByDay): array
    {
        $sets = array_map(fn ($s) => array_keys($s), $closesByDay);
        if (empty($sets)) {
            return [];
        }
        $first = array_shift($sets);
        foreach ($sets as $other) {
            $first = array_values(array_intersect($first, $other));
        }

        return $first;
    }
}
