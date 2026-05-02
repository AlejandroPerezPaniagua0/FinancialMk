import { useQuery } from '@tanstack/react-query'
import {
  fetchAssetClasses,
  fetchCorrelationMatrix,
  fetchCurrencies,
  fetchInsight,
  fetchInstruments,
  fetchPrices,
  type FetchInstrumentsParams,
  type FetchPricesParams,
} from '@/api/market'

export function useAssetClasses() {
  return useQuery({
    queryKey: ['asset-classes'],
    queryFn: fetchAssetClasses,
  })
}

export function useCurrencies() {
  return useQuery({
    queryKey: ['currencies'],
    queryFn: fetchCurrencies,
  })
}

export function useInstruments(params: FetchInstrumentsParams = {}) {
  return useQuery({
    queryKey: ['instruments', params],
    queryFn: () => fetchInstruments(params),
  })
}

export function usePrices(instrumentId: number, params: FetchPricesParams = {}) {
  return useQuery({
    queryKey: ['prices', instrumentId, params],
    queryFn: () => fetchPrices(instrumentId, params),
    enabled: instrumentId > 0,
  })
}

export function useInsight(instrumentId: number, benchmark?: string) {
  return useQuery({
    queryKey: ['insight', instrumentId, benchmark ?? null],
    queryFn: () => fetchInsight(instrumentId, benchmark),
    enabled: instrumentId > 0,
  })
}

export function useCorrelationMatrix(instrumentIds: number[]) {
  const sortedKey = [...instrumentIds].sort((a, b) => a - b).join(',')
  return useQuery({
    queryKey: ['correlation-matrix', sortedKey],
    queryFn: () => fetchCorrelationMatrix(instrumentIds),
    enabled: instrumentIds.length >= 2,
  })
}
