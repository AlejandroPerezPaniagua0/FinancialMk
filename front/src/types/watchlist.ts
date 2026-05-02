/**
 * Watchlist shapes returned by /api/watchlists.
 *
 * The instruments embedded inside a watchlist are a thinner shape than the
 * full `Instrument` (no nested objects, just denormalized strings) because
 * the backend joins them in WatchlistDTO. Don't try to use a full
 * `Instrument` — that's a separate fetch.
 */
export interface WatchlistInstrument {
  id: number
  ticker: string
  name: string
  asset_class: string | null
  currency: string | null
  position: number
}

export interface Watchlist {
  id: number
  name: string
  is_default: boolean
  instruments: WatchlistInstrument[]
  created_at: string
  updated_at: string
}
