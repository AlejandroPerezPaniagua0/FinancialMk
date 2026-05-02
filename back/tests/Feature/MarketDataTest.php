<?php

namespace Tests\Feature;

use App\Models\AssetClass;
use App\Models\Currency;
use App\Models\HistoricalPrice;
use App\Models\Instrument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketDataTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ── Asset Classes ─────────────────────────────────────────────────────────

    public function test_returns_all_asset_classes(): void
    {
        AssetClass::factory()->count(3)->create();

        $this->actingAs($this->user)
            ->getJson('/api/asset-classes')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name'],
                ],
            ]);
    }

    public function test_asset_classes_requires_authentication(): void
    {
        $this->getJson('/api/asset-classes')->assertStatus(401);
    }

    // ── Currencies ────────────────────────────────────────────────────────────

    public function test_returns_all_currencies(): void
    {
        Currency::factory()->count(4)->create();

        $this->actingAs($this->user)
            ->getJson('/api/currencies')
            ->assertStatus(200)
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'iso_code'],
                ],
            ]);
    }

    public function test_currencies_requires_authentication(): void
    {
        $this->getJson('/api/currencies')->assertStatus(401);
    }

    // ── Instruments ───────────────────────────────────────────────────────────

    public function test_returns_paginated_instruments(): void
    {
        Instrument::factory()->count(5)->create();

        $this->actingAs($this->user)
            ->getJson('/api/instruments')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'ticker',
                        'asset_class' => ['id', 'name'],
                        'currency' => ['id', 'name', 'iso_code'],
                    ],
                ],
                'meta' => ['current_page', 'per_page', 'total'],
            ]);
    }

    public function test_filters_instruments_by_asset_class(): void
    {
        $targetClass = AssetClass::factory()->create();
        $otherClass = AssetClass::factory()->create();
        $currency = Currency::factory()->create();

        Instrument::factory()->count(3)->create([
            'asset_class_id' => $targetClass->id,
            'currency_id' => $currency->id,
        ]);
        Instrument::factory()->count(2)->create([
            'asset_class_id' => $otherClass->id,
            'currency_id' => $currency->id,
        ]);

        $this->actingAs($this->user)
            ->getJson("/api/instruments?asset_class_id={$targetClass->id}")
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_instruments_rejects_invalid_asset_class_id(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/instruments?asset_class_id=99999')
            ->assertStatus(422);
    }

    public function test_instruments_requires_authentication(): void
    {
        $this->getJson('/api/instruments')->assertStatus(401);
    }

    // ── Historical Prices ─────────────────────────────────────────────────────

    public function test_returns_historical_prices_for_instrument(): void
    {
        $instrument = Instrument::factory()->create();

        HistoricalPrice::factory()->count(5)->create([
            'instrument_id' => $instrument->id,
        ]);

        $this->actingAs($this->user)
            ->getJson("/api/instruments/{$instrument->id}/prices")
            ->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['date', 'open', 'high', 'low', 'close', 'adjusted_close', 'volume'],
                ],
            ]);
    }

    public function test_prices_filters_by_date_range(): void
    {
        $instrument = Instrument::factory()->create();

        HistoricalPrice::factory()->create([
            'instrument_id' => $instrument->id,
            'date' => '2024-01-10',
        ]);
        HistoricalPrice::factory()->create([
            'instrument_id' => $instrument->id,
            'date' => '2024-06-15',
        ]);
        HistoricalPrice::factory()->create([
            'instrument_id' => $instrument->id,
            'date' => '2024-12-20',
        ]);

        $this->actingAs($this->user)
            ->getJson("/api/instruments/{$instrument->id}/prices?from=2024-03-01&to=2024-09-30")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_prices_returns_404_for_unknown_instrument(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/instruments/99999/prices')
            ->assertStatus(404)
            ->assertJson(['message' => 'Instrument not found.']);
    }

    public function test_prices_rejects_invalid_date_format(): void
    {
        $instrument = Instrument::factory()->create();

        $this->actingAs($this->user)
            ->getJson("/api/instruments/{$instrument->id}/prices?from=not-a-date")
            ->assertStatus(422);
    }

    public function test_prices_requires_authentication(): void
    {
        $this->getJson('/api/instruments/1/prices')->assertStatus(401);
    }
}
