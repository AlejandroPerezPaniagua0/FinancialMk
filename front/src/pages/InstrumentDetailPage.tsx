import { useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import DateRangePicker from '@/components/DateRangePicker'
import PriceChart from '@/components/PriceChart'
import { useInstruments, usePrices } from '@/hooks/useMarket'

function toISODate(date: Date): string {
  return date.toISOString().split('T')[0]!
}

function defaultDateRange() {
  const to = new Date()
  const from = new Date()
  from.setFullYear(from.getFullYear() - 1)
  return { from: toISODate(from), to: toISODate(to) }
}

export default function InstrumentDetailPage() {
  const { id } = useParams<{ id: string }>()
  const instrumentId = Number(id ?? 0)

  const [dateRange, setDateRange] = useState(defaultDateRange)

  // Fetch instrument details (reuse instruments list — small overhead for now)
  const { data: instrumentsPage } = useInstruments({ per_page: 100 })
  const instrument = instrumentsPage?.data.find((i) => i.id === instrumentId)

  const { data: prices = [], isLoading: pricesLoading } = usePrices(instrumentId, {
    from: dateRange.from,
    to: dateRange.to,
  })

  if (!instrument && !pricesLoading) {
    return (
      <div className="text-center py-16 text-sm text-gray-500">
        Instrument not found.{' '}
        <Link to="/instruments" className="text-blue-600 hover:underline">
          Back to instruments
        </Link>
      </div>
    )
  }

  const firstPrice = prices[0]
  const lastPrice = prices[prices.length - 1]
  const change =
    firstPrice && lastPrice ? lastPrice.close - firstPrice.close : null
  const changePct =
    firstPrice && lastPrice && firstPrice.close !== 0
      ? ((lastPrice.close - firstPrice.close) / firstPrice.close) * 100
      : null

  return (
    <div className="space-y-6 max-w-4xl">
      {/* Breadcrumb */}
      <nav className="text-sm text-gray-500">
        <Link to="/instruments" className="hover:text-gray-700">
          Instruments
        </Link>
        {' / '}
        <span className="text-gray-900 font-medium">{instrument?.ticker ?? '…'}</span>
      </nav>

      {/* Header */}
      <div className="flex items-start gap-4 flex-wrap">
        <div className="flex-1">
          <h1 className="text-2xl font-bold text-gray-900">
            {instrument?.name ?? '…'}
          </h1>
          <p className="mt-1 text-sm text-gray-500">
            {instrument?.ticker} · {instrument?.asset_class.name} ·{' '}
            {instrument?.currency.iso_code}
          </p>
        </div>

        {lastPrice && (
          <div className="text-right">
            <p className="text-3xl font-bold text-gray-900">
              {lastPrice.close.toFixed(2)}
            </p>
            {change !== null && changePct !== null && (
              <p
                className={`text-sm font-medium ${
                  change >= 0 ? 'text-green-600' : 'text-red-500'
                }`}
              >
                {change >= 0 ? '+' : ''}
                {change.toFixed(2)} ({changePct.toFixed(2)}%)
              </p>
            )}
          </div>
        )}
      </div>

      {/* Chart card */}
      <div className="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <DateRangePicker
          from={dateRange.from}
          to={dateRange.to}
          onChange={(from, to) => setDateRange({ from: from ?? '', to: to ?? '' })}
        />

        {pricesLoading ? (
          <div className="h-64 flex items-center justify-center text-sm text-gray-400 animate-pulse">
            Loading chart…
          </div>
        ) : (
          <PriceChart prices={prices} ticker={instrument?.ticker ?? ''} />
        )}
      </div>

      {/* OHLCAV summary */}
      {lastPrice && (
        <div className="bg-white rounded-xl border border-gray-200 p-6">
          <h2 className="text-sm font-semibold text-gray-700 mb-4">Latest session</h2>
          <div className="grid grid-cols-3 gap-4 sm:grid-cols-6 text-center">
            {[
              { label: 'Open', value: lastPrice.open.toFixed(2) },
              { label: 'High', value: lastPrice.high.toFixed(2) },
              { label: 'Low', value: lastPrice.low.toFixed(2) },
              { label: 'Close', value: lastPrice.close.toFixed(2) },
              { label: 'Adj. Close', value: lastPrice.adjusted_close.toFixed(2) },
              { label: 'Volume', value: lastPrice.volume.toLocaleString() },
            ].map(({ label, value }) => (
              <div key={label}>
                <p className="text-xs text-gray-400">{label}</p>
                <p className="mt-1 font-semibold text-gray-900">{value}</p>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
