<?php

namespace App\ApiClient;

use App\Services\Interfaces\MarketProviderInterface;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Stooq market-data provider — free, public, no API key.
 *
 * Stooq exposes CSV endpoints for end-of-day OHLCV and a "light" quote
 * stream. We do NOT extend BaseApiClient because that abstraction is
 * JSON-shaped; CSV warrants its own surface.
 *
 *   • Historical: https://stooq.com/q/d/l/?s={symbol}&i=d&d1=YYYYMMDD&d2=YYYYMMDD
 *   • Quote:      https://stooq.com/q/l/?s={symbol}&f=sd2t2ohlcv&h&e=csv
 *
 * Catalog endpoints (asset classes, currencies, instruments) are not
 * supported — Stooq has no clean catalog API. Users who select
 * MARKET_PROVIDER=stooq rely on the seeded lookup tables instead.
 *
 * Symbol mapping
 * --------------
 * Stooq uses suffixed tickers: AAPL.US, ASML.NL, BTCUSD, EURUSD. We
 * normalize the project's canonical formats (e.g. "BTC-USD", "EUR/USD")
 * into Stooq form. Tickers we don't recognise default to ".US" — the
 * single largest universe — which is rarely wrong for the seeded set.
 */
class StooqApiClient implements MarketProviderInterface
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly int $timeoutSeconds = 10,
    ) {}

    public function name(): string
    {
        return 'stooq';
    }

    public function isAvailable(): bool
    {
        try {
            return Http::timeout(3)
                ->head($this->baseUrl)
                ->successful();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Stooq has no catalog API. Returns empty — the seeded lookup table
     * is the source of truth for installs running on Stooq.
     */
    public function fetchAssetClasses(): Collection
    {
        return collect();
    }

    /** @see fetchAssetClasses() — same rationale. */
    public function fetchCurrencies(): Collection
    {
        return collect();
    }

    /** @see fetchAssetClasses() — same rationale. */
    public function fetchInstruments(?string $assetClass = null): Collection
    {
        return collect();
    }

    public function fetchHistoricalPrices(string $ticker, DateTimeInterface $from, DateTimeInterface $to): Collection
    {
        $symbol = $this->normalizeTicker($ticker);
        $csv = $this->fetchCsv('/q/d/l/', [
            's' => $symbol,
            'i' => 'd',                          // daily
            'd1' => $from->format('Ymd'),
            'd2' => $to->format('Ymd'),
        ]);

        return $this->parseHistoricalCsv($csv);
    }

    public function fetchQuote(string $ticker): ?array
    {
        $symbol = $this->normalizeTicker($ticker);

        try {
            $csv = $this->fetchCsv('/q/l/', [
                's' => $symbol,
                'f' => 'sd2t2ohlcvn',  // symbol, date, time, OHLCV, name
                'h' => '',             // include header row
                'e' => 'csv',
            ]);
        } catch (Throwable) {
            return null;
        }

        $rows = $this->parseCsvRows($csv);
        if (count($rows) < 2) {
            return null;
        }

        // Row 0 is the header, row 1 is the quote.
        // Stooq returns "N/D" for missing fields when the symbol is unknown.
        $header = array_map('strtolower', $rows[0]);
        $row = array_combine($header, $rows[1]);
        if ($row === false || ($row['close'] ?? 'N/D') === 'N/D') {
            return null;
        }

        $price = (float) $row['close'];
        $open = isset($row['open']) ? (float) $row['open'] : null;

        return [
            'ticker' => $ticker,
            'price' => $price,
            // Stooq's free CSV does not expose previous_close; we approximate
            // with `open` so the comparison view has *something* to render.
            'previous_close' => $open,
            'change' => $open !== null ? $price - $open : null,
            'change_percent' => $open !== null && $open !== 0.0 ? (($price - $open) / $open) * 100 : null,
            'currency' => null,
            'timestamp' => trim(($row['date'] ?? '').' '.($row['time'] ?? '')) ?: now()->toIso8601String(),
        ];
    }

    /**
     * Normalize ticker shapes used elsewhere in the app to Stooq's form.
     *
     * Examples:
     *   AAPL       → AAPL.US
     *   BRK.B      → BRK-B.US
     *   ASML       → ASML.US (Stooq has the US listing)
     *   BTC-USD    → BTCUSD
     *   EUR/USD    → EURUSD
     *   SAN.MC     → SAN.MC (already Stooq-shaped)
     */
    protected function normalizeTicker(string $ticker): string
    {
        $t = strtoupper(trim($ticker));

        // Crypto: ABC-USD → ABCUSD
        if (preg_match('/^[A-Z]+-[A-Z]{3}$/', $t)) {
            return str_replace('-', '', $t);
        }

        // Forex: EUR/USD → EURUSD
        if (str_contains($t, '/')) {
            return str_replace('/', '', $t);
        }

        // Already exchange-suffixed (e.g. ASML.NL, SAN.MC) → leave alone.
        if (preg_match('/\.[A-Z]{2,3}$/', $t)) {
            return $t;
        }

        // Multi-class share like BRK.B → Stooq writes BRK-B.US
        if (str_contains($t, '.')) {
            return str_replace('.', '-', $t).'.US';
        }

        return $t.'.US';
    }

    /**
     * @param  array<string, string>  $params
     */
    private function fetchCsv(string $path, array $params): string
    {
        $response = Http::timeout($this->timeoutSeconds)
            ->withHeaders(['Accept' => 'text/csv'])
            ->get(rtrim($this->baseUrl, '/').$path, $params);

        $response->throw();

        return (string) $response->body();
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function parseCsvRows(string $csv): array
    {
        $rows = [];
        foreach (preg_split("/\r\n|\n|\r/", trim($csv)) ?: [] as $line) {
            if ($line === '') {
                continue;
            }
            $rows[] = str_getcsv($line);
        }

        return $rows;
    }

    /**
     * @return Collection<int, array{
     *   date: string, open: float, high: float, low: float, close: float,
     *   adjusted_close: float, volume: int
     * }>
     */
    private function parseHistoricalCsv(string $csv): Collection
    {
        $rows = $this->parseCsvRows($csv);
        if (count($rows) < 2) {
            return collect();
        }

        $header = array_map('strtolower', array_shift($rows));
        $idx = array_flip($header);

        $required = ['date', 'open', 'high', 'low', 'close'];
        foreach ($required as $col) {
            if (! isset($idx[$col])) {
                return collect();
            }
        }

        return collect($rows)
            ->filter(fn ($row) => isset($row[$idx['date']]) && $row[$idx['date']] !== 'N/D')
            ->map(function (array $row) use ($idx): array {
                $close = (float) ($row[$idx['close']] ?? 0);

                return [
                    'date' => (string) $row[$idx['date']],
                    'open' => (float) ($row[$idx['open']] ?? 0),
                    'high' => (float) ($row[$idx['high']] ?? 0),
                    'low' => (float) ($row[$idx['low']] ?? 0),
                    'close' => $close,
                    'adjusted_close' => $close, // Stooq doesn't expose adjusted; mirror close.
                    'volume' => (int) ($row[$idx['volume'] ?? -1] ?? 0),
                ];
            })
            ->values();
    }
}
