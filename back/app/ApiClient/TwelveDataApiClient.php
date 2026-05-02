<?php

namespace App\ApiClient;

use App\Services\Interfaces\MarketProviderInterface;
use DateTimeInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;

class TwelveDataApiClient extends BaseApiClient implements MarketProviderInterface
{
    public function __construct(string $baseUrl, string $apiKey)
    {
        parent::__construct($baseUrl, $apiKey);
    }

    /**
     * Get the name of the provider.
     */
    public function name(): string
    {
        return 'twelve_data';
    }

    /**
     * Check availability.
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * Fetch the list of asset classes available (instrument types).
     *
     * @return Collection<int, array{name: string}>
     */
    public function fetchAssetClasses(): Collection
    {
        $payload = $this->get('instrument_type');
        $items = $payload['result'] ?? [];

        return collect($items)->map(function ($item): array {
            return [
                'name' => (string) ($item['name'] ?? $item),
            ];
        });
    }

    /**
     * Fetch the list of currencies available (forex pairs).
     *
     * @return Collection<int, array{name: string, iso_code: string}>
     */
    public function fetchCurrencies(): Collection
    {
        $payload = $this->get('forex_pairs');
        $items = $payload['data'] ?? [];

        return collect($items)->map(function ($item): array {
            return [
                'name' => (string) ($item['name'] ?? $item['currency'] ?? ''),
                'iso_code' => (string) ($item['currency'] ?? $item['symbol'] ?? ''),
            ];
        })->filter(fn (array $item) => $item['iso_code'] !== '');
    }

    /**
     * Fetch instruments filtered by asset class.
     *
     * @param  string|null  $assetClass  Optional filter by asset class name/code.
     * @return Collection<int, array{name: string, ticker: string, asset_class?: string, currency?: string}>
     */
    public function fetchInstruments(?string $assetClass = null): Collection
    {
        $normalizedClass = strtolower($assetClass ?? '');
        $endpoint = match ($normalizedClass) {
            'common stock', 'stocks' => 'stocks',
            'etf' => 'etfs',
            'digital currency', 'crypto' => 'cryptocurrencies',
            'mutual fund' => 'mutual_funds/list',
            default => 'symbol_search',
        };

        $payload = $this->get($endpoint);
        $items = $payload['data'] ?? $payload['result']['list'] ?? [];

        return collect($items)->map(function ($item) use ($normalizedClass): array {
            return [
                'name' => (string) ($item['name'] ?? $item['instrument_name'] ?? ''),
                'ticker' => (string) ($item['symbol'] ?? $item['ticker'] ?? ''),
                'asset_class' => $item['instrument_type'] ?? ($normalizedClass ?: null),
                'currency' => $item['currency'] ?? null,
            ];
        })->filter(fn (array $item) => $item['ticker'] !== '');
    }

    /**
     * Fetch historical prices.
     *
     * @return Collection<int, array{date: string, open: float, high: float, low: float, close: float, adjusted_close: float, volume: int}>
     */
    public function fetchHistoricalPrices(string $ticker, DateTimeInterface $from, DateTimeInterface $to): Collection
    {
        $payload = $this->get('time_series', [
            'symbol' => $ticker,
            'interval' => '1day',
            'start_date' => $from->format('Y-m-d H:i:s'),
            'end_date' => $to->format('Y-m-d H:i:s'),
            'order' => 'ASC',
        ]);

        $items = $payload['values'] ?? [];

        return collect($items)->map(function ($item): array {
            return [
                'date' => (string) ($item['datetime'] ?? $item['date'] ?? ''),
                'open' => (float) ($item['open'] ?? 0),
                'high' => (float) ($item['high'] ?? 0),
                'low' => (float) ($item['low'] ?? 0),
                'close' => (float) ($item['close'] ?? 0),
                'adjusted_close' => (float) ($item['adjusted_close'] ?? $item['close'] ?? 0),
                'volume' => (int) ($item['volume'] ?? 0),
            ];
        })->filter(fn (array $item) => $item['date'] !== '');
    }

    /**
     * Fetch a real-time quote snapshot for a ticker.
     *
     * Returns null on upstream failure so batch callers can continue with
     * the remaining assets rather than aborting the whole request.
     *
     * @return array{
     *   ticker: string,
     *   price: float,
     *   previous_close: float|null,
     *   change: float|null,
     *   change_percent: float|null,
     *   currency: string|null,
     *   timestamp: string
     * }|null
     */
    public function fetchQuote(string $ticker): ?array
    {
        try {
            $payload = $this->get('quote', ['symbol' => $ticker]);
        } catch (\Throwable) {
            return null;
        }

        if (! isset($payload['close']) && ! isset($payload['price'])) {
            return null;
        }

        $price = (float) ($payload['close'] ?? $payload['price'] ?? 0);
        $previousClose = isset($payload['previous_close']) ? (float) $payload['previous_close'] : null;
        $change = isset($payload['change']) ? (float) $payload['change'] : ($previousClose !== null ? $price - $previousClose : null);
        $changePercent = isset($payload['percent_change'])
            ? (float) $payload['percent_change']
            : ($previousClose !== null && $previousClose != 0.0 ? (($price - $previousClose) / $previousClose) * 100 : null);

        return [
            'ticker' => $ticker,
            'price' => $price,
            'previous_close' => $previousClose,
            'change' => $change,
            'change_percent' => $changePercent,
            'currency' => isset($payload['currency']) ? (string) $payload['currency'] : null,
            'timestamp' => (string) ($payload['datetime'] ?? $payload['timestamp'] ?? now()->toIso8601String()),
        ];
    }

    /**
     * Authorize the request by injecting the API Key.
     * Reference: Authentication by query parameter
     */
    protected function authorize(PendingRequest $request): PendingRequest
    {
        return $request->withQueryParameters([
            'apikey' => $this->apiKey,
        ]);
    }
}
