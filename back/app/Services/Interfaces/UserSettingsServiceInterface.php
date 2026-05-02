<?php

namespace App\Services\Interfaces;

use App\Models\User;
use App\Models\UserSetting;

interface UserSettingsServiceInterface
{
    /**
     * Return the settings for the given user, creating defaults if none exist.
     */
    public function getForUser(User $user): UserSetting;

    /**
     * Update the settings for the given user and return the updated record.
     *
     * @param  array<string, mixed>  $data  Only the fields to update (partial update).
     */
    public function updateForUser(User $user, array $data): UserSetting;
}
