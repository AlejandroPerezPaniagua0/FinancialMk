import { createContext, useCallback, useContext, useEffect, useState } from 'react'
import {
  type AuthUser,
  fetchCurrentUser,
  logout as apiLogout,
} from '@/api/auth'
import { TOKEN_STORAGE_KEY } from '@/api/client'

interface AuthContextValue {
  user: AuthUser | null
  token: string | null
  isAuthenticated: boolean
  isDemo: boolean
  setAuth: (user: AuthUser, token: string) => void
  clearAuth: () => void
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null)
  const [token, setToken] = useState<string | null>(
    () => localStorage.getItem(TOKEN_STORAGE_KEY),
  )

  // Hydrate the user from the API when a token is already stored on mount.
  // This populates is_demo so the demo banner can render after a refresh.
  useEffect(() => {
    const stored = localStorage.getItem(TOKEN_STORAGE_KEY)
    if (!stored) {
      setUser(null)
      setToken(null)
      return
    }

    let cancelled = false
    fetchCurrentUser()
      .then((profile) => {
        if (!cancelled) setUser(profile)
      })
      .catch(() => {
        if (!cancelled) {
          localStorage.removeItem(TOKEN_STORAGE_KEY)
          setUser(null)
          setToken(null)
        }
      })

    return () => {
      cancelled = true
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
      value={{
        user,
        token,
        isAuthenticated: token !== null,
        isDemo: user?.is_demo === true,
        setAuth,
        clearAuth,
      }}
    >
      {children}
    </AuthContext.Provider>
  )
}

// eslint-disable-next-line react-refresh/only-export-components
export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within <AuthProvider>')
  return ctx
}
