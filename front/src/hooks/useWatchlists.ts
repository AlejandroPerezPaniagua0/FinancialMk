import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  createWatchlist,
  deleteWatchlist,
  listWatchlists,
  renameWatchlist,
  syncWatchlistInstruments,
} from '@/api/watchlists'
import type { Watchlist } from '@/types/watchlist'

const KEY = ['watchlists'] as const

/**
 * TanStack Query wrappers around the watchlist endpoints.
 *
 * We keep all mutations in this file so the cache invalidation strategy
 * stays in one place: every successful write invalidates the list query.
 * That's coarse-grained on purpose — watchlists are a small payload and
 * a 1-2 KB refetch is cheaper than reasoning about partial cache updates.
 */
export function useWatchlists() {
  return useQuery<Watchlist[]>({
    queryKey: KEY,
    queryFn: listWatchlists,
  })
}

export function useCreateWatchlist() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: createWatchlist,
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  })
}

export function useRenameWatchlist() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: ({ id, name }: { id: number; name: string }) => renameWatchlist(id, name),
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  })
}

export function useDeleteWatchlist() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: deleteWatchlist,
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  })
}

export function useSyncWatchlistInstruments() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: ({ id, instrumentIds }: { id: number; instrumentIds: number[] }) =>
      syncWatchlistInstruments(id, instrumentIds),
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  })
}
