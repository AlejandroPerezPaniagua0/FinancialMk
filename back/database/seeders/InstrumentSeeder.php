<?php

namespace Database\Seeders;

use App\Models\AssetClass;
use App\Models\Currency;
use App\Models\Instrument;
use Illuminate\Database\Seeder;

class InstrumentSeeder extends Seeder
{
    public function run(): void
    {
        $stocksClass = AssetClass::where('name', 'Stocks')->first();
        $etfsClass = AssetClass::where('name', 'ETFs')->first();
        $cryptoClass = AssetClass::where('name', 'Crypto')->first();

        $usd = Currency::where('iso_code', 'USD')->first();
        $eur = Currency::where('iso_code', 'EUR')->first();

        if (!$stocksClass || !$etfsClass || !$cryptoClass || !$usd || !$eur) {
            return;
        }

        $instruments = [
            [
                'name' => 'Apple Inc.',
                'ticker' => 'AAPL',
                'asset_class_id' => $stocksClass->id,
                'currency_id' => $usd->id,
            ],
            [
                'name' => 'Microsoft Corporation',
                'ticker' => 'MSFT',
                'asset_class_id' => $stocksClass->id,
                'currency_id' => $usd->id,
            ],
            [
                'name' => 'SPDR S&P 500 ETF',
                'ticker' => 'SPY',
                'asset_class_id' => $etfsClass->id,
                'currency_id' => $usd->id,
            ],
            [
                'name' => 'iShares Core MSCI World ETF',
                'ticker' => 'IWDA',
                'asset_class_id' => $etfsClass->id,
                'currency_id' => $eur->id,
            ],
            [
                'name' => 'Bitcoin',
                'ticker' => 'BTC',
                'asset_class_id' => $cryptoClass->id,
                'currency_id' => $usd->id,
            ],
            [
                'name' => 'Ethereum',
                'ticker' => 'ETH',
                'asset_class_id' => $cryptoClass->id,
                'currency_id' => $usd->id,
            ],
        ];

        foreach ($instruments as $instrument) {
            Instrument::firstOrCreate(
                ['ticker' => $instrument['ticker']],
                [
                    'name' => $instrument['name'],
                    'asset_class_id' => $instrument['asset_class_id'],
                    'currency_id' => $instrument['currency_id'],
                ]
            );
        }
    }
}
