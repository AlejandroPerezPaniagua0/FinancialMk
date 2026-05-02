<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ── GET /api/user/settings ────────────────────────────────────────────────

    public function test_returns_default_settings_on_first_access(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/user/settings')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'currency',
                    'theme',
                    'language',
                    'timezone',
                    'default_chart_range',
                    'default_chart_interval',
                    'show_extended_metrics',
                    'notifications_enabled',
                    'preferences',
                ],
            ]);

        $this->assertDatabaseHas('user_settings', ['user_id' => $this->user->id]);
    }

    public function test_returns_existing_settings(): void
    {
        $currency = Currency::factory()->create();

        UserSetting::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $currency->id,
            'theme' => 'dark',
            'language' => 'es',
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/user/settings')
            ->assertStatus(200)
            ->assertJsonPath('data.theme', 'dark')
            ->assertJsonPath('data.language', 'es')
            ->assertJsonPath('data.currency.id', $currency->id);
    }

    public function test_show_requires_authentication(): void
    {
        $this->getJson('/api/user/settings')->assertStatus(401);
    }

    // ── PUT /api/user/settings ────────────────────────────────────────────────

    public function test_updates_settings(): void
    {
        $currency = Currency::factory()->create();

        $this->actingAs($this->user)
            ->putJson('/api/user/settings', [
                'theme' => 'dark',
                'language' => 'es',
                'currency_id' => $currency->id,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.theme', 'dark')
            ->assertJsonPath('data.language', 'es')
            ->assertJsonPath('data.currency.id', $currency->id);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $this->user->id,
            'theme' => 'dark',
            'language' => 'es',
            'currency_id' => $currency->id,
        ]);
    }

    public function test_partial_update_only_changes_provided_fields(): void
    {
        UserSetting::factory()->create([
            'user_id' => $this->user->id,
            'theme' => 'dark',
            'language' => 'es',
            'timezone' => 'Europe/Madrid',
        ]);

        $this->actingAs($this->user)
            ->putJson('/api/user/settings', ['theme' => 'light'])
            ->assertStatus(200)
            ->assertJsonPath('data.theme', 'light')
            ->assertJsonPath('data.language', 'es')
            ->assertJsonPath('data.timezone', 'Europe/Madrid');
    }

    public function test_creates_settings_on_first_update(): void
    {
        $this->assertDatabaseMissing('user_settings', ['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->putJson('/api/user/settings', ['theme' => 'dark'])
            ->assertStatus(200);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $this->user->id,
            'theme' => 'dark',
        ]);
    }

    public function test_rejects_invalid_theme(): void
    {
        $this->actingAs($this->user)
            ->putJson('/api/user/settings', ['theme' => 'rainbow'])
            ->assertStatus(422);
    }

    public function test_rejects_invalid_timezone(): void
    {
        $this->actingAs($this->user)
            ->putJson('/api/user/settings', ['timezone' => 'Not/ATimezone'])
            ->assertStatus(422);
    }

    public function test_rejects_invalid_chart_range(): void
    {
        $this->actingAs($this->user)
            ->putJson('/api/user/settings', ['default_chart_range' => '10Y'])
            ->assertStatus(422);
    }

    public function test_rejects_nonexistent_currency_id(): void
    {
        $this->actingAs($this->user)
            ->putJson('/api/user/settings', ['currency_id' => 99999])
            ->assertStatus(422);
    }

    public function test_accepts_null_currency_id(): void
    {
        UserSetting::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => Currency::factory()->create()->id,
        ]);

        $this->actingAs($this->user)
            ->putJson('/api/user/settings', ['currency_id' => null])
            ->assertStatus(200)
            ->assertJsonPath('data.currency', null);
    }

    public function test_update_requires_authentication(): void
    {
        $this->putJson('/api/user/settings', ['theme' => 'dark'])->assertStatus(401);
    }
}
