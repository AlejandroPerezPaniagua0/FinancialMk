<?php

namespace App\DTOs\Watchlist;

use App\Models\Watchlist;

/**
 * Outgoing shape for watchlist endpoints. Embeds the included instruments
 * with their asset class + currency so the SPA only needs one round-trip
 * to render the page.
 */
class WatchlistDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly bool $isDefault,
        public readonly array $instruments,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public static function fromModel(Watchlist $watchlist): self
    {
        return new self(
            id: $watchlist->id,
            name: $watchlist->name,
            isDefault: (bool) $watchlist->is_default,
            instruments: $watchlist->relationLoaded('instruments')
                ? $watchlist->instruments->map(fn ($i) => [
                    'id' => $i->id,
                    'ticker' => $i->ticker,
                    'name' => $i->name,
                    'asset_class' => $i->relationLoaded('assetClass') ? $i->assetClass?->name : null,
                    'currency' => $i->relationLoaded('currency') ? $i->currency?->iso_code : null,
                    'position' => (int) ($i->pivot->position ?? 0),
                ])->all()
                : [],
            createdAt: (string) $watchlist->created_at,
            updatedAt: (string) $watchlist->updated_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_default' => $this->isDefault,
            'instruments' => $this->instruments,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
