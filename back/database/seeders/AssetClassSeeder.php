<?php

namespace Database\Seeders;

use App\Models\AssetClass;
use Illuminate\Database\Seeder;

class AssetClassSeeder extends Seeder
{
    public function run(): void
    {
        $assetClasses = [
            'Stocks',
            'ETFs',
            'Bonds',
            'Forex',
            'Commodities',
            'Crypto',
        ];

        foreach ($assetClasses as $name) {
            AssetClass::firstOrCreate(['name' => $name]);
        }
    }
}
