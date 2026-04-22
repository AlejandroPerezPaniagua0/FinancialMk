import { useState } from 'react'
import AssetClassFilter from '@/components/AssetClassFilter'
import InstrumentsTable from '@/components/InstrumentsTable'
import Pagination from '@/components/Pagination'
import { useAssetClasses, useInstruments } from '@/hooks/useMarket'

export default function DashboardPage() {
  const [selectedAssetClassId, setSelectedAssetClassId] = useState<number | undefined>()
  const [page, setPage] = useState(1)

  const { data: assetClasses = [] } = useAssetClasses()
  const { data: instrumentsPage, isLoading } = useInstruments({
    asset_class_id: selectedAssetClassId,
    page,
    per_page: 20,
  })

  function handleAssetClassChange(id: number | undefined) {
    setSelectedAssetClassId(id)
    setPage(1)
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Market Overview</h1>
        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Browse and compare financial instruments across markets.
        </p>
      </div>

      {/* Stats cards */}
      <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
        {assetClasses.map((ac) => (
          <button
            key={ac.id}
            onClick={() => handleAssetClassChange(ac.id)}
            className={`rounded-xl border p-4 text-left transition-colors
              ${selectedAssetClassId === ac.id
                ? 'border-blue-300 bg-blue-50 dark:border-blue-700 dark:bg-blue-950/40'
                : 'border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:hover:bg-gray-800'
              }`}
          >
            <p className="text-xs font-medium text-gray-500 dark:text-gray-400">Asset class</p>
            <p className="mt-1 text-base font-semibold text-gray-900 dark:text-white">{ac.name}</p>
          </button>
        ))}
      </div>

      {/* Instruments table */}
      <div className="bg-white rounded-xl border border-gray-200 p-6 space-y-4 dark:bg-gray-900 dark:border-gray-800">
        <div className="flex items-center justify-between gap-4 flex-wrap">
          <h2 className="text-base font-semibold text-gray-900 dark:text-white">Instruments</h2>
          <AssetClassFilter
            assetClasses={assetClasses}
            selected={selectedAssetClassId}
            onChange={handleAssetClassChange}
          />
        </div>

        <InstrumentsTable
          instruments={instrumentsPage?.data ?? []}
          loading={isLoading}
        />

        {instrumentsPage && (
          <Pagination
            currentPage={instrumentsPage.meta.current_page}
            lastPage={instrumentsPage.meta.last_page}
            total={instrumentsPage.meta.total}
            onPageChange={setPage}
          />
        )}
      </div>
    </div>
  )
}
