import { useMemo } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import ComparisonGrid from '@/components/ComparisonGrid'
import { useComparisonSelection } from '@/contexts/ComparisonSelectionContext'
import { useInstruments } from '@/hooks/useMarket'
import { useQuotesPolling } from '@/hooks/useQuotesPolling'
import { MIN_COMPARISON_ASSETS } from '@/types/market'
import type { Instrument, Quote } from '@/types/market'

export default function ComparisonPage() {
  const navigate = useNavigate()
  const { selectedIds, remove, clear } = useComparisonSelection()

  // We need at least 2 assets for the grid to be meaningful. While the
  // selection is below that threshold we bounce the user back to the list.
  const hasEnough = selectedIds.length >= MIN_COMPARISON_ASSETS

  // Pull enough instruments to resolve metadata for any selection. The list
  // endpoint is paginated but the selection is always 2-4 ids, so this is
  // a one-shot lookup that React Query will cache.
  const { data: instrumentsPage } = useInstruments({ per_page: 100 })

  const selectedInstruments = useMemo<Instrument[]>(() => {
    const all = instrumentsPage?.data ?? []
    const byId = new Map(all.map((i) => [i.id, i]))
    return selectedIds
      .map((id) => byId.get(id))
      .filter((i): i is Instrument => i !== undefined)
  }, [instrumentsPage, selectedIds])

  const { quotes, lastUpdatedAt, isLoading, isRefreshing, error, throttleSeconds, refresh } =
    useQuotesPolling(selectedIds, { enabled: hasEnough, intervalMs: 30_000 })

  const quotesById = useMemo<Map<number, Quote>>(
    () => new Map(quotes.map((q) => [q.instrument_id, q])),
    [quotes],
  )

  function handleClose() {
    clear()
    navigate('/instruments')
  }

  if (!hasEnough) {
    return (
      <div className="mx-auto max-w-lg rounded-xl border border-gray-200 bg-white p-8 text-center space-y-3 dark:border-gray-800 dark:bg-gray-900">
        <h1 className="text-lg font-semibold text-gray-900 dark:text-white">Pick at least 2 assets</h1>
        <p className="text-sm text-gray-500 dark:text-gray-400">
          Open the instruments list and select between 2 and 4 instruments to compare side by side.
        </p>
        <Link
          to="/instruments"
          className="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
        >
          Browse instruments
        </Link>
      </div>
    )
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Asset comparison</h1>
          <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Comparing {selectedIds.length} assets · refreshing every {throttleSeconds ?? 30}s
            {lastUpdatedAt && (
              <> · last update {new Date(lastUpdatedAt).toLocaleTimeString()}</>
            )}
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            type="button"
            onClick={refresh}
            disabled={isRefreshing || isLoading}
            className="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
          >
            {isRefreshing ? 'Refreshing…' : 'Refresh now'}
          </button>
          <button
            type="button"
            onClick={handleClose}
            className="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
          >
            Close
          </button>
        </div>
      </div>

      {error && (
        <div className="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
          {error}
        </div>
      )}

      {isLoading && selectedInstruments.length === 0 ? (
        <div className="py-12 text-center text-sm text-gray-500 animate-pulse dark:text-gray-400">
          Loading comparison…
        </div>
      ) : (
        <ComparisonGrid
          instruments={selectedInstruments}
          quotesById={quotesById}
          onRemove={remove}
        />
      )}
    </div>
  )
}
