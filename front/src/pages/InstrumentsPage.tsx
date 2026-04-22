import { useState } from 'react'
import AssetClassFilter from '@/components/AssetClassFilter'
import InstrumentsTable from '@/components/InstrumentsTable'
import Pagination from '@/components/Pagination'
import { useAssetClasses, useInstruments } from '@/hooks/useMarket'

export default function InstrumentsPage() {
  const [selectedAssetClassId, setSelectedAssetClassId] = useState<number | undefined>()
  const [page, setPage] = useState(1)

  const { data: assetClasses = [] } = useAssetClasses()
  const { data: instrumentsPage, isLoading } = useInstruments({
    asset_class_id: selectedAssetClassId,
    page,
    per_page: 50,
  })

  function handleAssetClassChange(id: number | undefined) {
    setSelectedAssetClassId(id)
    setPage(1)
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Instruments</h1>
        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {instrumentsPage ? `${instrumentsPage.meta.total} instruments available` : 'Loading…'}
        </p>
      </div>

      <div className="bg-white rounded-xl border border-gray-200 p-6 space-y-4 dark:bg-gray-900 dark:border-gray-800">
        <AssetClassFilter
          assetClasses={assetClasses}
          selected={selectedAssetClassId}
          onChange={handleAssetClassChange}
        />

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
