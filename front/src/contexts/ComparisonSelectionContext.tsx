import { createContext, useCallback, useContext, useMemo, useState } from 'react'
import { MAX_COMPARISON_ASSETS } from '@/types/market'

interface ComparisonSelectionContextValue {
  selectedIds: number[]
  isSelected: (id: number) => boolean
  toggle: (id: number) => void
  remove: (id: number) => void
  clear: () => void
  canAddMore: boolean
  maxReached: boolean
}

const ComparisonSelectionContext = createContext<ComparisonSelectionContextValue | null>(null)

/**
 * Holds the list of instrument ids the user has picked for cross-market
 * comparison. Lives at the app layout level so selections survive
 * navigation between the instruments list and the comparison view.
 */
export function ComparisonSelectionProvider({ children }: { children: React.ReactNode }) {
  const [selectedIds, setSelectedIds] = useState<number[]>([])

  const toggle = useCallback((id: number) => {
    setSelectedIds((current) => {
      if (current.includes(id)) {
        return current.filter((existing) => existing !== id)
      }
      if (current.length >= MAX_COMPARISON_ASSETS) {
        return current
      }
      return [...current, id]
    })
  }, [])

  const remove = useCallback((id: number) => {
    setSelectedIds((current) => current.filter((existing) => existing !== id))
  }, [])

  const clear = useCallback(() => setSelectedIds([]), [])

  const value = useMemo<ComparisonSelectionContextValue>(
    () => ({
      selectedIds,
      isSelected: (id: number) => selectedIds.includes(id),
      toggle,
      remove,
      clear,
      canAddMore: selectedIds.length < MAX_COMPARISON_ASSETS,
      maxReached: selectedIds.length >= MAX_COMPARISON_ASSETS,
    }),
    [selectedIds, toggle, remove, clear],
  )

  return (
    <ComparisonSelectionContext.Provider value={value}>
      {children}
    </ComparisonSelectionContext.Provider>
  )
}

export function useComparisonSelection(): ComparisonSelectionContextValue {
  const ctx = useContext(ComparisonSelectionContext)
  if (ctx === null) {
    throw new Error('useComparisonSelection must be used within ComparisonSelectionProvider')
  }
  return ctx
}
