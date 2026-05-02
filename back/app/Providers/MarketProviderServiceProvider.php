<?php

namespace App\Providers;

use App\ApiClient\StooqApiClient;
use App\ApiClient\TwelveDataApiClient;
use App\Services\Interfaces\MarketProviderInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

/**
 * Picks the active market-data provider based on `MARKET_PROVIDER`.
 *
 * Why a dedicated provider class (rather than a binding in AppServiceProvider)?
 *
 *   • Single place to look at when adding a new provider — drop a new
 *     `case 'finnhub'` here and ship.
 *   • Keeps AppServiceProvider focused on cross-cutting concerns
 *     (rate limiters, repository bindings) and out of vendor specifics.
 *   • When something breaks, the stack trace points at this file.
 *
 * The interface contract is: every provider returns the same shapes from
 * fetchQuote / fetchHistoricalPrices. Catalog endpoints (asset classes,
 * currencies, instruments) are best-effort — providers that don't expose a
 * catalog return empty collections and the install relies on seeded data.
 */
class MarketProviderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Concrete TwelveData client (kept here so it stays alongside its
        // sibling Stooq for symmetry).
        $this->app->bind(TwelveDataApiClient::class, function (Container $app) {
            return new TwelveDataApiClient(
                config('services.twelve_data.base_url'),
                config('services.twelve_data.api_key'),
            );
        });

        $this->app->bind(StooqApiClient::class, function () {
            return new StooqApiClient(
                config('market.providers.stooq.base_url', 'https://stooq.com'),
                (int) config('market.providers.stooq.timeout', 10),
            );
        });

        $this->app->bind(MarketProviderInterface::class, function (Container $app) {
            $name = strtolower((string) config('market.provider', 'twelve_data'));

            return match ($name) {
                'twelve_data', 'twelvedata' => $app->make(TwelveDataApiClient::class),
                'stooq' => $app->make(StooqApiClient::class),
                default => throw new RuntimeException(
                    "Unknown MARKET_PROVIDER '{$name}'. Supported: twelve_data, stooq."
                ),
            };
        });
    }
}
