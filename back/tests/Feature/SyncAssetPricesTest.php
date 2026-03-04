<?php

namespace Tests\Feature;

use App\Models\AssetClass;
use App\Models\Currency;
use App\Models\HistoricalPrice;
use App\Models\Instrument;
use App\Services\Interfaces\MarketProviderInterface;
use App\UseCases\SyncAssetPricesUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class SyncAssetPricesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Carbon\Carbon::setTestNow('2025-06-15');
    }

    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow();
        parent::tearDown();
    }

    private function createInstrument(string $ticker = 'AAPL', string $name = 'Apple Inc.'): Instrument
    {
        $assetClass = AssetClass::where('name', 'Common Stock')->first() ?: AssetClass::factory()->create(['name' => 'Common Stock']);
        $currency = Currency::where('iso_code', 'USD')->first() ?: Currency::factory()->create(['name' => 'US Dollar', 'iso_code' => 'USD']);

        return Instrument::factory()->create([
            'name' => $name,
            'ticker' => $ticker,
            'asset_class_id' => $assetClass->id,
            'currency_id' => $currency->id,
        ]);
    }

    private function fakePriceData(array $dates): Collection
    {
        return collect($dates)->map(fn(string $date) => [
        'date' => $date,
        'open' => 150.0,
        'high' => 155.0,
        'low' => 148.0,
        'close' => 153.0,
        'adjusted_close' => 153.0,
        'volume' => 100000,
        ]);
    }

    public function test_syncs_prices_for_all_instruments(): void
    {
        $instrument = $this->createInstrument();

        $mockProvider = Mockery::mock(MarketProviderInterface::class);
        $mockProvider
            ->shouldReceive('name')
            ->andReturn('test_provider');
        $mockProvider
            ->shouldReceive('fetchHistoricalPrices')
            ->once()
            ->andReturn($this->fakePriceData(['2025-06-01', '2025-06-02', '2025-06-03']));

        $this->app->instance(MarketProviderInterface::class , $mockProvider);

        /** @var SyncAssetPricesUseCase $useCase */
        $useCase = $this->app->make(SyncAssetPricesUseCase::class);
        $useCase->execute();

        $this->assertDatabaseCount('historical_prices', 3);
        $this->assertDatabaseHas('historical_prices', [
            'instrument_id' => $instrument->id,
            'date' => '2025-06-01',
            'close' => 153.0,
        ]);
    }

    public function test_resumes_from_latest_existing_date(): void
    {
        $instrument = $this->createInstrument();

        HistoricalPrice::factory()->create([
            'instrument_id' => $instrument->id,
            'date' => '2025-06-10',
        ]);

        $mockProvider = Mockery::mock(MarketProviderInterface::class);
        $mockProvider
            ->shouldReceive('fetchHistoricalPrices')
            ->once()
            ->withArgs(function (string $ticker, \DateTimeInterface $from, \DateTimeInterface $to) {
            return $ticker === 'AAPL'
            && $from->format('Y-m-d') === '2025-06-11';
        })
            ->andReturn($this->fakePriceData(['2025-06-11', '2025-06-12']));

        $this->app->instance(MarketProviderInterface::class , $mockProvider);

        /** @var SyncAssetPricesUseCase $useCase */
        $useCase = $this->app->make(SyncAssetPricesUseCase::class);
        $useCase->execute();

        $this->assertDatabaseCount('historical_prices', 3);
    }

    public function test_handles_empty_instruments(): void
    {
        $mockProvider = Mockery::mock(MarketProviderInterface::class);
        $mockProvider->shouldNotReceive('fetchHistoricalPrices');

        $this->app->instance(MarketProviderInterface::class , $mockProvider);

        /** @var SyncAssetPricesUseCase $useCase */
        $useCase = $this->app->make(SyncAssetPricesUseCase::class);
        $useCase->execute();

        $this->assertDatabaseCount('historical_prices', 0);
    }

    public function test_upsert_does_not_duplicate_prices(): void
    {
        $instrument = $this->createInstrument();

        HistoricalPrice::factory()->create([
            'instrument_id' => $instrument->id,
            'date' => '2025-06-01',
            'close' => 100.0,
        ]);

        $mockProvider = Mockery::mock(MarketProviderInterface::class);
        $mockProvider
            ->shouldReceive('fetchHistoricalPrices')
            ->once()
            ->withArgs(function (string $ticker, \DateTimeInterface $from, \DateTimeInterface $to) {
            return $ticker === 'AAPL'
            && $from->format('Y-m-d') === '2025-06-02';
        })
            ->andReturn($this->fakePriceData(['2025-06-02', '2025-06-03']));

        $this->app->instance(MarketProviderInterface::class , $mockProvider);

        /** @var SyncAssetPricesUseCase $useCase */
        $useCase = $this->app->make(SyncAssetPricesUseCase::class);
        $useCase->execute();

        $this->assertDatabaseCount('historical_prices', 3);

        $this->assertDatabaseHas('historical_prices', [
            'instrument_id' => $instrument->id,
            'date' => '2025-06-02',
            'close' => 153.0,
        ]);
    }

    public function test_handles_provider_exception_continuing_with_other_instruments(): void
    {
        $instrument1 = $this->createInstrument('AAPL', 'Apple');
        $instrument2 = $this->createInstrument('MSFT', 'Microsoft');

        $mockProvider = Mockery::mock(MarketProviderInterface::class);

        // AAPL fails
        $mockProvider
            ->shouldReceive('fetchHistoricalPrices')
            ->with('AAPL', Mockery::any(), Mockery::any())
            ->once()
            ->andThrow(new \Exception('API Error'));

        // MSFT succeeds
        $mockProvider
            ->shouldReceive('fetchHistoricalPrices')
            ->with('MSFT', Mockery::any(), Mockery::any())
            ->once()
            ->andReturn($this->fakePriceData(['2025-06-14']));

        $this->app->instance(MarketProviderInterface::class , $mockProvider);

        /** @var SyncAssetPricesUseCase $useCase */
        $useCase = $this->app->make(SyncAssetPricesUseCase::class);
        $useCase->execute();

        // MSFT should have its price, even if AAPL failed
        $this->assertDatabaseHas('historical_prices', [
            'instrument_id' => $instrument2->id,
            'date' => '2025-06-14',
        ]);
    }

}