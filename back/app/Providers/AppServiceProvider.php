<?php

namespace App\Providers;

use App\Repositories\HistoricalPriceRepository;
use App\Repositories\InstrumentRepository;
use App\Repositories\Interfaces\HistoricalPriceRepositoryInterface;
use App\Repositories\Interfaces\InstrumentRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WatchlistRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\WatchlistRepository;
use App\Services\AuthService;
use App\Services\DemoService;
use App\Services\ExportService;
use App\Services\InsightService;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\DemoServiceInterface;
use App\Services\Interfaces\ExportServiceInterface;
use App\Services\Interfaces\InsightServiceInterface;
use App\Services\Interfaces\MarketPollingServiceInterface;
use App\Services\Interfaces\UserSettingsServiceInterface;
use App\Services\Interfaces\WatchlistServiceInterface;
use App\Services\MarketPollingService;
use App\Services\UserSettingsService;
use App\Services\WatchlistService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositories
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(InstrumentRepositoryInterface::class, InstrumentRepository::class);
        $this->app->bind(HistoricalPriceRepositoryInterface::class, HistoricalPriceRepository::class);
        $this->app->bind(WatchlistRepositoryInterface::class, WatchlistRepository::class);

        // Services
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserSettingsServiceInterface::class, UserSettingsService::class);
        $this->app->bind(MarketPollingServiceInterface::class, MarketPollingService::class);
        $this->app->bind(InsightServiceInterface::class, InsightService::class);
        $this->app->bind(WatchlistServiceInterface::class, WatchlistService::class);
        $this->app->bind(ExportServiceInterface::class, ExportService::class);
        $this->app->bind(DemoServiceInterface::class, DemoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiters();
    }

    /**
     * Register the named rate limiter guarding the quote-polling endpoint.
     * Parsed from market.polling.request_rate as "requests,minutes".
     */
    private function configureRateLimiters(): void
    {
        RateLimiter::for('market-polling', function (Request $request) {
            [$requests, $minutes] = $this->parseRate(config('market.polling.request_rate', '30,1'));
            $key = optional($request->user())->getAuthIdentifier() ?: $request->ip();

            return Limit::perMinutes($minutes, $requests)->by((string) $key);
        });
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function parseRate(string $raw): array
    {
        $parts = array_map('trim', explode(',', $raw));
        $requests = (int) ($parts[0] ?? 30);
        $minutes = (int) ($parts[1] ?? 1);

        return [max(1, $requests), max(1, $minutes)];
    }
}
