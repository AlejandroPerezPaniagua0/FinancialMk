import { useInsight } from '@/hooks/useMarket'

/**
 * Risk/return summary card for the instrument detail page.
 *
 * Computed locally from historical prices already in the database — no
 * external call, no "premium" gating. Renders an em-dash for any metric
 * where we don't have enough history to compute confidently.
 */
export default function InsightCard({
  instrumentId,
  benchmark,
}: {
  instrumentId: number
  benchmark?: string
}) {
  const { data, isLoading, isError } = useInsight(instrumentId, benchmark)

  if (isLoading) {
    return (
      <div className="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <p className="text-sm text-gray-400 dark:text-gray-500 animate-pulse">Computing insights…</p>
      </div>
    )
  }

  if (isError || !data) {
    return (
      <div className="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <p className="text-sm text-gray-500 dark:text-gray-400">Insights unavailable.</p>
      </div>
    )
  }

  const benchmarkLabel = data.benchmark_ticker ?? 'SPY'

  return (
    <section className="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
      <header className="mb-4 flex flex-wrap items-baseline justify-between gap-2">
        <h2 className="text-sm font-semibold text-gray-700 dark:text-gray-200">Insights</h2>
        <p className="text-xs text-gray-400 dark:text-gray-500">
          Computed locally · {data.samples} samples
          {data.range_start && data.range_end && (
            <> · {data.range_start} → {data.range_end}</>
          )}
        </p>
      </header>

      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <Metric
          label="Volatility (30d, annualized)"
          value={formatPercent(data.volatility_30d_annualized)}
          hint="Standard deviation of daily log returns × √252."
        />
        <Metric
          label="Max drawdown (1y)"
          value={formatPercent(data.max_drawdown_1y, { sign: 'negative' })}
          hint="Largest peak-to-trough decline in the last year."
          tone={data.max_drawdown_1y !== null && data.max_drawdown_1y < 0 ? 'negative' : 'neutral'}
        />
        <Metric
          label={`Correlation vs ${benchmarkLabel}`}
          value={formatNumber(data.correlation_with_benchmark, 2)}
          hint="Pearson correlation of daily log returns over the overlap range."
        />
      </div>
    </section>
  )
}

function Metric({
  label,
  value,
  hint,
  tone = 'neutral',
}: {
  label: string
  value: string
  hint: string
  tone?: 'neutral' | 'negative'
}) {
  const valueClass =
    tone === 'negative'
      ? 'text-red-600 dark:text-red-400'
      : 'text-gray-900 dark:text-white'

  return (
    <div>
      <p className="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">{label}</p>
      <p className={`mt-1 text-2xl font-semibold tabular-nums ${valueClass}`}>{value}</p>
      <p className="mt-1 text-xs text-gray-400 dark:text-gray-500">{hint}</p>
    </div>
  )
}

function formatPercent(value: number | null, { sign }: { sign?: 'negative' } = {}): string {
  if (value === null || Number.isNaN(value)) return '—'
  const pct = value * 100
  if (sign === 'negative') return `${pct.toFixed(2)}%`
  const prefix = pct >= 0 ? '+' : ''
  return `${prefix}${pct.toFixed(2)}%`
}

function formatNumber(value: number | null, fractionDigits: number): string {
  if (value === null || Number.isNaN(value)) return '—'
  return value.toFixed(fractionDigits)
}
