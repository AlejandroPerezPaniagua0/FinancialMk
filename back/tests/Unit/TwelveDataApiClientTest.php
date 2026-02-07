<?php

namespace Tests\Unit;

use App\ApiClient\TwelveDataApiClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TwelveDataApiClientTest extends TestCase
{
    private const BASE_URL = 'https://api.twelvedata.com';
    private const API_KEY = 'test-key';

    public function test_fetches_asset_classes(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'instrument_type')) {
                return Http::response([
                    'result' => [
                        ['name' => 'Stocks'],
                        'ETFs',
                    ],
                ]);
            }

            return Http::response([], 404);
        });

        $client = new TwelveDataApiClient(self::BASE_URL, self::API_KEY);
        $result = $client->fetchAssetClasses()->toArray();

        $this->assertSame([
            ['name' => 'Stocks'],
            ['name' => 'ETFs'],
        ], $result);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'apikey=' . self::API_KEY));
    }

    public function test_fetches_currencies(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'forex_pairs')) {
                return Http::response([
                    'data' => [
                        ['name' => 'US Dollar', 'currency' => 'USD'],
                        ['currency' => 'EUR'],
                    ],
                ]);
            }

            return Http::response([], 404);
        });

        $client = new TwelveDataApiClient(self::BASE_URL, self::API_KEY);
        $result = $client->fetchCurrencies()->toArray();

        $this->assertSame([
            ['name' => 'US Dollar', 'iso_code' => 'USD'],
            ['name' => 'EUR', 'iso_code' => 'EUR'],
        ], $result);
    }

    public function test_fetches_instruments_by_asset_class(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'stocks')) {
                return Http::response([
                    'data' => [
                        ['name' => 'Apple Inc.', 'symbol' => 'AAPL', 'currency' => 'USD'],
                        ['name' => 'Tesla', 'symbol' => 'TSLA', 'currency' => 'USD'],
                    ],
                ]);
            }

            return Http::response([], 404);
        });

        $client = new TwelveDataApiClient(self::BASE_URL, self::API_KEY);
        $result = $client->fetchInstruments('stocks')->toArray();

        $this->assertSame([
            [
                'name' => 'Apple Inc.',
                'ticker' => 'AAPL',
                'asset_class' => 'stocks',
                'currency' => 'USD',
            ],
            [
                'name' => 'Tesla',
                'ticker' => 'TSLA',
                'asset_class' => 'stocks',
                'currency' => 'USD',
            ],
        ], $result);
    }

    public function test_fetches_historical_prices(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'time_series')) {
                return Http::response([
                    'values' => [
                        [
                            'datetime' => '2024-01-02 00:00:00',
                            'open' => '100.10',
                            'high' => '105.50',
                            'low' => '99.80',
                            'close' => '104.00',
                            'volume' => '120000',
                        ],
                    ],
                ]);
            }

            return Http::response([], 404);
        });

        $client = new TwelveDataApiClient(self::BASE_URL, self::API_KEY);
        $result = $client
            ->fetchHistoricalPrices('AAPL', new \DateTimeImmutable('2024-01-01'), new \DateTimeImmutable('2024-01-31'))
            ->toArray();

        $this->assertSame([
            [
                'date' => '2024-01-02 00:00:00',
                'open' => 100.1,
                'high' => 105.5,
                'low' => 99.8,
                'close' => 104.0,
                'adjusted_close' => 104.0,
                'volume' => 120000,
            ],
        ], $result);
    }
}
