import { useEffect, useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import ExportButton from '@/components/ExportButton'
import { useInstruments } from '@/hooks/useMarket'
import {
  useCreateWatchlist,
  useDeleteWatchlist,
  useRenameWatchlist,
  useSyncWatchlistInstruments,
  useWatchlists,
} from '@/hooks/useWatchlists'
import type { Instrument } from '@/types/market'
import type { Watchlist, WatchlistInstrument } from '@/types/watchlist'

/**
 * Watchlists page — lets the user manage simple lists of instruments to
 * keep an eye on. This is intentionally NOT portfolio tracking: no
 * positions, no costs, no tax lots.
 */
export default function WatchlistsPage() {
  const { data: watchlists, isLoading, isError } = useWatchlists()
  const createMut  = useCreateWatchlist()
  const deleteMut  = useDeleteWatchlist()

  const [newName, setNewName] = useState('')

  function handleCreate(e: React.FormEvent) {
    e.preventDefault()
    const name = newName.trim()
    if (!name) return
    createMut.mutate({ name }, { onSuccess: () => setNewName('') })
  }

  if (isLoading) {
    return <p className="text-sm text-gray-500 dark:text-gray-400">Loading watchlists…</p>
  }
  if (isError) {
    return <p className="text-sm text-red-600">Could not load watchlists.</p>
  }

  return (
    <div className="space-y-6">
      <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div className="min-w-0">
          <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Watchlists</h1>
          <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Keep an eye on instruments. Analysis only — no positions, no portfolio tracking.
          </p>
        </div>

        <form
          onSubmit={handleCreate}
          className="flex w-full items-center gap-2 sm:w-auto sm:shrink-0"
        >
          <input
            value={newName}
            onChange={(e) => setNewName(e.target.value)}
            placeholder="New watchlist name"
            className="w-full min-w-0 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500 sm:w-64 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500"
          />
          <button
            type="submit"
            disabled={createMut.isPending || newName.trim() === ''}
            className="shrink-0 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed dark:disabled:bg-gray-700 dark:disabled:text-gray-400"
          >
            {createMut.isPending ? 'Creating…' : 'Create'}
          </button>
        </form>
      </header>

      {watchlists && watchlists.length === 0 && (
        <div className="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
          You don&apos;t have any watchlists yet. Create one above.
        </div>
      )}

      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {watchlists?.map((wl) => (
          <WatchlistCard
            key={wl.id}
            watchlist={wl}
            onDelete={() => deleteMut.mutate(wl.id)}
          />
        ))}
      </div>
    </div>
  )
}

function WatchlistCard({
  watchlist,
  onDelete,
}: {
  watchlist: Watchlist
  onDelete: () => void
}) {
  const renameMut = useRenameWatchlist()
  const syncMut   = useSyncWatchlistInstruments()
  const [editing, setEditing] = useState(false)
  const [name, setName] = useState(watchlist.name)

  function startEditing() {
    setName(watchlist.name)
    setEditing(true)
  }

  function cancelEditing() {
    setEditing(false)
    setName(watchlist.name)
  }

  function handleRemove(item: WatchlistInstrument) {
    const remaining = watchlist.instruments
      .filter((i) => i.id !== item.id)
      .map((i) => i.id)
    syncMut.mutate({ id: watchlist.id, instrumentIds: remaining })
  }

  function handleAdd(instrumentId: number) {
    const ids = [...watchlist.instruments.map((i) => i.id), instrumentId]
    syncMut.mutate({ id: watchlist.id, instrumentIds: ids })
  }

  return (
    <article className="flex h-full flex-col rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
      <header className="mb-3 space-y-2">
        {editing ? (
          <form
            onSubmit={(e) => {
              e.preventDefault()
              const trimmed = name.trim() || watchlist.name
              renameMut.mutate(
                { id: watchlist.id, name: trimmed },
                { onSuccess: () => setEditing(false) },
              )
            }}
            className="flex items-center gap-2"
          >
            <input
              autoFocus
              value={name}
              onChange={(e) => setName(e.target.value)}
              className="min-w-0 flex-1 rounded border border-gray-300 px-2 py-1 text-sm outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
            <button
              type="submit"
              disabled={renameMut.isPending || name.trim() === ''}
              className="shrink-0 rounded bg-blue-600 px-2 py-1 text-xs font-medium text-white hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-500 dark:disabled:bg-gray-700"
            >
              {renameMut.isPending ? 'Saving…' : 'Save'}
            </button>
            <button
              type="button"
              onClick={cancelEditing}
              className="shrink-0 rounded px-2 py-1 text-xs text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
            >
              Cancel
            </button>
          </form>
        ) : (
          <>
            <div className="flex items-start justify-between gap-2">
              <h2
                className="flex min-w-0 items-center gap-2 text-base font-semibold text-gray-900 dark:text-white"
                title={watchlist.name}
              >
                <span className="truncate">{watchlist.name}</span>
                {watchlist.is_default && (
                  <span className="shrink-0 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                    Default
                  </span>
                )}
              </h2>

              <div className="flex shrink-0 items-center gap-1 text-xs">
                <button
                  type="button"
                  onClick={startEditing}
                  className="rounded px-2 py-1 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                >
                  Rename
                </button>
                <button
                  type="button"
                  onClick={onDelete}
                  className="rounded px-2 py-1 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                >
                  Delete
                </button>
              </div>
            </div>

            <ExportButton
              endpoint={`export/watchlists/${watchlist.id}`}
              filenameBase={`watchlist_${watchlist.id}`}
            />
          </>
        )}
      </header>

      <div className="flex-1">
        {watchlist.instruments.length === 0 ? (
          <p className="py-3 text-sm text-gray-500 dark:text-gray-400">
            Empty. Add an instrument below.
          </p>
        ) : (
          <ul className="divide-y divide-gray-100 dark:divide-gray-800">
            {watchlist.instruments.map((i) => (
              <li key={i.id} className="flex items-center justify-between gap-2 py-2 text-sm">
                <Link
                  to={`/instruments/${i.id}`}
                  className="flex min-w-0 items-center gap-2 text-gray-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                >
                  <span className="font-mono font-semibold">{i.ticker}</span>
                  <span className="truncate text-gray-500 dark:text-gray-400">{i.name}</span>
                </Link>
                <button
                  type="button"
                  onClick={() => handleRemove(i)}
                  aria-label={`Remove ${i.ticker} from ${watchlist.name}`}
                  className="shrink-0 rounded px-2 py-1 text-xs text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                >
                  Remove
                </button>
              </li>
            ))}
          </ul>
        )}
      </div>

      <AddInstrumentForm
        watchlist={watchlist}
        onAdd={handleAdd}
        pending={syncMut.isPending}
      />
    </article>
  )
}

/**
 * Inline combobox: filter the catalog by ticker OR name (case-insensitive
 * substring match) and pick a result. Replaces the previous <datalist>
 * approach, which only worked when the user typed the ticker exactly.
 *
 * Why a custom dropdown instead of a library? The catalog is small (≤500),
 * the UX surface is intentionally minimal, and we avoid pulling in a
 * combobox dep just for one place.
 */
function AddInstrumentForm({
  watchlist,
  onAdd,
  pending,
}: {
  watchlist: Watchlist
  onAdd: (instrumentId: number) => void
  pending: boolean
}) {
  // 100 is the backend's max for per_page; that's enough to cover the
  // catalog used in the autocomplete and lets us share TanStack Query's
  // cache with InstrumentDetailPage and ComparisonPage.
  const { data: instrumentsPage } = useInstruments({ per_page: 100 })
  const [query, setQuery] = useState('')
  const [open, setOpen] = useState(false)
  const wrapperRef = useRef<HTMLDivElement>(null)

  // Click-outside closes the suggestion list. Pointerdown beats blur to
  // simplify the interaction model — selection clicks below run before close.
  useEffect(() => {
    function onPointerDown(event: PointerEvent) {
      if (!wrapperRef.current) return
      if (!wrapperRef.current.contains(event.target as Node)) {
        setOpen(false)
      }
    }
    document.addEventListener('pointerdown', onPointerDown)
    return () => document.removeEventListener('pointerdown', onPointerDown)
  }, [])

  const matches = useMemo<Instrument[]>(() => {
    const existing = new Set(watchlist.instruments.map((i) => i.id))
    const all = (instrumentsPage?.data ?? []).filter((i) => !existing.has(i.id))
    const q = query.trim().toLowerCase()
    if (!q) return all.slice(0, 8)
    return all
      .filter((i) => i.ticker.toLowerCase().includes(q) || i.name.toLowerCase().includes(q))
      .slice(0, 8)
  }, [instrumentsPage, watchlist.instruments, query])

  function commitAdd(instrumentId: number) {
    onAdd(instrumentId)
    setQuery('')
    setOpen(false)
  }

  function handleKeyDown(e: React.KeyboardEvent<HTMLInputElement>) {
    if (e.key === 'Enter') {
      e.preventDefault()
      const first = matches[0]
      if (first) commitAdd(first.id)
    } else if (e.key === 'Escape') {
      setOpen(false)
    }
  }

  return (
    <div
      ref={wrapperRef}
      className="relative mt-3 border-t border-gray-100 pt-3 dark:border-gray-800"
    >
      <input
        type="text"
        value={query}
        onChange={(e) => {
          setQuery(e.target.value)
          setOpen(true)
        }}
        onFocus={() => setOpen(true)}
        onKeyDown={handleKeyDown}
        placeholder="Add instrument by ticker or name…"
        disabled={pending}
        aria-label={`Add instrument to ${watchlist.name}`}
        className="w-full rounded border border-gray-300 px-3 py-1.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
      />

      {open && matches.length > 0 && (
        <ul className="absolute left-0 right-0 z-20 mt-1 max-h-60 overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
          {matches.map((i) => (
            <li key={i.id}>
              <button
                type="button"
                disabled={pending}
                onClick={() => commitAdd(i.id)}
                className="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-gray-100 disabled:opacity-50 dark:hover:bg-gray-700"
              >
                <span className="font-mono font-semibold text-gray-900 dark:text-white">
                  {i.ticker}
                </span>
                <span className="truncate text-gray-500 dark:text-gray-400">{i.name}</span>
                <span className="ml-auto shrink-0 text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500">
                  {i.asset_class.name}
                </span>
              </button>
            </li>
          ))}
        </ul>
      )}

      {open && query.trim() !== '' && matches.length === 0 && (
        <div className="absolute left-0 right-0 z-20 mt-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-500 shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
          No instruments match.
        </div>
      )}
    </div>
  )
}
