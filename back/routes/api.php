<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\MarketDataController;
use App\Http\Controllers\MarketPollingController;
use App\Http\Controllers\UserSettingsController;
use App\Http\Controllers\WatchlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Demo flow — public so the landing page can probe status and mint a token.
Route::get('/demo/status', [DemoController::class, 'status']);
Route::post('/demo/login', [DemoController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        $user = $request->user();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_demo' => (bool) $user->is_demo,
        ];
    });

    // Market data
    Route::get('/asset-classes', [MarketDataController::class, 'assetClasses']);
    Route::get('/currencies', [MarketDataController::class, 'currencies']);
    Route::get('/instruments', [MarketDataController::class, 'instruments']);
    Route::get('/instruments/{id}/prices', [MarketDataController::class, 'prices']);

    // Real-time comparison polling. The throttle middleware protects the
    // upstream provider by limiting how often a single authenticated user
    // can request fresh quotes — configured via market.polling.request_rate.
    Route::middleware('throttle:market-polling')
        ->get('/instruments/quotes', [MarketPollingController::class, 'quotes']);

    // Insights — locally computed risk/return stats and correlation matrix.
    Route::get('/instruments/correlation-matrix', [InsightController::class, 'correlationMatrix']);
    Route::get('/instruments/{id}/insights', [InsightController::class, 'show']);

    // Watchlists (per-user CRUD)
    Route::get('/watchlists', [WatchlistController::class, 'index']);
    Route::post('/watchlists', [WatchlistController::class, 'store']);
    Route::get('/watchlists/{id}', [WatchlistController::class, 'show']);
    Route::put('/watchlists/{id}', [WatchlistController::class, 'update']);
    Route::delete('/watchlists/{id}', [WatchlistController::class, 'destroy']);
    Route::put('/watchlists/{id}/instruments', [WatchlistController::class, 'syncInstruments']);

    // Export — universal CSV/JSON download for prices and watchlists.
    Route::get('/export/instruments/{id}/prices', [ExportController::class, 'instrumentPrices']);
    Route::get('/export/watchlists/{id}', [ExportController::class, 'watchlist']);

    // User settings
    Route::get('/user/settings', [UserSettingsController::class, 'show']);
    Route::put('/user/settings', [UserSettingsController::class, 'update']);
});
