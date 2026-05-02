<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Watchlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Instruments in this watchlist, ordered by user-defined position.
     */
    public function instruments(): BelongsToMany
    {
        return $this->belongsToMany(Instrument::class, 'watchlist_instrument')
            ->withPivot(['position'])
            ->withTimestamps()
            ->orderBy('watchlist_instrument.position');
    }
}
