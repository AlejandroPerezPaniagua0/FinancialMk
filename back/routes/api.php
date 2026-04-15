<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarketDataController;
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
});
