<?php

namespace Database\Seeders;

use App\Models\AssetClass;
use Illuminate\Database\Seeder;

/**
 * Idempotent: uses firstOrCreate so re-running doesn't duplicate rows.
 * Kept tiny on purpose — extra asset classes belong in domain code, not here.
 */
class AssetClassesSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Stock', 'ETF', 'Crypto', 'Forex', 'Index'];

        foreach ($names as $name) {
            AssetClass::firstOrCreate(['name' => $name]);
        }
    }
}
