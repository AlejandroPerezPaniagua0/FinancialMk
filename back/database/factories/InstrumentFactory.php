<?php

namespace Database\Factories;

use App\Models\AssetClass;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Instrument>
 */
class InstrumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'           => fake()->company(),
            'ticker'         => strtoupper(fake()->unique()->lexify('????')),
            'asset_class_id' => AssetClass::factory(),
            'currency_id'    => Currency::factory(),
        ];
    }
}
