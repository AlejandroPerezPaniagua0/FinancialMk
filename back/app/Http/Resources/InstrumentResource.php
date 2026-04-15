<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstrumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'ticker'      => $this->ticker,
            'asset_class' => new AssetClassResource($this->whenLoaded('assetClass')),
            'currency'    => new CurrencyResource($this->whenLoaded('currency')),
        ];
    }
}
