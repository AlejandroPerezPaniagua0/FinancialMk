<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarketDataController;
use App\Http\Controllers\MarketPollingController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
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

    // User settings
    Route::get('/user/settings', [UserSettingsController::class, 'show']);
    Route::put('/user/settings', [UserSettingsController::class, 'update']);
});
