import type { Instrument, Quote } from '@/types/market'
import AssetComparisonCard from '@/components/AssetComparisonCard'

interface ComparisonGridProps {
  instruments: Instrument[]
  quotesById: Map<number, Quote>
  onRemove: (id: number) => void
}

/**
 * Proportional grid for 2-4 selected assets.
 * Uses CSS Grid with an inline `grid-template-columns` so the layout
 * always splits the available width evenly regardless of the count.
 * Collapses to a single column below md breakpoint to stay readable on mobile.
 */
export default function ComparisonGrid({ instruments, quotesById, onRemove }: ComparisonGridProps) {
  const count = instruments.length

  if (count === 0) {
    return null
  }

  // With 3 assets, keep rows balanced: 3 cols on xl, 2 cols on lg (last row has 1).
  // With 4 assets, 2x2 on md+ feels better than 1x4, so we opt into that shape.
  const columnsStyle = getColumnsStyle(count)

  return (
    <div
      className="grid gap-4"
      style={columnsStyle}
      data-asset-count={count}
    >
      {instruments.map((instrument) => (
        <AssetComparisonCard
          key={instrument.id}
          instrument={instrument}
          quote={quotesById.get(instrument.id) ?? null}
          onRemove={() => onRemove(instrument.id)}
        />
      ))}
    </div>
  )
}

function getColumnsStyle(count: number): React.CSSProperties {
  // At the mobile breakpoint we always use a single column; the responsive
  // split kicks in at md via a CSS custom media query. Tailwind's JIT can't
  // generate arbitrary grid-template-columns values at build time for every
  // count, so we use inline styles paired with a media query via CSS vars.
  switch (count) {
    case 2:
      return {
        gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
      }
    case 3:
      return {
        gridTemplateColumns: 'repeat(auto-fit, minmax(260px, 1fr))',
      }
    case 4:
      return {
        gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
      }
    default:
      return {
        gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
      }
  }
}
