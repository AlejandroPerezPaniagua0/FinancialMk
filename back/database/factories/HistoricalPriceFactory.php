<?php

namespace Database\Factories;

use App\Models\Instrument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HistoricalPrice>
 */
class HistoricalPriceFactory extends Factory
{
    public function definition(): array
    {
        $open = fake()->randomFloat(4, 10, 500);

        return [
            'instrument_id'  => Instrument::factory(),
            'date'           => fake()->unique()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'open'           => $open,
            'high'           => $open * fake()->randomFloat(4, 1.00, 1.05),
            'low'            => $open * fake()->randomFloat(4, 0.95, 1.00),
            'close'          => fake()->randomFloat(4, $open * 0.95, $open * 1.05),
            'adjusted_close' => fake()->randomFloat(4, $open * 0.95, $open * 1.05),
            'volume'         => fake()->numberBetween(100_000, 50_000_000),
        ];
    }
}
