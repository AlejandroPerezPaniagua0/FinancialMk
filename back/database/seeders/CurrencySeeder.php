<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['name' => 'US Dollar', 'iso_code' => 'USD'],
            ['name' => 'Euro', 'iso_code' => 'EUR'],
            ['name' => 'British Pound', 'iso_code' => 'GBP'],
            ['name' => 'Japanese Yen', 'iso_code' => 'JPY'],
            ['name' => 'Swiss Franc', 'iso_code' => 'CHF'],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(
                ['iso_code' => $currency['iso_code']],
                ['name' => $currency['name']]
            );
        }
    }
}
