interface FormFieldProps {
  id: string
  label: string
  type?: string
  value: string
  onChange: (value: string) => void
  error?: string
  autoComplete?: string
  placeholder?: string
}

export default function FormField({
  id,
  label,
  type = 'text',
  value,
  onChange,
  error,
  autoComplete,
  placeholder,
}: FormFieldProps) {
  return (
    <div>
      <label htmlFor={id} className="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
        {label}
      </label>
      <input
        id={id}
        type={type}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        autoComplete={autoComplete}
        placeholder={placeholder}
        className={`w-full rounded-lg border px-3 py-2 text-sm outline-none transition-colors
          focus:ring-2 focus:ring-blue-500 focus:border-transparent
          dark:text-white dark:placeholder:text-gray-500
          ${error
            ? 'border-red-400 bg-red-50 dark:border-red-800 dark:bg-red-950/40'
            : 'border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900'
          }`}
      />
      {error && <p className="mt-1 text-xs text-red-600 dark:text-red-400">{error}</p>}
    </div>
  )
}
