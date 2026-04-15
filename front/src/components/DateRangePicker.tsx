interface Preset {
  label: string
  days: number
}

const PRESETS: Preset[] = [
  { label: '1M', days: 30 },
  { label: '3M', days: 90 },
  { label: '6M', days: 180 },
  { label: '1Y', days: 365 },
  { label: 'MAX', days: 0 },
]

interface DateRangePickerProps {
  from: string | undefined
  to: string | undefined
  onChange: (from: string | undefined, to: string | undefined) => void
}

function toISODate(date: Date): string {
  return date.toISOString().split('T')[0]!
}

export default function DateRangePicker({ from, to, onChange }: DateRangePickerProps) {
  function applyPreset(days: number) {
    if (days === 0) {
      onChange(undefined, undefined)
      return
    }
    const endDate = new Date()
    const startDate = new Date()
    startDate.setDate(startDate.getDate() - days)
    onChange(toISODate(startDate), toISODate(endDate))
  }

  function isActivePreset(days: number): boolean {
    if (days === 0) return from === undefined && to === undefined
    if (!from || !to) return false
    const expectedStart = new Date()
    expectedStart.setDate(expectedStart.getDate() - days)
    return from === toISODate(expectedStart)
  }

  return (
    <div className="flex items-center gap-3 flex-wrap">
      <div className="flex gap-1">
        {PRESETS.map((preset) => (
          <button
            key={preset.label}
            onClick={() => applyPreset(preset.days)}
            className={`rounded px-3 py-1 text-xs font-medium transition-colors
              ${isActivePreset(preset.days)
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              }`}
          >
            {preset.label}
          </button>
        ))}
      </div>

      <div className="flex items-center gap-2 text-sm">
        <input
          type="date"
          value={from ?? ''}
          onChange={(e) => onChange(e.target.value || undefined, to)}
          className="rounded border border-gray-300 px-2 py-1 text-xs outline-none focus:ring-2 focus:ring-blue-500"
        />
        <span className="text-gray-400">–</span>
        <input
          type="date"
          value={to ?? ''}
          onChange={(e) => onChange(from, e.target.value || undefined)}
          className="rounded border border-gray-300 px-2 py-1 text-xs outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
    </div>
  )
}
