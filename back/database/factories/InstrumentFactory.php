<?php

namespace Database\Factories;

use App\Models\AssetClass;
use App\Models\Currency;
use App\Models\Instrument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Instrument>
 */
class InstrumentFactory extends Factory
{
    protected $model = Instrument::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'ticker' => $this->faker->unique()->lexify('????'),
            'asset_class_id' => AssetClass::factory(),
            'currency_id' => Currency::factory(),
        ];
    }
}