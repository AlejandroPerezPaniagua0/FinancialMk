<?php

namespace Database\Factories;

use App\Models\HistoricalPrice;
use App\Models\Instrument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HistoricalPrice>
 */
class HistoricalPriceFactory extends Factory
{
    protected $model = HistoricalPrice::class;

    public function definition(): array
    {
        $open = $this->faker->randomFloat(6, 50, 500);

        return [
            'instrument_id' => Instrument::factory(),
            'date' => $this->faker->unique()->date(),
            'open' => $open,
            'high' => $open + $this->faker->randomFloat(6, 0, 10),
            'low' => $open - $this->faker->randomFloat(6, 0, 10),
            'close' => $open + $this->faker->randomFloat(6, -5, 5),
            'adjusted_close' => $open + $this->faker->randomFloat(6, -5, 5),
            'volume' => $this->faker->numberBetween(10000, 5000000),
        ];
    }
}