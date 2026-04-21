<?php

namespace App\Services;

use App\Repositories\Interfaces\InstrumentRepositoryInterface;
use App\Services\Interfaces\MarketPollingServiceInterface;
use App\Services\Interfaces\MarketProviderInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MarketPollingService implements MarketPollingServiceInterface
{
    private CacheRepository $cache;

    private int $throttleSeconds;

    private string $cachePrefix;

    public function __construct(
        private readonly MarketProviderInterface $provider,
        private readonly InstrumentRepositoryInterface $instrumentRepository,
    ) {
        $store = config('market.polling.cache_store');
        $this->cache = $store ? Cache::store($store) : Cache::store();
        $this->throttleSeconds = (int) config('market.polling.throttle_seconds', 30);
        $this->cachePrefix = (string) config('market.polling.cache_prefix', 'market:quote:');
    }

    public function quotesFor(array $instrumentIds): Collection
    {
        $ids = collect($instrumentIds)
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return $ids->map(function (int $id): ?array {
            $instrument = $this->instrumentRepository->findById($id);
            if ($instrument === null) {
                return null;
            }

            $entry = $this->resolveQuote($instrument->ticker);

            return [
                'instrument_id'   => $id,
                'ticker'          => $instrument->ticker,
                'price'           => $entry['quote']['price'] ?? null,
                'previous_close'  => $entry['quote']['previous_close'] ?? null,
                'change'          => $entry['quote']['change'] ?? null,
                'change_percent'  => $entry['quote']['change_percent'] ?? null,
                'currency'        => $entry['quote']['currency'] ?? ($instrument->currency->iso_code ?? null),
                'fetched_at'      => $entry['fetched_at'],
                'cached'          => $entry['cached'],
                'next_refresh_in' => $entry['next_refresh_in'],
            ];
        })->filter()->values();
    }

    /**
     * Return the cached quote if still fresh, otherwise fetch a new one
     * and cache it for the throttle window.
     *
     * @return array{quote: array|null, fetched_at: string, cached: bool, next_refresh_in: int}
     */
    private function resolveQuote(string $ticker): array
    {
        $key = $this->cachePrefix . strtolower($ticker);
        $cached = $this->cache->get($key);
        $now = now();

        if (is_array($cached) && isset($cached['fetched_at'])) {
            $age = $now->diffInSeconds($cached['fetched_at'], true);
            if ($age < $this->throttleSeconds) {
                return [
                    'quote'           => $cached['quote'] ?? null,
                    'fetched_at'      => $cached['fetched_at'],
                    'cached'          => true,
                    'next_refresh_in' => max(0, $this->throttleSeconds - (int) $age),
                ];
            }
        }

        $quote = $this->provider->fetchQuote($ticker);
        $fetchedAt = $now->toIso8601String();

        // Even when the upstream fails, cache the failure for the throttle
        // window to avoid hammering the provider after a rejection.
        $entry = [
            'quote'      => $quote,
            'fetched_at' => $fetchedAt,
        ];
        $this->cache->put($key, $entry, $this->throttleSeconds);

        return [
            'quote'           => $quote,
            'fetched_at'      => $fetchedAt,
            'cached'          => false,
            'next_refresh_in' => $this->throttleSeconds,
        ];
    }
}
