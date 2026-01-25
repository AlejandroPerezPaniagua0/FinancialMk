<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency_id',
        'theme',
        'language',
        'timezone',
        'default_chart_range',
        'default_chart_interval',
        'show_extended_metrics',
        'notifications_enabled',
        'preferences',
    ];

    protected $casts = [
        'language' => 'string',
        'timezone' => 'string',
        'show_extended_metrics' => 'boolean',
        'notifications_enabled' => 'boolean',
        'preferences' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
