<?php

namespace Database\Seeders;

use App\Models\AssetClass;
use App\Models\Currency;
use App\Models\Instrument;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Reads database/fixtures/instruments.json and upserts each entry by ticker.
 *
 * Idempotent: re-running won't duplicate. AssetClass + Currency rows must
 * exist already (handled by AssetClassesSeeder + CurrenciesSeeder running
 * earlier in DatabaseSeeder).
 *
 * Why a fixture file and not a factory? Because we want a curated, stable
 * list of well-known tickers — the same set every developer sees on a
 * fresh `docker compose up`. Reproducible demos beat random data here.
 */
class PopularInstrumentsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('fixtures/instruments.json');

        if (! file_exists($path)) {
            throw new RuntimeException("Fixture not found: {$path}");
        }

        $payload = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $rows    = $payload['instruments'] ?? [];

        // Pre-load lookups to avoid N queries per row.
        $assetClassIds = AssetClass::pluck('id', 'name')->all();
        $currencyIds   = Currency::pluck('id', 'iso_code')->all();

        foreach ($rows as $row) {
            $assetClassId = $assetClassIds[$row['asset_class']] ?? null;
            $currencyId   = $currencyIds[$row['currency']] ?? null;

            if (! $assetClassId) {
                throw new RuntimeException("AssetClass '{$row['asset_class']}' missing — run AssetClassesSeeder first.");
            }
            if (! $currencyId) {
                throw new RuntimeException("Currency '{$row['currency']}' missing — run CurrenciesSeeder first.");
            }

            Instrument::updateOrCreate(
                ['ticker' => $row['ticker']],
                [
                    'name'           => $row['name'],
                    'asset_class_id' => $assetClassId,
                    'currency_id'    => $currencyId,
                ]
            );
        }

        $this->command?->info('Seeded '.count($rows).' popular instruments from fixture.');
    }
}
