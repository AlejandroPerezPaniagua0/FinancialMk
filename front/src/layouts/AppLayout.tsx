import { Link, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '@/contexts/AuthContext'
import {
  ComparisonSelectionProvider,
  useComparisonSelection,
} from '@/contexts/ComparisonSelectionContext'
import { MAX_COMPARISON_ASSETS, MIN_COMPARISON_ASSETS } from '@/types/market'

function ComparisonActionBar() {
  const { selectedIds, clear } = useComparisonSelection()
  const navigate = useNavigate()

  if (selectedIds.length === 0) {
    return null
  }

  const canCompare = selectedIds.length >= MIN_COMPARISON_ASSETS

  return (
    <div className="fixed bottom-4 left-1/2 z-30 -translate-x-1/2 flex items-center gap-3 rounded-full border border-gray-200 bg-white px-4 py-2 shadow-lg">
      <span className="text-sm text-gray-600">
        {selectedIds.length} / {MAX_COMPARISON_ASSETS} selected
      </span>
      <button
        onClick={() => navigate('/comparison')}
        disabled={!canCompare}
        className="rounded-full bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:bg-gray-200 disabled:text-gray-500 disabled:cursor-not-allowed"
      >
        {canCompare ? 'Compare' : `Pick ${MIN_COMPARISON_ASSETS - selectedIds.length} more`}
      </button>
      <button
        onClick={clear}
        className="text-sm text-gray-500 hover:text-gray-900"
      >
        Clear
      </button>
    </div>
  )
}

export default function AppLayout() {
  const { user, clearAuth } = useAuth()
  const navigate = useNavigate()

  async function handleLogout() {
    await clearAuth()
    navigate('/login')
  }

  return (
    <ComparisonSelectionProvider>
      <div className="min-h-screen bg-gray-50 flex flex-col">
        {/* Top navigation */}
        <header className="bg-white border-b border-gray-200 h-14 flex items-center px-6 shrink-0">
          <Link to="/dashboard" className="text-lg font-bold text-gray-900 mr-8">
            FinancialMk
          </Link>

          <nav className="flex items-center gap-6 flex-1">
            <Link
              to="/dashboard"
              className="text-sm text-gray-600 hover:text-gray-900 transition-colors"
            >
              Dashboard
            </Link>
            <Link
              to="/instruments"
              className="text-sm text-gray-600 hover:text-gray-900 transition-colors"
            >
              Instruments
            </Link>
            <Link
              to="/comparison"
              className="text-sm text-gray-600 hover:text-gray-900 transition-colors"
            >
              Comparison
            </Link>
          </nav>

          <div className="flex items-center gap-4">
            <span className="text-sm text-gray-500">{user?.name}</span>
            <button
              onClick={handleLogout}
              className="text-sm text-gray-500 hover:text-gray-900 transition-colors"
            >
              Logout
            </button>
          </div>
        </header>

        {/* Page content */}
        <main className="flex-1 p-6">
          <Outlet />
        </main>

        <ComparisonActionBar />
      </div>
    </ComparisonSelectionProvider>
  )
}
