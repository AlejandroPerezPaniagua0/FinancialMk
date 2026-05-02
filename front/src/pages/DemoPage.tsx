import { useEffect, useRef, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { demoLogin } from '@/api/demo'
import { useAuth } from '@/contexts/AuthContext'

/**
 * Public "Try the demo" landing target.
 *
 * On mount: requests POST /api/demo/login. On success the auth context is
 * populated and the user lands in the dashboard. On 404 (demo disabled) or
 * 503 (seeder not yet run) we surface a clear error instead of failing
 * silently.
 *
 * StrictMode safety: a `useRef` guard prevents the double-mount in dev from
 * minting two tokens.
 */
export default function DemoPage() {
  const { setAuth, isAuthenticated } = useAuth()
  const navigate = useNavigate()
  const triggered = useRef(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (triggered.current) return
    triggered.current = true

    if (isAuthenticated) {
      navigate('/dashboard', { replace: true })
      return
    }

    demoLogin()
      .then((response) => {
        setAuth(response.user, response.access_token)
        navigate('/dashboard', { replace: true })
      })
      .catch((err: { response?: { status?: number } }) => {
        const status = err?.response?.status
        if (status === 404) {
          setError('Demo mode is not enabled on this instance.')
        } else if (status === 503) {
          setError('Demo user is missing. Run `php artisan db:seed --class=DemoUserSeeder`.')
        } else {
          setError('Could not start the demo. Please try again.')
        }
      })
  }, [isAuthenticated, navigate, setAuth])

  if (error) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-gray-50 px-6 dark:bg-[#0b0f17]">
        <div className="max-w-md text-center">
          <h1 className="text-xl font-bold text-gray-900 dark:text-white">Demo unavailable</h1>
          <p className="mt-2 text-sm text-gray-600 dark:text-gray-300">{error}</p>
          <div className="mt-4 flex items-center justify-center gap-3">
            <Link to="/login" className="text-sm text-blue-600 hover:underline">Sign in instead</Link>
            <span className="text-gray-400">·</span>
            <Link to="/register" className="text-sm text-blue-600 hover:underline">Create an account</Link>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-50 dark:bg-[#0b0f17]">
      <div className="text-center">
        <div className="inline-block h-8 w-8 animate-spin rounded-full border-2 border-current border-t-transparent text-blue-600" aria-hidden="true" />
        <p className="mt-3 text-sm text-gray-600 dark:text-gray-300">Loading demo…</p>
      </div>
    </div>
  )
}
