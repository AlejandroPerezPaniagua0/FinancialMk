<?php

/**
 * FinancialMk · Demo mode configuration
 *
 * When enabled, the public POST /api/demo/login endpoint mints a Sanctum
 * token for a pre-seeded read-only user so visitors can try the dashboard
 * with no signup. Disable in any deployment that exposes write surfaces.
 */
return [
    /*
    |---------------------------------------------------------------------
    | Enabled
    |---------------------------------------------------------------------
    |
    | Master switch. When false, /api/demo/login returns 404 and
    | /api/demo/status reports `enabled: false`.
    */
    'enabled' => env('DEMO_MODE', false),

    /*
    |---------------------------------------------------------------------
    | Demo user identity
    |---------------------------------------------------------------------
    |
    | Email is the lookup key used by both the seeder and the controller.
    | The password is unused at runtime (we mint a token directly without
    | Auth::attempt) but exists so the row is well-formed.
    */
    'email'    => env('DEMO_EMAIL', 'demo@financialmk.local'),
    'name'     => env('DEMO_NAME',  'Demo User'),
    'password' => env('DEMO_PASSWORD', 'fmk-demo-password'),

    /*
    |---------------------------------------------------------------------
    | Demo watchlist
    |---------------------------------------------------------------------
    |
    | Tickers pre-loaded into the demo user's default watchlist (created
    | by Phase 2.12). Order matters — first ticker is opened by default.
    */
    'tickers' => ['AAPL', 'MSFT', 'GOOGL', 'SPY', 'BTC-USD'],
];
