<?php

namespace App\UseCases;

use App\Repositories\Interfaces\HistoricalPriceRepositoryInterface;
use App\Repositories\Interfaces\InstrumentRepositoryInterface;
use App\Services\Interfaces\MarketProviderInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncAssetPricesUseCase
{
    public function __construct(
        private MarketProviderInterface $marketProvider,
        private InstrumentRepositoryInterface $instrumentRepository,
        private HistoricalPriceRepositoryInterface $historicalPriceRepository,
        )
    {
    }

    /**
     * Synchronize historical prices for all instruments.
     */
    public function execute(): void
    {
        $totalSynced = 0;
        $today = Carbon::today();

        // Using chunk to handle large numbers of instruments efficiently
        $this->instrumentRepository->getAll()->chunk(100)->each(function ($instruments) use ($today, &$totalSynced) {
            foreach ($instruments as $instrument) {
                try {
                    $latestDate = $this->instrumentRepository->getLatestPriceDate($instrument->id);
                    $from = $latestDate ? $latestDate->copy()->addDay() : $today->copy()->subYear();

                    if ($from->greaterThan($today)) {
                        Log::info("[SyncAssetPrices] {$instrument->ticker} is already up to date.");
                        continue;
                    }

                    $prices = $this->marketProvider->fetchHistoricalPrices(
                        $instrument->ticker,
                        $from,
                        $today,
                    );

                    if ($prices->isEmpty()) {
                        Log::info("[SyncAssetPrices] No new prices for {$instrument->ticker}.");
                        continue;
                    }

                    $affected = $this->historicalPriceRepository->upsertPrices($instrument->id, $prices);
                    $totalSynced += $affected;

                    Log::info("[SyncAssetPrices] Synced {$prices->count()} prices for {$instrument->ticker}.");
                }
                catch (\Exception $e) {
                    Log::error("[SyncAssetPrices] Error syncing prices for {$instrument->ticker}: {$e->getMessage()}");
                    continue;
                }
            }
        });

        Log::info("[SyncAssetPrices] Completed. Total rows affected: {$totalSynced}.");
    }
}