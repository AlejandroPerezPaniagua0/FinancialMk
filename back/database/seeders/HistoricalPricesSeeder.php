<?php

namespace Database\Seeders;

use App\Models\HistoricalPrice;
use App\Models\Instrument;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Generates ~2 years of synthetic daily OHLCV per instrument so a fresh
 * install has charts that *look* like real markets out of the box.
 *
 * IMPORTANT — these prices are SYNTHETIC and DETERMINISTIC, not real:
 *   • Generated locally with a seeded random walk (no network calls).
 *   • CI-stable: same input → same output. Tests can rely on that.
 *   • The `starting_price` from instruments.json anchors each ticker to
 *     a realistic order of magnitude.
 *
 * Why not call TwelveData/Stooq during seeding?
 *   • Network in CI = flake. Hard pass.
 *   • Free tiers rate-limit; mass seeding torches quotas.
 *   • Real historical data is hundreds of MB across the fixture set.
 *
 * Why a seeded random walk and not a JSON dump of historical OHLCV?
 *   • A 2y JSON dump for ~50 tickers is ~50 MB. Repo bloat.
 *   • Generated curves are good enough for visual demos and analytics
 *     development. When the user adds an API key, the polling layer
 *     starts overwriting today's row with real data.
 *
 * Idempotent: uses upsert keyed on (instrument_id, date).
 *
 * Performance: chunked 1000 rows per insert. Postgres + Redis stack
 * seeds the full set in ~5–10 s on a laptop.
 */
class HistoricalPricesSeeder extends Seeder
{
    /** Number of trading days to generate. ~252 per year. */
    private const TRADING_DAYS = 504;

    /** Asset-class-specific volatility (daily stddev of log return). */
    private const DAILY_VOL = [
        'Stock'  => 0.018,
        'ETF'    => 0.011,
        'Crypto' => 0.045,
        'Forex'  => 0.005,
        'Index'  => 0.012,
    ];

    /** Annualized drift (positive = secular uptrend). */
    private const ANNUAL_DRIFT = [
        'Stock'  => 0.10,
        'ETF'    => 0.08,
        'Crypto' => 0.20,
        'Forex'  => 0.00,
        'Index'  => 0.07,
    ];

    public function run(): void
    {
        $fixturePath = database_path('fixtures/instruments.json');
        $payload     = json_decode(file_get_contents($fixturePath), true, flags: JSON_THROW_ON_ERROR);
        $startingPriceByTicker = collect($payload['instruments'] ?? [])
            ->keyBy('ticker')
            ->map(fn ($row) => (float) $row['starting_price'])
            ->all();

        $instruments = Instrument::with('assetClass')->get();

        $this->command?->info("Generating ~{$this->getEstimatedRowCount($instruments->count())} synthetic historical prices...");

        foreach ($instruments as $instrument) {
            $startPrice = $startingPriceByTicker[$instrument->ticker] ?? 100.0;
            $assetClass = $instrument->assetClass->name ?? 'Stock';

            $rows = $this->generateSeries($instrument->id, $instrument->ticker, $startPrice, $assetClass);

            foreach (array_chunk($rows, 1000) as $chunk) {
                DB::table('historical_prices')->upsert(
                    $chunk,
                    ['instrument_id', 'date'],
                    ['open', 'high', 'low', 'close', 'adjusted_close', 'volume', 'updated_at']
                );
            }
        }

        $this->command?->info('Historical prices seeded.');
    }

    /**
     * Build a deterministic random-walk price series ending at `startPrice`
     * on the most recent trading day. We work backwards in price-space then
     * reverse-order so the chart climbs/falls into "today" naturally.
     *
     * @return array<int, array<string, mixed>>
     */
    private function generateSeries(int $instrumentId, string $ticker, float $startPrice, string $assetClass): array
    {
        $vol   = self::DAILY_VOL[$assetClass]   ?? 0.018;
        $drift = (self::ANNUAL_DRIFT[$assetClass] ?? 0.08) / 252.0; // daily

        // Deterministic seed per ticker so re-runs produce the same curve.
        $seed = crc32($ticker);
        mt_srand($seed);

        // Walk backward from startPrice for TRADING_DAYS using log-returns.
        // newer day → older day, so we'll reverse at the end.
        $prices = [$startPrice];
        for ($i = 1; $i < self::TRADING_DAYS; $i++) {
            $z      = $this->boxMuller();
            $logRet = $drift + $vol * $z;
            // walking back: divide by exp(logRet) instead of multiply
            $prev   = end($prices) / exp($logRet);
            $prices[] = max(0.01, $prev);
        }
        $prices = array_reverse($prices);

        $today = CarbonImmutable::now()->startOfDay();
        $rows  = [];
        $now   = now();

        for ($i = 0; $i < self::TRADING_DAYS; $i++) {
            $date  = $this->subtractTradingDays($today, self::TRADING_DAYS - 1 - $i)->toDateString();
            $close = $prices[$i];

            // Build O/H/L around close with reasonable intraday range.
            $intraday = $vol * 0.6;
            $open     = $close * (1 + $intraday * ($this->mtRandUniform() - 0.5));
            $high     = max($open, $close) * (1 + $intraday * $this->mtRandUniform() * 0.5);
            $low      = min($open, $close) * (1 - $intraday * $this->mtRandUniform() * 0.5);

            $rows[] = [
                'instrument_id'  => $instrumentId,
                'date'           => $date,
                'open'           => round($open,  6),
                'high'           => round($high,  6),
                'low'            => round($low,   6),
                'close'          => round($close, 6),
                'adjusted_close' => round($close, 6),
                'volume'         => $this->syntheticVolume($assetClass),
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        return $rows;
    }

    /** Standard normal via Box-Muller using mt_rand for determinism. */
    private function boxMuller(): float
    {
        $u1 = max(1e-10, $this->mtRandUniform());
        $u2 = $this->mtRandUniform();
        return sqrt(-2.0 * log($u1)) * cos(2.0 * M_PI * $u2);
    }

    private function mtRandUniform(): float
    {
        return mt_rand() / mt_getrandmax();
    }

    /**
     * Skip weekends. Deliberately ignores holidays — close enough for
     * synthetic demo data.
     */
    private function subtractTradingDays(CarbonImmutable $from, int $days): CarbonImmutable
    {
        $cursor = $from;
        $left   = $days;
        while ($left > 0) {
            $cursor = $cursor->subDay();
            if (! $cursor->isWeekend()) {
                $left--;
            }
        }
        return $cursor;
    }

    private function syntheticVolume(string $assetClass): int
    {
        return match ($assetClass) {
            'Crypto' => mt_rand(50_000_000,    500_000_000),
            'Forex'  => mt_rand(100_000_000, 5_000_000_000),
            'ETF'    => mt_rand(5_000_000,    80_000_000),
            'Index'  => 0,
            default  => mt_rand(1_000_000,    50_000_000),
        };
    }

    private function getEstimatedRowCount(int $instruments): int
    {
        return $instruments * self::TRADING_DAYS;
    }
}
