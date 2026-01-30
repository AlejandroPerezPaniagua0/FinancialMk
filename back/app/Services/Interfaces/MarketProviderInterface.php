<?php

namespace App\Services\MarketProviders;

use DateTimeInterface;
use Illuminate\Support\Collection;

interface MarketProviderInterface
{
    /**
     * Identifier of the provider (e.g. "twelvedata", "finnhub").
     */
    public function name(): string;

    /**
     * Optional health check for availability/credentials.
     */
    public function isAvailable(): bool;

    /**
     * Fetch all asset classes available in the provider.
     *
     * @return Collection<int, array{id?: int, name: string}>
     */
    public function fetchAssetClasses(): Collection;

    /**
     * Fetch all currencies available in the provider.
     *
     * @return Collection<int, array{id?: int, name: string, iso_code: string}>
     */
    public function fetchCurrencies(): Collection;

    /**
     * Fetch instruments (assets) available in the provider.
     *
     * @param string|null $assetClass Optional filter by asset class name/code.
     * @return Collection<int, array{
     *   name: string,
     *   ticker: string,
     *   asset_class?: string,
     *   currency?: string
     * }>
     */
    public function fetchInstruments(?string $assetClass = null): Collection;

    /**
     * Fetch historical prices for a given instrument (ticker) between dates.
     *
     * @return Collection<int, array{
     *   date: string,
     *   open: float,
     *   high: float,
     *   low: float,
     *   close: float,
     *   adjusted_close: float,
     *   volume: int
     * }>
     */
    public function fetchHistoricalPrices(
        string $ticker,
        DateTimeInterface $from,
        DateTimeInterface $to
    ): Collection;
}
