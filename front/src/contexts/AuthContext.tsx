import { createContext, useCallback, useContext, useEffect, useState } from 'react'
import { type AuthUser, logout as apiLogout } from '@/api/auth'
import { TOKEN_STORAGE_KEY } from '@/api/client'

interface AuthContextValue {
  user: AuthUser | null
  token: string | null
  isAuthenticated: boolean
  setAuth: (user: AuthUser, token: string) => void
  clearAuth: () => void
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null)
  const [token, setToken] = useState<string | null>(
    () => localStorage.getItem(TOKEN_STORAGE_KEY),
  )

  // On mount, if there is already a token stored, mark as authenticated.
  // A full user-profile fetch (GET /api/user) can be added here later.
  useEffect(() => {
    const stored = localStorage.getItem(TOKEN_STORAGE_KEY)
    if (!stored) {
      setUser(null)
      setToken(null)
    }
  }, [])

  const setAuth = useCallback((authUser: AuthUser, authToken: string) => {
    localStorage.setItem(TOKEN_STORAGE_KEY, authToken)
    setUser(authUser)
    setToken(authToken)
  }, [])

  const clearAuth = useCallback(async () => {
    try {
      await apiLogout()
    } catch {
      // Token already invalid — still clear local state
    } finally {
      localStorage.removeItem(TOKEN_STORAGE_KEY)
      setUser(null)
      setToken(null)
    }
  }, [])

  return (
    <AuthContext.Provider
      value={{ user, token, isAuthenticated: token !== null, setAuth, clearAuth }}
    >
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within <AuthProvider>')
  return ctx
}
