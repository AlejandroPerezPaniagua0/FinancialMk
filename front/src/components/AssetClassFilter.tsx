import type { AssetClass } from '@/types/market'

interface AssetClassFilterProps {
  assetClasses: AssetClass[]
  selected: number | undefined
  onChange: (id: number | undefined) => void
}

export default function AssetClassFilter({
  assetClasses,
  selected,
  onChange,
}: AssetClassFilterProps) {
  return (
    <div className="flex flex-wrap gap-2">
      <button
        onClick={() => onChange(undefined)}
        className={`rounded-full px-4 py-1.5 text-sm font-medium transition-colors
          ${selected === undefined
            ? 'bg-blue-600 text-white'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          }`}
      >
        All
      </button>
      {assetClasses.map((ac) => (
        <button
          key={ac.id}
          onClick={() => onChange(ac.id)}
          className={`rounded-full px-4 py-1.5 text-sm font-medium transition-colors
            ${selected === ac.id
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
        >
          {ac.name}
        </button>
      ))}
    </div>
  )
}
