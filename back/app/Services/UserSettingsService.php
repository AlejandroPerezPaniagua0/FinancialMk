<?php

namespace App\Services;

use App\DTOs\UserSettings\UpdateUserSettingsDTO;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\Interfaces\UserSettingsServiceInterface;

class UserSettingsService implements UserSettingsServiceInterface
{
    public function getForUser(User $user): UserSetting
    {
        return UserSetting::with('currency')
            ->firstOrCreate(
                ['user_id' => $user->id],
                ['user_id' => $user->id],
            )->load('currency');
    }

    public function updateForUser(User $user, array $data): UserSetting
    {
        $settings = UserSetting::firstOrCreate(['user_id' => $user->id]);

        $settings->fill($data);
        $settings->save();

        return $settings->load('currency');
    }
}
