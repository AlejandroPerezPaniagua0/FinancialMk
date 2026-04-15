import { apiClient } from './client'
import type {
  AssetClass,
  CollectionResponse,
  Currency,
  HistoricalPrice,
  Instrument,
  PaginatedResponse,
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
