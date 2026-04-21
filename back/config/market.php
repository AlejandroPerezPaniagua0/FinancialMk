<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Market polling
    |--------------------------------------------------------------------------
    |
    | Settings that govern how the real-time comparison view refreshes
    | quotes for selected instruments. The defaults are tuned to respect
    | TwelveData's free-tier rate limits (8 req/min).
    |
    */

    'polling' => [
        // Minimum seconds between upstream calls for the same ticker.
        // The service caches each quote for this TTL and serves cached
        // copies when the client polls faster than the throttle.
        'throttle_seconds' => (int) env('MARKET_POLLING_THROTTLE_SECONDS', 30),

        // Maximum number of instruments a single comparison request may
        // ask for. Matches the frontend grid upper bound (2-4 assets).
        'max_assets' => (int) env('MARKET_POLLING_MAX_ASSETS', 4),

        // Rate-limit applied to the /instruments/quotes endpoint per user.
        // "requests,minutes" format — e.g. 30,1 allows 30 requests per minute,
        // giving every selected asset a 30s refresh cadence with headroom.
        'request_rate' => env('MARKET_POLLING_REQUEST_RATE', '30,1'),

        // Cache store used for per-ticker quote caching. Falls back to the
        // default store when null.
        'cache_store' => env('MARKET_POLLING_CACHE_STORE'),

        // Cache key prefix for quote entries.
        'cache_prefix' => 'market:quote:',
    ],

];
