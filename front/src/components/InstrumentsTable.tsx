import { Link } from 'react-router-dom'
import { useComparisonSelection } from '@/contexts/ComparisonSelectionContext'
import { MAX_COMPARISON_ASSETS } from '@/types/market'
import type { Instrument } from '@/types/market'

interface InstrumentsTableProps {
  instruments: Instrument[]
  loading?: boolean
}

export default function InstrumentsTable({ instruments, loading }: InstrumentsTableProps) {
  const { isSelected, toggle, maxReached } = useComparisonSelection()

  if (loading) {
    return (
      <div className="py-12 text-center text-sm text-gray-500 animate-pulse">
        Loading instruments…
      </div>
    )
  }

  if (instruments.length === 0) {
    return (
      <div className="py-12 text-center text-sm text-gray-500">No instruments found.</div>
    )
  }

  return (
    <div className="overflow-x-auto rounded-xl border border-gray-200">
      <table className="w-full text-sm">
        <thead className="bg-gray-50 border-b border-gray-200">
          <tr>
            <th className="px-3 py-3 w-10 text-left">
              <span className="sr-only">Compare</span>
            </th>
            <th className="px-4 py-3 text-left font-medium text-gray-500">Ticker</th>
            <th className="px-4 py-3 text-left font-medium text-gray-500">Name</th>
            <th className="px-4 py-3 text-left font-medium text-gray-500">Asset class</th>
            <th className="px-4 py-3 text-left font-medium text-gray-500">Currency</th>
            <th className="px-4 py-3" />
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-100 bg-white">
          {instruments.map((inst) => {
            const selected = isSelected(inst.id)
            const disabled = !selected && maxReached
            return (
              <tr
                key={inst.id}
                className={`transition-colors ${selected ? 'bg-blue-50/50' : 'hover:bg-gray-50'}`}
              >
                <td className="px-3 py-3">
                  <input
                    type="checkbox"
                    aria-label={`Select ${inst.ticker} for comparison`}
                    checked={selected}
                    disabled={disabled}
                    onChange={() => toggle(inst.id)}
                    title={
                      disabled
                        ? `You can compare up to ${MAX_COMPARISON_ASSETS} assets at a time`
                        : undefined
                    }
                    className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 disabled:opacity-40"
                  />
                </td>
                <td className="px-4 py-3 font-mono font-semibold text-gray-900">{inst.ticker}</td>
                <td className="px-4 py-3 text-gray-700">{inst.name}</td>
                <td className="px-4 py-3">
                  <span className="inline-block rounded-full bg-blue-50 text-blue-700 px-2.5 py-0.5 text-xs font-medium">
                    {inst.asset_class.name}
                  </span>
                </td>
                <td className="px-4 py-3 text-gray-500">{inst.currency.iso_code}</td>
                <td className="px-4 py-3 text-right">
                  <Link
                    to={`/instruments/${inst.id}`}
                    className="text-blue-600 hover:text-blue-800 font-medium"
                  >
                    View →
                  </Link>
                </td>
              </tr>
            )
          })}
        </tbody>
      </table>
    </div>
  )
}
