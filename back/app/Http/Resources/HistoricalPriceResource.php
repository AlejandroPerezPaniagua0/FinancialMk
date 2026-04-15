<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoricalPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date'           => $this->date->toDateString(),
            'open'           => $this->open,
            'high'           => $this->high,
            'low'            => $this->low,
            'close'          => $this->close,
            'adjusted_close' => $this->adjusted_close,
            'volume'         => $this->volume,
        ];
    }
}
