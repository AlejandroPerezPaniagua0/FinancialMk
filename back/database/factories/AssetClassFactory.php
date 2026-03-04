<?php

namespace Database\Factories;

use App\Models\AssetClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetClass>
 */
class AssetClassFactory extends Factory
{
    protected $model = AssetClass::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Common Stock', 'ETF', 'Digital Currency', 'Mutual Fund', 'Bond',
            ]),
        ];
    }
}