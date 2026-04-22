import { Outlet } from 'react-router-dom'

export default function AuthLayout() {
  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center px-4 dark:bg-[#0b0f17]">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white">FinancialMk</h1>
          <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">Cross-market movement analysis</p>
        </div>
        <div className="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 dark:bg-gray-900 dark:border-gray-800">
          <Outlet />
        </div>
      </div>
    </div>
  )
}
