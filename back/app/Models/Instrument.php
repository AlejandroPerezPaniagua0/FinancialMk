<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instrument extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'ticker',
        'asset_class_id',
        'currency_id',
    ];

    /**
     * Get the asset class that owns the instrument.
     */
    public function assetClass(): BelongsTo
    {
        return $this->belongsTo(AssetClass::class);
    }

    /**
     * Get the currency that owns the instrument.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
