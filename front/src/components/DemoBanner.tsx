import { Link } from 'react-router-dom'
import { useAuth } from '@/contexts/AuthContext'

/**
 * Top-of-app banner shown only when the current session belongs to the
 * seeded demo user. Renders nothing otherwise — safe to mount anywhere.
 *
 * The CTA points at /register so that a curious demo visitor has a one-click
 * path to "make this my own". We deliberately do NOT show "create your own
 * data" verbiage in the demo, because the demo is shared and its purpose is
 * to evaluate, not to onboard.
 */
export default function DemoBanner() {
  const { isDemo } = useAuth()
  if (!isDemo) return null

  return (
    <div
      role="status"
      className="bg-amber-50 border-b border-amber-200 px-6 py-2 text-sm text-amber-900 dark:bg-amber-950/40 dark:border-amber-900/60 dark:text-amber-200"
    >
      <div className="flex flex-wrap items-center gap-x-4 gap-y-1">
        <span className="font-semibold uppercase tracking-wide text-xs">Demo</span>
        <span>
          You're exploring a shared demo. Data and changes here are not private.
        </span>
        <Link
          to="/register"
          className="ml-auto underline underline-offset-2 hover:text-amber-700 dark:hover:text-amber-100"
        >
          Create your own account →
        </Link>
      </div>
    </div>
  )
}
