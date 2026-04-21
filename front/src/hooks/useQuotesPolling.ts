import { useEffect, useRef, useState } from 'react'
import axios from 'axios'
import { fetchQuotes } from '@/api/market'
import type { Quote } from '@/types/market'

export interface UseQuotesPollingOptions {
  /** Polling cadence in ms. Server throttle guarantees upstream calls <= 30s. */
  intervalMs?: number
  /** Disables polling (e.g. when the user has not yet selected enough assets). */
  enabled?: boolean
}

export interface UseQuotesPollingResult {
  quotes: Quote[]
  lastUpdatedAt: string | null
  isLoading: boolean
  isRefreshing: boolean
  error: string | null
  throttleSeconds: number | null
  refresh: () => void
}

const DEFAULT_INTERVAL_MS = 30_000

/**
 * Polls the real-time quotes endpoint for the selected instruments.
 *
 * All work is cancelled on unmount or when `enabled` becomes false:
 *   - the scheduled interval is cleared
 *   - the in-flight axios request is aborted
 *   - late responses are ignored via a generation counter
 * This guarantees no stray network chatter survives closing the view.
 */
export function useQuotesPolling(
  instrumentIds: number[],
  options: UseQuotesPollingOptions = {},
): UseQuotesPollingResult {
  const { intervalMs = DEFAULT_INTERVAL_MS, enabled = true } = options

  const [quotes, setQuotes] = useState<Quote[]>([])
  const [lastUpdatedAt, setLastUpdatedAt] = useState<string | null>(null)
  const [isLoading, setIsLoading] = useState<boolean>(false)
  const [isRefreshing, setIsRefreshing] = useState<boolean>(false)
  const [error, setError] = useState<string | null>(null)
  const [throttleSeconds, setThrottleSeconds] = useState<number | null>(null)

  // Stable primitive key so identical id sets don't restart the interval.
  const idsKey = [...instrumentIds].sort((a, b) => a - b).join(',')
  const intervalRef = useRef<number | null>(null)
  const controllerRef = useRef<AbortController | null>(null)
  const generationRef = useRef<number>(0)
  const hasLoadedRef = useRef<boolean>(false)
  const [manualTick, setManualTick] = useState<number>(0)

  useEffect(() => {
    hasLoadedRef.current = false

    if (!enabled || idsKey === '') {
      setQuotes([])
      setLastUpdatedAt(null)
      setIsLoading(false)
      setIsRefreshing(false)
      setError(null)
      return
    }

    const ids = idsKey.split(',').map((v) => Number(v))
    const currentGeneration = ++generationRef.current

    const tick = async () => {
      // Abort any in-flight request before starting a new one.
      controllerRef.current?.abort()
      const controller = new AbortController()
      controllerRef.current = controller

      if (hasLoadedRef.current) {
        setIsRefreshing(true)
      } else {
        setIsLoading(true)
      }

      try {
        const response = await fetchQuotes(ids, controller.signal)
        if (generationRef.current !== currentGeneration) return
        setQuotes(response.data)
        setLastUpdatedAt(response.meta.server_time)
        setThrottleSeconds(response.meta.throttle_seconds)
        setError(null)
        hasLoadedRef.current = true
      } catch (err) {
        if (axios.isCancel(err) || (err instanceof DOMException && err.name === 'AbortError')) {
          return
        }
        if (generationRef.current !== currentGeneration) return
        const message =
          axios.isAxiosError(err) && err.response?.status === 429
            ? 'Rate limit reached — waiting before the next refresh.'
            : 'Could not load live quotes.'
        setError(message)
      } finally {
        if (generationRef.current === currentGeneration) {
          setIsLoading(false)
          setIsRefreshing(false)
        }
      }
    }

    // Kick off immediately, then on the configured cadence.
    void tick()
    intervalRef.current = window.setInterval(tick, intervalMs)

    return () => {
      // Invalidate this generation so any late response is dropped.
      generationRef.current++
      if (intervalRef.current !== null) {
        window.clearInterval(intervalRef.current)
        intervalRef.current = null
      }
      controllerRef.current?.abort()
      controllerRef.current = null
    }
  }, [idsKey, intervalMs, enabled, manualTick])

  function refresh() {
    setManualTick((v) => v + 1)
  }

  return { quotes, lastUpdatedAt, isLoading, isRefreshing, error, throttleSeconds, refresh }
}
