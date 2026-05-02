import { useQueries } from '@tanstack/react-query'
import { useMemo } from 'react'
import {
  CartesianGrid,
  Legend,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'
import { fetchPrices } from '@/api/market'
import { useTheme } from '@/contexts/ThemeContext'
import type { HistoricalPrice } from '@/types/market'

/**
 * Multi-asset chart with all series rebased to 100 on the first overlapping
 * date. Lets the user compare *shape* (% evolution) regardless of absolute
 * price — solves the "AAPL at $192 vs BTC at $67k" scale problem cleanly.
 */
interface Props {
  instruments: { id: number; ticker: string }[]
  from?: string
  to?: string
}

const COLORS = ['#2563eb', '#0891b2', '#22d3ee', '#a855f7', '#ec4899', '#f59e0b']

interface ChartPoint {
  date: string
  [ticker: string]: number | string
}

export default function NormalizedPriceChart({ instruments, from, to }: Props) {
  const { theme } = useTheme()
  const isDark = theme === 'dark'

  // Pull each series independently via useQueries — using a fixed-shape
  // hook here is what keeps React's hook order stable when the user adds
  // or removes an instrument from the comparison.
  const queries = useQueries({
    queries: instruments.map((i) => ({
      queryKey: ['prices', i.id, { from, to }],
      queryFn: () => fetchPrices(i.id, { from, to }),
      enabled: i.id > 0,
    })),
  })

  const isLoading = queries.some((q) => q.isLoading)

  const data = useMemo<ChartPoint[]>(() => {
    if (queries.some((q) => !q.data || q.data.length === 0)) {
      return []
    }

    // Build a map per ticker for cheap O(1) lookup, plus a sorted list of
    // overlapping dates so missing days don't break the rebase math.
    const seriesByTicker = new Map<string, Map<string, number>>()
    instruments.forEach((instrument, idx) => {
      const rows = (queries[idx]?.data ?? []) as HistoricalPrice[]
      const m = new Map<string, number>()
      for (const row of rows) {
        m.set(row.date, row.adjusted_close)
      }
      seriesByTicker.set(instrument.ticker, m)
    })

    const dateSets = [...seriesByTicker.values()].map((m) => new Set(m.keys()))
    if (dateSets.length === 0) return []

    const overlap = [...dateSets[0]!].filter((d) =>
      dateSets.every((set) => set.has(d)),
    ).sort()

    if (overlap.length === 0) return []

    // Rebase each series to 100 on the first overlapping date.
    const baselines = new Map<string, number>()
    for (const [ticker, m] of seriesByTicker) {
      baselines.set(ticker, m.get(overlap[0]!) ?? 0)
    }

    return overlap.map((date) => {
      const point: ChartPoint = { date }
      for (const [ticker, m] of seriesByTicker) {
        const baseline = baselines.get(ticker) ?? 0
        const close = m.get(date) ?? 0
        point[ticker] = baseline > 0 ? (close / baseline) * 100 : 0
      }
      return point
    })
  }, [queries, instruments])

  if (isLoading) {
    return (
      <div className="h-72 flex items-center justify-center text-sm text-gray-400 animate-pulse dark:text-gray-500">
        Loading comparison chart…
      </div>
    )
  }

  if (data.length === 0) {
    return (
      <div className="h-72 flex items-center justify-center text-sm text-gray-500 dark:text-gray-400">
        Not enough overlapping history to compare these instruments.
      </div>
    )
  }

  const gridColor = isDark ? '#1f2937' : '#e5e7eb'
  const axisColor = isDark ? '#9ca3af' : '#6b7280'

  return (
    <div className="h-72">
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={data} margin={{ top: 10, right: 16, left: 0, bottom: 0 }}>
          <CartesianGrid stroke={gridColor} strokeDasharray="3 3" />
          <XAxis dataKey="date" tick={{ fontSize: 11, fill: axisColor }} minTickGap={32} />
          <YAxis
            tick={{ fontSize: 11, fill: axisColor }}
            tickFormatter={(v: number) => v.toFixed(0)}
            domain={['auto', 'auto']}
            label={{
              value: 'Rebased to 100',
              angle: -90,
              position: 'insideLeft',
              style: { fill: axisColor, fontSize: 11 },
            }}
          />
          <Tooltip
            formatter={(v: number) => v.toFixed(2)}
            contentStyle={{
              background: isDark ? '#111827' : '#fff',
              border: `1px solid ${gridColor}`,
              fontSize: 12,
            }}
          />
          <Legend wrapperStyle={{ fontSize: 12 }} />
          {instruments.map((i, idx) => (
            <Line
              key={i.id}
              type="monotone"
              dataKey={i.ticker}
              stroke={COLORS[idx % COLORS.length]}
              dot={false}
              strokeWidth={2}
            />
          ))}
        </LineChart>
      </ResponsiveContainer>
    </div>
  )
}
