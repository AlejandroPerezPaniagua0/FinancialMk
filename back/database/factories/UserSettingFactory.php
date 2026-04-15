<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserSetting>
 */
class UserSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'                 => User::factory(),
            'currency_id'             => null,
            'theme'                   => fake()->randomElement(['light', 'dark', 'system']),
            'language'                => fake()->randomElement(['en', 'es', 'fr', 'de']),
            'timezone'                => fake()->timezone(),
            'default_chart_range'     => fake()->randomElement(['1D', '1W', '1M', '3M', '6M', '1Y', 'MAX']),
            'default_chart_interval'  => fake()->randomElement(['1d', '1wk', '1mo']),
            'show_extended_metrics'   => fake()->boolean(),
            'notifications_enabled'   => fake()->boolean(),
            'preferences'             => null,
        ];
    }
}
