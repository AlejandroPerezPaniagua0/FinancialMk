import { useCorrelationMatrix } from '@/hooks/useMarket'

/**
 * N×N correlation heatmap for the selected instruments.
 *
 * Color encoding:
 *   • +1.0 → strong blue (perfectly correlated)
 *   • 0    → neutral background
 *   • -1.0 → strong red  (anti-correlated)
 */
export default function CorrelationMatrix({ instrumentIds }: { instrumentIds: number[] }) {
  const { data, isLoading, isError } = useCorrelationMatrix(instrumentIds)

  if (instrumentIds.length < 2) return null

  if (isLoading) {
    return (
      <div className="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <p className="text-sm text-gray-400 dark:text-gray-500 animate-pulse">Computing correlation matrix…</p>
      </div>
    )
  }

  if (isError || !data || data.tickers.length < 2) {
    return (
      <div className="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <p className="text-sm text-gray-500 dark:text-gray-400">
          Not enough overlapping history to build a correlation matrix.
        </p>
      </div>
    )
  }

  return (
    <section className="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
      <header className="mb-3 flex flex-wrap items-baseline justify-between gap-2">
        <h2 className="text-sm font-semibold text-gray-700 dark:text-gray-200">Correlation matrix</h2>
        <p className="text-xs text-gray-400 dark:text-gray-500">
          {data.samples} samples · {data.range_start} → {data.range_end}
        </p>
      </header>

      <div className="overflow-x-auto">
        <table className="min-w-full text-sm border-collapse">
          <thead>
            <tr>
              <th className="p-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400" />
              {data.tickers.map((t) => (
                <th
                  key={t}
                  className="p-2 text-center text-xs font-medium text-gray-700 dark:text-gray-300"
                >
                  {t}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {data.tickers.map((row, i) => (
              <tr key={row}>
                <th className="p-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300">{row}</th>
                {data.tickers.map((col, j) => {
                  const value = data.matrix[i]?.[j]
                  return (
                    <td
                      key={`${row}-${col}`}
                      className="p-0 text-center text-xs font-semibold tabular-nums"
                    >
                      <div
                        className="px-3 py-2"
                        style={{
                          background: cellBg(value),
                          color: cellColor(value),
                        }}
                        title={value === null ? 'No overlap' : value.toFixed(4)}
                      >
                        {value === null ? '—' : value.toFixed(2)}
                      </div>
                    </td>
                  )
                })}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </section>
  )
}

/** Map -1..1 to a blue/neutral/red gradient. Returns RGBA. */
function cellBg(value: number | null | undefined): string {
  if (value === null || value === undefined) return 'transparent'
  if (value > 0) {
    // 0 → transparent, +1 → blue
    return `rgba(37, 99, 235, ${Math.min(1, value).toFixed(3)})`
  }
  // 0 → transparent, -1 → red
  return `rgba(220, 38, 38, ${Math.min(1, -value).toFixed(3)})`
}

/** Switch text to white once the cell is dark enough that black-on-color hurts. */
function cellColor(value: number | null | undefined): string {
  if (value === null || value === undefined) return 'inherit'
  return Math.abs(value) > 0.5 ? '#fff' : 'inherit'
}
