<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

/**
 * Idempotent: firstOrCreate per ISO code. Add the currencies you need
 * BEFORE referencing them in instruments.json — PopularInstrumentsSeeder
 * looks them up by iso_code and will fail loudly if missing.
 */
class CurrenciesSeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['name' => 'US Dollar',          'iso_code' => 'USD'],
            ['name' => 'Euro',               'iso_code' => 'EUR'],
            ['name' => 'British Pound',      'iso_code' => 'GBP'],
            ['name' => 'Japanese Yen',       'iso_code' => 'JPY'],
            ['name' => 'Swiss Franc',        'iso_code' => 'CHF'],
            ['name' => 'Australian Dollar',  'iso_code' => 'AUD'],
            ['name' => 'Canadian Dollar',    'iso_code' => 'CAD'],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(
                ['iso_code' => $currency['iso_code']],
                ['name' => $currency['name']]
            );
        }
    }
}
