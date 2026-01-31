<?php

namespace App\Providers;

use App\ApiClient\TwelveDataApiClient;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\MarketProviders\MarketProviderInterface;
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

        // Api Clients
        $this->app->bind(TwelveDataApiClient::class, function ($app) {
            return new TwelveDataApiClient(config('services.twelve_data.base_url'), config('services.twelve_data.api_key'));
        });

        // Services
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(MarketProviderInterface::class, TwelveDataApiClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
