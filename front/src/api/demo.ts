import type { AuthResponse } from './auth'
import { apiClient, TOKEN_STORAGE_KEY } from './client'

interface DemoStatus {
  enabled: boolean
}

/**
 * Cheap probe so the SPA can show or hide the "Try the demo" CTA without
 * attempting a login that might 404.
 */
export async function demoStatus(): Promise<DemoStatus> {
  const { data } = await apiClient.get<DemoStatus>('/demo/status')
  return data
}

/**
 * Mints a Sanctum token for the seeded demo user and persists it the same
 * way the regular login flow does — so `RequireAuth` and the axios
 * interceptor work identically.
 */
export async function demoLogin(): Promise<AuthResponse> {
  const { data } = await apiClient.post<AuthResponse>('/demo/login')
  localStorage.setItem(TOKEN_STORAGE_KEY, data.access_token)
  return data
}
