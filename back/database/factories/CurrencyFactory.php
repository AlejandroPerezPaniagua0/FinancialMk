<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'     => fake()->unique()->country(),
            'iso_code' => strtoupper(fake()->unique()->lexify('???')),
        ];
    }
}
