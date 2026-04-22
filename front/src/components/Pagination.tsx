interface PaginationProps {
  currentPage: number
  lastPage: number
  total: number
  onPageChange: (page: number) => void
}

export default function Pagination({
  currentPage,
  lastPage,
  total,
  onPageChange,
}: PaginationProps) {
  if (lastPage <= 1) return null

  return (
    <div className="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
      <span>{total} instruments</span>

      <div className="flex items-center gap-1">
        <button
          onClick={() => onPageChange(currentPage - 1)}
          disabled={currentPage === 1}
          className="rounded px-3 py-1.5 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors dark:hover:bg-gray-800"
        >
          ← Prev
        </button>

        <span className="px-2 font-medium text-gray-700 dark:text-gray-200">
          {currentPage} / {lastPage}
        </span>

        <button
          onClick={() => onPageChange(currentPage + 1)}
          disabled={currentPage === lastPage}
          className="rounded px-3 py-1.5 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors dark:hover:bg-gray-800"
        >
          Next →
        </button>
      </div>
    </div>
  )
}
