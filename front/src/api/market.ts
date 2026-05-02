import { apiClient } from './client'
import type {
  AssetClass,
  CollectionResponse,
  CorrelationMatrix,
  Currency,
  HistoricalPrice,
  Insight,
  Instrument,
  PaginatedResponse,
  QuotesResponse,
} from '@/types/market'

export async function fetchAssetClasses(): Promise<AssetClass[]> {
  const { data } = await apiClient.get<CollectionResponse<AssetClass>>('/asset-classes')
  return data.data
}

export async function fetchCurrencies(): Promise<Currency[]> {
  const { data } = await apiClient.get<CollectionResponse<Currency>>('/currencies')
  return data.data
}

export interface FetchInstrumentsParams {
  asset_class_id?: number
  page?: number
  per_page?: number
}

export async function fetchInstruments(
  params: FetchInstrumentsParams = {},
): Promise<PaginatedResponse<Instrument>> {
  const { data } = await apiClient.get<PaginatedResponse<Instrument>>('/instruments', {
    params,
  })
  return data
}

export interface FetchPricesParams {
  from?: string
  to?: string
}

export async function fetchPrices(
  instrumentId: number,
  params: FetchPricesParams = {},
): Promise<HistoricalPrice[]> {
  const { data } = await apiClient.get<CollectionResponse<HistoricalPrice>>(
    `/instruments/${instrumentId}/prices`,
    { params },
  )
  return data.data
}

export async function fetchQuotes(
  instrumentIds: number[],
  signal?: AbortSignal,
): Promise<QuotesResponse> {
  const { data } = await apiClient.get<QuotesResponse>('/instruments/quotes', {
    params: { ids: instrumentIds },
    // indexes:false -> ids[]=1&ids[]=2, which PHP parses as an array.
    paramsSerializer: { indexes: false },
    signal,
  })
  return data
}

export async function fetchInsight(
  instrumentId: number,
  benchmark?: string,
): Promise<Insight> {
  const { data } = await apiClient.get<{ data: Insight }>(
    `/instruments/${instrumentId}/insights`,
    { params: benchmark ? { benchmark } : {} },
  )
  return data.data
}

export async function fetchCorrelationMatrix(
  instrumentIds: number[],
): Promise<CorrelationMatrix> {
  const { data } = await apiClient.get<{ data: CorrelationMatrix }>(
    '/instruments/correlation-matrix',
    {
      params: { ids: instrumentIds },
      paramsSerializer: { indexes: false },
    },
  )
  return data.data
}
