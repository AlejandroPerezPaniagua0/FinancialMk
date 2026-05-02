<?php

namespace Database\Seeders;

use App\Models\Instrument;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent seeder for the demo user used by /api/demo/login.
 *
 * Always runs (not gated on config('demo.enabled')) so toggling DEMO_MODE
 * at runtime works without re-seeding. The controller is the gate, not the
 * seeder. Also primes a default watchlist with the tickers configured in
 * config/demo.php so the demo user opens the dashboard with something
 * meaningful instead of an empty state.
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('demo.email');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => config('demo.name'),
                'password' => Hash::make(config('demo.password')),
                'is_demo' => true,
                'email_verified_at' => now(),
            ]
        );

        // Default watchlist — only created if the watchlists table exists,
        // so re-seeding before phase 2.12 migrations have been applied is
        // a soft no-op rather than a hard error.
        if (Schema::hasTable('watchlists')) {
            $watchlist = Watchlist::firstOrCreate(
                ['user_id' => $user->id, 'is_default' => true],
                ['name' => 'Demo watchlist'],
            );

            $tickers = (array) config('demo.tickers', []);
            $ids = Instrument::whereIn('ticker', $tickers)
                ->orderByRaw('FIELD(ticker, '.implode(',', array_fill(0, count($tickers), '?')).')', $tickers)
                ->pluck('id')
                ->all();

            // Fallback: order may not work on SQLite — re-sort in PHP.
            if (count($ids) !== count($tickers)) {
                $byTicker = Instrument::whereIn('ticker', $tickers)->pluck('id', 'ticker');
                $ids = collect($tickers)->map(fn ($t) => $byTicker[$t] ?? null)->filter()->values()->all();
            }

            $payload = [];
            foreach ($ids as $position => $instrumentId) {
                $payload[(int) $instrumentId] = ['position' => $position];
            }
            $watchlist->instruments()->sync($payload);
        }

        $this->command?->info("Demo user ensured: {$email}");
    }
}
