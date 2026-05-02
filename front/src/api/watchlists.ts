import { apiClient } from './client'
import type { Watchlist } from '@/types/watchlist'

interface WatchlistEnvelope { data: Watchlist }
interface WatchlistListEnvelope { data: Watchlist[] }

export async function listWatchlists(): Promise<Watchlist[]> {
  const { data } = await apiClient.get<WatchlistListEnvelope>('/watchlists')
  return data.data
}

export async function getWatchlist(id: number): Promise<Watchlist> {
  const { data } = await apiClient.get<WatchlistEnvelope>(`/watchlists/${id}`)
  return data.data
}

export async function createWatchlist(input: { name: string; is_default?: boolean }): Promise<Watchlist> {
  const { data } = await apiClient.post<WatchlistEnvelope>('/watchlists', input)
  return data.data
}

export async function renameWatchlist(id: number, name: string): Promise<Watchlist> {
  const { data } = await apiClient.put<WatchlistEnvelope>(`/watchlists/${id}`, { name })
  return data.data
}

export async function deleteWatchlist(id: number): Promise<void> {
  await apiClient.delete(`/watchlists/${id}`)
}

/**
 * Replace the instruments in a watchlist. Order in the array drives the
 * stored `position`. Idempotent — sending the same array is a no-op.
 */
export async function syncWatchlistInstruments(
  id: number,
  instrumentIds: number[],
): Promise<Watchlist> {
  const { data } = await apiClient.put<WatchlistEnvelope>(`/watchlists/${id}/instruments`, {
    instrument_ids: instrumentIds,
  })
  return data.data
}
