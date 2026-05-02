import { useState } from 'react'
import { TOKEN_STORAGE_KEY } from '@/api/client'

/**
 * Universal "Export" button — emits the streamed download from the backend
 * with the auth token attached. We can't use a plain `<a href>` because the
 * API requires the Bearer token; we fetch the response, turn it into a Blob,
 * and trigger a synthetic click on an anchor pointing at the object URL.
 *
 * Why both CSV and JSON? Different homes:
 *   • CSV  — Excel, Google Sheets, anything tabular.
 *   • JSON — your own scripts and pipelines.
 *
 * The button is intentionally text-and-icon: discoverable, never the primary
 * CTA on a page. The whole point is "by the way, you can take this with you."
 */
export default function ExportButton({
  endpoint,
  filenameBase,
  className = '',
}: {
  /** Path under /api, without the leading slash. e.g. "export/watchlists/12" */
  endpoint: string
  /** Used to suggest a filename if the backend's Content-Disposition is missing. */
  filenameBase: string
  className?: string
}) {
  const [busy, setBusy] = useState<'csv' | 'json' | null>(null)

  async function download(format: 'csv' | 'json') {
    setBusy(format)
    try {
      const token = localStorage.getItem(TOKEN_STORAGE_KEY)
      const url   = `/api/${endpoint}${endpoint.includes('?') ? '&' : '?'}format=${format}`
      const res   = await fetch(url, {
        headers: token ? { Authorization: `Bearer ${token}` } : undefined,
      })
      if (!res.ok) throw new Error(`Export failed (${res.status})`)

      const filename = parseFilename(res.headers.get('content-disposition'))
        ?? `${filenameBase}.${format}`

      const blob = await res.blob()
      triggerDownload(blob, filename)
    } catch (e) {
      console.error('[ExportButton] download error', e)
    } finally {
      setBusy(null)
    }
  }

  return (
    <div className={`inline-flex items-center gap-2 ${className}`}>
      <span className="text-xs text-gray-400 dark:text-gray-500" title="Your data is yours.">
        Export
      </span>
      <button
        type="button"
        onClick={() => download('csv')}
        disabled={busy !== null}
        className="rounded border border-gray-300 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
      >
        {busy === 'csv' ? '…' : 'CSV'}
      </button>
      <button
        type="button"
        onClick={() => download('json')}
        disabled={busy !== null}
        className="rounded border border-gray-300 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
      >
        {busy === 'json' ? '…' : 'JSON'}
      </button>
    </div>
  )
}

function parseFilename(header: string | null): string | null {
  if (!header) return null
  const utf8 = /filename\*=UTF-8''([^;]+)/i.exec(header)
  if (utf8?.[1]) return decodeURIComponent(utf8[1])
  const ascii = /filename="?([^";]+)"?/i.exec(header)
  return ascii?.[1] ?? null
}

function triggerDownload(blob: Blob, filename: string): void {
  const objectUrl = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = objectUrl
  a.download = filename
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  // Give the browser a tick to start the download before revoking.
  setTimeout(() => URL.revokeObjectURL(objectUrl), 1000)
}
