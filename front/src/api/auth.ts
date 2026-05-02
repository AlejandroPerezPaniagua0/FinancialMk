import { apiClient, TOKEN_STORAGE_KEY } from './client'

export interface AuthUser {
  id: number
  name: string
  email: string
  is_demo?: boolean
}

export interface AuthResponse {
  message: string
  access_token: string
  token_type: string
  user: AuthUser
}

export interface LoginPayload {
  email: string
  password: string
}

export interface RegisterPayload {
  name: string
  email: string
  password: string
}

export async function login(payload: LoginPayload): Promise<AuthResponse> {
  const { data } = await apiClient.post<AuthResponse>('/auth/login', payload)
  localStorage.setItem(TOKEN_STORAGE_KEY, data.access_token)
  return data
}

export async function register(payload: RegisterPayload): Promise<AuthResponse> {
  const { data } = await apiClient.post<AuthResponse>('/auth/register', payload)
  localStorage.setItem(TOKEN_STORAGE_KEY, data.access_token)
  return data
}

export async function logout(): Promise<void> {
  await apiClient.post('/auth/logout')
  localStorage.removeItem(TOKEN_STORAGE_KEY)
}

export async function fetchCurrentUser(): Promise<AuthUser> {
  const { data } = await apiClient.get<AuthUser>('/user')
  return data
}
