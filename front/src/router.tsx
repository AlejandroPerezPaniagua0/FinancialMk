import { Navigate, createBrowserRouter } from 'react-router-dom'
import { useAuth } from '@/contexts/AuthContext'
import AppLayout from '@/layouts/AppLayout'
import AuthLayout from '@/layouts/AuthLayout'
import DashboardPage from '@/pages/DashboardPage'
import InstrumentDetailPage from '@/pages/InstrumentDetailPage'
import InstrumentsPage from '@/pages/InstrumentsPage'
import LoginPage from '@/pages/LoginPage'
import RegisterPage from '@/pages/RegisterPage'

/** Redirects to /login when the user is not authenticated. */
function RequireAuth({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuth()
  return isAuthenticated ? <>{children}</> : <Navigate to="/login" replace />
}

/** Redirects to /dashboard when the user is already authenticated. */
function GuestOnly({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuth()
  return isAuthenticated ? <Navigate to="/dashboard" replace /> : <>{children}</>
}

export const router = createBrowserRouter([
  // ── Public (auth) routes ───────────────────────────────────────────────────
  {
    element: <AuthLayout />,
    children: [
      {
        path: '/login',
        element: (
          <GuestOnly>
            <LoginPage />
          </GuestOnly>
        ),
      },
      {
        path: '/register',
        element: (
          <GuestOnly>
            <RegisterPage />
          </GuestOnly>
        ),
      },
    ],
  },

  // ── Protected routes ───────────────────────────────────────────────────────
  {
    element: (
      <RequireAuth>
        <AppLayout />
      </RequireAuth>
    ),
    children: [
      { path: '/dashboard', element: <DashboardPage /> },
      { path: '/instruments', element: <InstrumentsPage /> },
      { path: '/instruments/:id', element: <InstrumentDetailPage /> },
    ],
  },

  // ── Root redirect ──────────────────────────────────────────────────────────
  { path: '/', element: <Navigate to="/dashboard" replace /> },
  { path: '*', element: <Navigate to="/dashboard" replace /> },
])
