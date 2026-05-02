<?php

namespace Tests\Unit;

use App\ApiClient\StooqApiClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Unit tests for the Stooq CSV provider.
 *
 * We assert symbol normalization, CSV parsing edge cases, and that catalog
 * methods short-circuit (Stooq has no catalog endpoint by design).
 */
class StooqApiClientTest extends TestCase
{
    private const BASE_URL = 'https://stooq.com';

    public function test_name_returns_stooq(): void
    {
        $this->assertSame('stooq', (new StooqApiClient(self::BASE_URL))->name());
    }

    public function test_catalog_methods_return_empty_collections(): void
    {
        $client = new StooqApiClient(self::BASE_URL);

        $this->assertTrue($client->fetchAssetClasses()->isEmpty());
        $this->assertTrue($client->fetchCurrencies()->isEmpty());
        $this->assertTrue($client->fetchInstruments()->isEmpty());
    }

    public function test_fetches_historical_prices_from_csv(): void
    {
        Http::fake([
            'stooq.com/q/d/l/*' => Http::response(
                "Date,Open,High,Low,Close,Volume\n"
                ."2024-01-02,100.10,105.50,99.80,104.00,120000\n"
                ."2024-01-03,104.00,106.20,103.40,105.80,98000\n"
            ),
        ]);

        $client = new StooqApiClient(self::BASE_URL);
        $result = $client->fetchHistoricalPrices(
            'AAPL',
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-01-31'),
        )->toArray();

        $this->assertSame([
            [
                'date' => '2024-01-02',
                'open' => 100.10,
                'high' => 105.50,
                'low' => 99.80,
                'close' => 104.00,
                'adjusted_close' => 104.00,
                'volume' => 120000,
            ],
            [
                'date' => '2024-01-03',
                'open' => 104.00,
                'high' => 106.20,
                'low' => 103.40,
                'close' => 105.80,
                'adjusted_close' => 105.80,
                'volume' => 98000,
            ],
        ], $result);

        Http::assertSent(fn ($request) => str_contains($request->url(), 's=AAPL.US')
            && str_contains($request->url(), 'd1=20240101')
            && str_contains($request->url(), 'd2=20240131'));
    }

    public function test_fetches_quote_from_light_csv(): void
    {
        Http::fake([
            'stooq.com/q/l/*' => Http::response(
                "Symbol,Date,Time,Open,High,Low,Close,Volume,Name\n"
                ."AAPL.US,2024-04-12,22:00:00,190.00,195.50,189.40,192.80,55000000,APPLE\n"
            ),
        ]);

        $client = new StooqApiClient(self::BASE_URL);
        $quote = $client->fetchQuote('AAPL');

        $this->assertNotNull($quote);
        $this->assertSame('AAPL', $quote['ticker']);
        $this->assertSame(192.80, $quote['price']);
        $this->assertSame(190.00, $quote['previous_close']);
        $this->assertEqualsWithDelta(2.80, $quote['change'], 0.001);
        $this->assertEqualsWithDelta(1.4736, $quote['change_percent'], 0.001);
    }

    public function test_returns_null_quote_when_symbol_unknown(): void
    {
        Http::fake([
            'stooq.com/q/l/*' => Http::response(
                "Symbol,Date,Time,Open,High,Low,Close,Volume,Name\n"
                ."ZZZ.US,N/D,N/D,N/D,N/D,N/D,N/D,N/D,\n"
            ),
        ]);

        $this->assertNull((new StooqApiClient(self::BASE_URL))->fetchQuote('ZZZ'));
    }

    /**
     * @dataProvider tickerNormalizationCases
     */
    public function test_normalizes_tickers_to_stooq_form(string $input, string $expectedSymbolFragment): void
    {
        Http::fake([
            'stooq.com/q/d/l/*' => Http::response("Date,Open,High,Low,Close,Volume\n"),
        ]);

        $client = new StooqApiClient(self::BASE_URL);
        $client->fetchHistoricalPrices(
            $input,
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-01-02'),
        );

        Http::assertSent(fn ($request) => str_contains(
            urldecode($request->url()),
            's='.$expectedSymbolFragment,
        ));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function tickerNormalizationCases(): array
    {
        return [
            'plain US ticker' => ['AAPL',     'AAPL.US'],
            'crypto with hyphen' => ['BTC-USD',  'BTCUSD'],
            'forex with slash' => ['EUR/USD',  'EURUSD'],
            'multi-class share' => ['BRK.B',    'BRK-B.US'],
            'foreign exchange tag' => ['ASML.NL',  'ASML.NL'],
        ];
    }
}
