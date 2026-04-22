import type { Instrument, Quote } from '@/types/market'

interface AssetComparisonCardProps {
  instrument: Instrument
  quote: Quote | null
  onRemove: () => void
}

function formatPrice(value: number | null | undefined): string {
  if (value === null || value === undefined || Number.isNaN(value)) return '—'
  return value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 })
}

function formatPercent(value: number | null | undefined): string {
  if (value === null || value === undefined || Number.isNaN(value)) return '—'
  const sign = value > 0 ? '+' : ''
  return `${sign}${value.toFixed(2)}%`
}

function formatChange(value: number | null | undefined): string {
  if (value === null || value === undefined || Number.isNaN(value)) return '—'
  const sign = value > 0 ? '+' : ''
  return `${sign}${value.toFixed(2)}`
}

export default function AssetComparisonCard({
  instrument,
  quote,
  onRemove,
}: AssetComparisonCardProps) {
  const change = quote?.change ?? null
  const changePercent = quote?.change_percent ?? null
  const direction = change === null ? 'flat' : change > 0 ? 'up' : change < 0 ? 'down' : 'flat'

  const badgeClass =
    direction === 'up'
      ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-950/40 dark:text-green-300 dark:border-green-900'
      : direction === 'down'
        ? 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-900'
        : 'bg-gray-50 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700'

  const currency = quote?.currency ?? instrument.currency.iso_code

  return (
    <div className="h-full flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
      <div className="flex items-start justify-between gap-2">
        <div className="min-w-0">
          <p className="font-mono text-base font-semibold text-gray-900 truncate dark:text-white">
            {instrument.ticker}
          </p>
          <p className="text-xs text-gray-500 truncate dark:text-gray-400" title={instrument.name}>
            {instrument.name}
          </p>
        </div>
        <button
          type="button"
          onClick={onRemove}
          className="shrink-0 rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-200"
          aria-label={`Remove ${instrument.ticker} from comparison`}
        >
          ×
        </button>
      </div>

      <div className="mt-5">
        <p className="text-2xl font-bold text-gray-900 tabular-nums dark:text-white">
          {formatPrice(quote?.price ?? null)}
          <span className="ml-2 text-sm font-medium text-gray-400 dark:text-gray-500">{currency}</span>
        </p>
        <div className="mt-1 flex items-center gap-2 text-xs">
          <span
            className={`inline-flex items-center rounded-full border px-2 py-0.5 font-medium tabular-nums ${badgeClass}`}
          >
            {formatChange(change)} ({formatPercent(changePercent)})
          </span>
        </div>
      </div>

      <div className="mt-auto pt-4 text-[11px] text-gray-400 flex items-center justify-between dark:text-gray-500">
        <span>{instrument.asset_class.name}</span>
        {quote?.fetched_at && (
          <span title={quote.fetched_at}>
            {quote.cached ? 'cached' : 'live'} · {new Date(quote.fetched_at).toLocaleTimeString()}
          </span>
        )}
      </div>
    </div>
  )
}
