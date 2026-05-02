export interface AssetClass {
  id: number
  name: string
}

export interface Currency {
  id: number
  name: string
  iso_code: string
}

export interface Instrument {
  id: number
  name: string
  ticker: string
  asset_class: AssetClass
  currency: Currency
}

export interface HistoricalPrice {
  date: string
  open: number
  high: number
  low: number
  close: number
  adjusted_close: number
  volume: number
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
  }
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
}

export interface CollectionResponse<T> {
  data: T[]
}

export interface Quote {
  instrument_id: number
  ticker: string
  price: number | null
  previous_close: number | null
  change: number | null
  change_percent: number | null
  currency: string | null
  fetched_at: string
  cached: boolean
  next_refresh_in: number
}

export interface QuotesResponse {
  data: Quote[]
  meta: {
    throttle_seconds: number
    max_assets: number
    server_time: string
  }
}

export interface Insight {
  instrument_id: number
  ticker: string
  volatility_30d_annualized: number | null
  max_drawdown_1y: number | null
  correlation_with_benchmark: number | null
  benchmark_ticker: string | null
  samples: number
  range_start: string | null
  range_end: string | null
}

export interface CorrelationMatrix {
  tickers: string[]
  matrix: Array<Array<number | null>>
  range_start: string | null
  range_end: string | null
  samples: number
}

export const MAX_COMPARISON_ASSETS = 4
export const MIN_COMPARISON_ASSETS = 2
