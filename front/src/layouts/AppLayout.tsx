import { Link, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '@/contexts/AuthContext'
import {
  ComparisonSelectionProvider,
  useComparisonSelection,
} from '@/contexts/ComparisonSelectionContext'
import { useTheme } from '@/contexts/ThemeContext'
import { MAX_COMPARISON_ASSETS, MIN_COMPARISON_ASSETS } from '@/types/market'

function ComparisonActionBar() {
  const { selectedIds, clear } = useComparisonSelection()
  const navigate = useNavigate()

  if (selectedIds.length === 0) {
    return null
  }

  const canCompare = selectedIds.length >= MIN_COMPARISON_ASSETS

  return (
    <div className="fixed bottom-4 left-1/2 z-30 -translate-x-1/2 flex items-center gap-3 rounded-full border border-gray-200 bg-white px-4 py-2 shadow-lg dark:border-gray-700 dark:bg-gray-800">
      <span className="text-sm text-gray-600 dark:text-gray-300">
        {selectedIds.length} / {MAX_COMPARISON_ASSETS} selected
      </span>
      <button
        onClick={() => navigate('/comparison')}
        disabled={!canCompare}
        className="rounded-full bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:bg-gray-200 disabled:text-gray-500 disabled:cursor-not-allowed dark:disabled:bg-gray-700 dark:disabled:text-gray-400"
      >
        {canCompare ? 'Compare' : `Pick ${MIN_COMPARISON_ASSETS - selectedIds.length} more`}
      </button>
      <button
        onClick={clear}
        className="text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
      >
        Clear
      </button>
    </div>
  )
}

function ThemeToggleButton() {
  const { theme, toggleTheme } = useTheme()
  const isDark = theme === 'dark'

  return (
    <button
      type="button"
      onClick={toggleTheme}
      aria-label={isDark ? 'Switch to light mode' : 'Switch to dark mode'}
      title={isDark ? 'Switch to light mode' : 'Switch to dark mode'}
      className="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
    >
      {isDark ? (
        // Sun icon (click to go light)
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-4 w-4">
          <circle cx="12" cy="12" r="4" />
          <path d="M12 2v2" />
          <path d="M12 20v2" />
          <path d="m4.93 4.93 1.41 1.41" />
          <path d="m17.66 17.66 1.41 1.41" />
          <path d="M2 12h2" />
          <path d="M20 12h2" />
          <path d="m6.34 17.66-1.41 1.41" />
          <path d="m19.07 4.93-1.41 1.41" />
        </svg>
      ) : (
        // Moon icon (click to go dark)
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-4 w-4">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
        </svg>
      )}
    </button>
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
      <div className="min-h-screen bg-gray-50 flex flex-col dark:bg-[#0b0f17]">
        {/* Top navigation */}
        <header className="bg-white border-b border-gray-200 h-14 flex items-center px-6 shrink-0 dark:bg-gray-900 dark:border-gray-800">
          <Link to="/dashboard" className="text-lg font-bold text-gray-900 mr-8 dark:text-white">
            FinancialMk
          </Link>

          <nav className="flex items-center gap-6 flex-1">
            <Link
              to="/dashboard"
              className="text-sm text-gray-600 hover:text-gray-900 transition-colors dark:text-gray-300 dark:hover:text-white"
            >
              Dashboard
            </Link>
            <Link
              to="/instruments"
              className="text-sm text-gray-600 hover:text-gray-900 transition-colors dark:text-gray-300 dark:hover:text-white"
            >
              Instruments
            </Link>
            <Link
              to="/comparison"
              className="text-sm text-gray-600 hover:text-gray-900 transition-colors dark:text-gray-300 dark:hover:text-white"
            >
              Comparison
            </Link>
          </nav>

          <div className="flex items-center gap-4">
            <span className="text-sm text-gray-500 dark:text-gray-400">{user?.name}</span>
            <ThemeToggleButton />
            <button
              onClick={handleLogout}
              className="text-sm text-gray-500 hover:text-gray-900 transition-colors dark:text-gray-300 dark:hover:text-white"
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
