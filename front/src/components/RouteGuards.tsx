import { Navigate } from 'react-router-dom'
import { useAuth } from '@/contexts/AuthContext'

/** Redirects to /login when the user is not authenticated. */
export function RequireAuth({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuth()
  return isAuthenticated ? <>{children}</> : <Navigate to="/login" replace />
}

/** Redirects to /dashboard when the user is already authenticated. */
export function GuestOnly({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuth()
  return isAuthenticated ? <Navigate to="/dashboard" replace /> : <>{children}</>
}
