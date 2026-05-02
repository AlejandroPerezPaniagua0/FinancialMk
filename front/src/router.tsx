import { Navigate, createBrowserRouter } from 'react-router-dom'
import { GuestOnly, RequireAuth } from '@/components/RouteGuards'
import AppLayout from '@/layouts/AppLayout'
import AuthLayout from '@/layouts/AuthLayout'
import ComparisonPage from '@/pages/ComparisonPage'
import DashboardPage from '@/pages/DashboardPage'
import InstrumentDetailPage from '@/pages/InstrumentDetailPage'
import InstrumentsPage from '@/pages/InstrumentsPage'
import LoginPage from '@/pages/LoginPage'
import RegisterPage from '@/pages/RegisterPage'

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
      { path: '/comparison', element: <ComparisonPage /> },
    ],
  },

  // ── Root redirect ──────────────────────────────────────────────────────────
  { path: '/', element: <Navigate to="/dashboard" replace /> },
  { path: '*', element: <Navigate to="/dashboard" replace /> },
])
