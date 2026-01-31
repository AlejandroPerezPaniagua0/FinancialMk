<?php

namespace App\ApiClient;

use App\Services\MarketProviders\MarketProviderInterface;
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
     * Get the name of the market provider
     * @return string
     */
    public function name(): string
    {
        return 'twelve_data';
    }

    /**
     * Check if the market provider is available
     * @return bool
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * Fetch the asset classes
     * @return Collection
     */
    public function fetchAssetClasses(): Collection
    {
        return collect();
    }

    /**
     * Fetch the currencies
     * @return Collection
     */
    public function fetchCurrencies(): Collection
    {
        return collect();
    }

    /**
     * Fetch the instruments
     * @param string|null $assetClass
     * @return Collection
     */
    public function fetchInstruments(?string $assetClass = null): Collection
    {
        return collect();
    }

    /**
     * Fetch the historical prices
     * @param string $ticker
     * @param DateTimeInterface $from
     * @param DateTimeInterface $to
     * @return Collection
     */
    public function fetchHistoricalPrices(string $ticker, DateTimeInterface $from, DateTimeInterface $to): Collection
    {
        return collect();
    }

    /**
     * Authorize the request
     * @param PendingRequest $request
     * @return PendingRequest
     */
    protected function authorize(PendingRequest $request): PendingRequest
    {
        return $request->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ]);
    }
}
