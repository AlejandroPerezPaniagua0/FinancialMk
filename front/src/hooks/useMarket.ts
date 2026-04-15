import { useQuery } from '@tanstack/react-query'
import {
  fetchAssetClasses,
  fetchCurrencies,
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
