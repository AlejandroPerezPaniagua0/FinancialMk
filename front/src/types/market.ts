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
