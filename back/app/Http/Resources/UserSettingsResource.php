<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'theme' => $this->theme,
            'language' => $this->language,
            'timezone' => $this->timezone,
            'default_chart_range' => $this->default_chart_range,
            'default_chart_interval' => $this->default_chart_interval,
            'show_extended_metrics' => $this->show_extended_metrics,
            'notifications_enabled' => $this->notifications_enabled,
            'preferences' => $this->preferences,
        ];
    }
}
