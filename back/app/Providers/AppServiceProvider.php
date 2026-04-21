<?php

namespace App\Providers;

use App\ApiClient\TwelveDataApiClient;
use App\Repositories\HistoricalPriceRepository;
use App\Repositories\InstrumentRepository;
use App\Repositories\Interfaces\HistoricalPriceRepositoryInterface;
use App\Repositories\Interfaces\InstrumentRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\MarketPollingServiceInterface;
use App\Services\Interfaces\MarketProviderInterface;
use App\Services\Interfaces\UserSettingsServiceInterface;
use App\Services\MarketPollingService;
use App\Services\UserSettingsService;
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

        // Api Clients
        $this->app->bind(TwelveDataApiClient::class, function ($app) {
            return new TwelveDataApiClient(config('services.twelve_data.base_url'), config('services.twelve_data.api_key'));
        });

        // Services
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(MarketProviderInterface::class, TwelveDataApiClient::class);
        $this->app->bind(UserSettingsServiceInterface::class, UserSettingsService::class);
        $this->app->bind(MarketPollingServiceInterface::class, MarketPollingService::class);
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
