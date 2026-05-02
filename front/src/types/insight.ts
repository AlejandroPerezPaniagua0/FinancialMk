/**
 * Shape returned by GET /api/instruments/{id}/insights.
 *
 * All metrics are nullable because we may not have enough history yet.
 * Render `—` for null fields rather than guessing a default.
 */
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

/** Shape returned by GET /api/instruments/correlation. */
export interface CorrelationMatrix {
  tickers: string[]
  /** matrix[i][j] is the correlation between tickers[i] and tickers[j]. */
  matrix: (number | null)[][]
  range_start: string | null
  range_end: string | null
  samples: number
}
