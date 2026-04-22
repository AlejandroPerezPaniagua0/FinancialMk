import {
  Area,
  AreaChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'
import type { HistoricalPrice } from '@/types/market'

interface PriceChartProps {
  prices: HistoricalPrice[]
  ticker: string
}

interface TooltipPayload {
  payload?: { date: string; close: number; open: number; high: number; low: number; volume: number }
}

function CustomTooltip({ payload }: TooltipPayload) {
  if (!payload) return null
  const d = payload
  return (
    <div className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs shadow-lg space-y-1 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
      <p className="font-semibold text-gray-700 dark:text-gray-100">{d.date}</p>
      <p>Open: <span className="font-medium">{d.open?.toFixed(2)}</span></p>
      <p>High: <span className="font-medium text-green-600 dark:text-green-400">{d.high?.toFixed(2)}</span></p>
      <p>Low: <span className="font-medium text-red-500 dark:text-red-400">{d.low?.toFixed(2)}</span></p>
      <p>Close: <span className="font-medium">{d.close?.toFixed(2)}</span></p>
      <p className="text-gray-400 dark:text-gray-500">Vol: {d.volume?.toLocaleString()}</p>
    </div>
  )
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

export default function PriceChart({ prices, ticker }: PriceChartProps) {
  if (prices.length === 0) {
    return (
      <div className="flex items-center justify-center h-64 text-sm text-gray-400 dark:text-gray-500">
        No price data available for the selected range.
      </div>
    )
  }

  const minClose = Math.min(...prices.map((p) => p.close))
  const maxClose = Math.max(...prices.map((p) => p.close))
  const padding = (maxClose - minClose) * 0.05

  const isPositive = prices[prices.length - 1]!.close >= prices[0]!.close

  return (
    <div>
      <p className="text-xs text-gray-500 mb-2 dark:text-gray-400">{ticker} — Close price</p>
      <ResponsiveContainer width="100%" height={300}>
        <AreaChart data={prices} margin={{ top: 4, right: 8, left: 0, bottom: 0 }}>
          <defs>
            <linearGradient id="priceGradient" x1="0" y1="0" x2="0" y2="1">
              <stop
                offset="5%"
                stopColor={isPositive ? '#2563eb' : '#ef4444'}
                stopOpacity={0.2}
              />
              <stop
                offset="95%"
                stopColor={isPositive ? '#2563eb' : '#ef4444'}
                stopOpacity={0}
              />
            </linearGradient>
          </defs>
          <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
          <XAxis
            dataKey="date"
            tickFormatter={formatDate}
            tick={{ fontSize: 11, fill: '#9ca3af' }}
            tickLine={false}
            axisLine={false}
            minTickGap={40}
          />
          <YAxis
            domain={[minClose - padding, maxClose + padding]}
            tick={{ fontSize: 11, fill: '#9ca3af' }}
            tickLine={false}
            axisLine={false}
            tickFormatter={(v: number) => v.toFixed(0)}
            width={48}
          />
          <Tooltip
            content={({ payload }) => (
              // eslint-disable-next-line @typescript-eslint/no-explicit-any
              <CustomTooltip payload={(payload as any)?.[0]?.payload} />
            )}
          />
          <Area
            type="monotone"
            dataKey="close"
            stroke={isPositive ? '#2563eb' : '#ef4444'}
            strokeWidth={1.5}
            fill="url(#priceGradient)"
            dot={false}
            activeDot={{ r: 4 }}
          />
        </AreaChart>
      </ResponsiveContainer>
    </div>
  )
}
