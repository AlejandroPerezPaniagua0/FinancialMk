<?php

namespace App\Jobs;

use App\UseCases\SyncAssetPricesUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncAssetPrices implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(SyncAssetPricesUseCase $useCase): void
    {
        $useCase->execute();
    }
}