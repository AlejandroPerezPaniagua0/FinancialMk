import { Link, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '@/contexts/AuthContext'

export default function AppLayout() {
  const { user, clearAuth } = useAuth()
  const navigate = useNavigate()

  async function handleLogout() {
    await clearAuth()
    navigate('/login')
  }

  return (
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
    </div>
  )
}
