<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricalPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'instrument_id',
        'date',
        'open',
        'high',
        'low',
        'close',
        'adjusted_close',
        'volume',
    ];

    /**
     * Casting de atributos para asegurar tipos correctos al pintar gráficos
     */
    protected $casts = [
        'date' => 'date',
        'open' => 'float',
        'high' => 'float',
        'low' => 'float',
        'close' => 'float',
        'adjusted_close' => 'float',
        'volume' => 'integer',
    ];

    /**
     * Obtiene el instrumento al que pertenece este precio
     */
    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }
}
