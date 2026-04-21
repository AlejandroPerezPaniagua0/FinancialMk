<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'instrument_id'   => $this['instrument_id'],
            'ticker'          => $this['ticker'],
            'price'           => $this['price'],
            'previous_close'  => $this['previous_close'],
            'change'          => $this['change'],
            'change_percent'  => $this['change_percent'],
            'currency'        => $this['currency'],
            'fetched_at'      => $this['fetched_at'],
            'cached'          => $this['cached'],
            'next_refresh_in' => $this['next_refresh_in'],
        ];
    }
}
