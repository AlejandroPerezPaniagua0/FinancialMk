<?php

namespace App\DTOs\UserSettings;

class UpdateUserSettingsDTO
{
    public function __construct(
        public readonly ?int $currencyId,
        public readonly ?string $theme,
        public readonly ?string $language,
        public readonly ?string $timezone,
        public readonly ?string $defaultChartRange,
        public readonly ?string $defaultChartInterval,
        public readonly ?bool $showExtendedMetrics,
        public readonly ?bool $notificationsEnabled,
        public readonly ?array $preferences,
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            currencyId: $validated['currency_id'] ?? null,
            theme: $validated['theme'] ?? null,
            language: $validated['language'] ?? null,
            timezone: $validated['timezone'] ?? null,
            defaultChartRange: $validated['default_chart_range'] ?? null,
            defaultChartInterval: $validated['default_chart_interval'] ?? null,
            showExtendedMetrics: $validated['show_extended_metrics'] ?? null,
            notificationsEnabled: $validated['notifications_enabled'] ?? null,
            preferences: $validated['preferences'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'currency_id' => $this->currencyId,
            'theme' => $this->theme,
            'language' => $this->language,
            'timezone' => $this->timezone,
            'default_chart_range' => $this->defaultChartRange,
            'default_chart_interval' => $this->defaultChartInterval,
            'show_extended_metrics' => $this->showExtendedMetrics,
            'notifications_enabled' => $this->notificationsEnabled,
            'preferences' => $this->preferences,
        ], fn ($v) => $v !== null);
    }
}
