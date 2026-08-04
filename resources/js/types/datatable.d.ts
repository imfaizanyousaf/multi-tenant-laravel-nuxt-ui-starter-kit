import type { TableColumn } from '@nuxt/ui'
import type { Row } from '@tanstack/table-core'

// Base types for the datatable
export interface DataTableColumn<T = any> extends Omit<TableColumn<T>, 'cell' | 'header'> {
  accessorKey?: keyof T
  header?: string | ((props: any) => any)
  cell?: (props: { row: Row<T>; getValue: () => any }) => any
  sortable?: boolean
  pinnable?: boolean
  pinnedPosition?: 'left' | 'right'
  filterable?: boolean
  filterType?: 'text' | 'select' | 'date' | 'number' | 'custom'
  filterOptions?: Array<{ label: string; value: any }>
  width?: string | number
  minWidth?: string | number
  maxWidth?: string | number
  slot?: string
}

// Action types
export interface DataTableAction<T = any> {
  id: string
  label: string
  icon?: string
  color?: 'primary' | 'secondary' | 'success' | 'warning' | 'error' | 'neutral'
  variant?: 'solid' | 'outline' | 'soft' | 'ghost' | 'link'
  disabled?: boolean | ((row: Row<T>) => boolean)
  visible?: boolean | ((row: Row<T>) => boolean)
  onClick: (row: Row<T>) => void | Promise<void>
}

export interface DataTableBulkAction<T = any> {
  id: string
  label: string
  icon?: string
  color?: 'primary' | 'secondary' | 'success' | 'warning' | 'error' | 'neutral'
  variant?: 'solid' | 'outline' | 'soft' | 'ghost' | 'link'
  disabled?: boolean | ((rows: Row<T>[]) => boolean)
  visible?: boolean | ((rows: Row<T>[]) => boolean)
  onClick: (rows: Row<T>[]) => void | Promise<void>
}

export interface DataTableToolbarAction {
  id: string
  label: string
  icon?: string
  color?: 'primary' | 'secondary' | 'success' | 'warning' | 'error' | 'neutral'
  variant?: 'solid' | 'outline' | 'soft' | 'ghost' | 'link'
  disabled?: boolean
  visible?: boolean
  onClick: () => void | Promise<void>
}

// Filter types
export interface DataTableFilter {
  id: string
  type: 'text' | 'select' | 'date' | 'number' | 'custom'
  label: string
  placeholder?: string
  options?: Array<{ label: string; value: any }>
  component?: any
}

// Server-side data types
export interface DataTableServerResponse<T = any> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
    path?: string
  }
  links?: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
}

export interface DataTableServerParams {
  page?: number
  per_page?: number
  sort?: string
  search?: string
  filters?: Record<string, any>
}

// Configuration types
export interface DataTableConfig {
  pagination?: {
    enabled: boolean
    pageSize: number
    pageSizeOptions: number[]
    showPageSizeSelector: boolean
  }

  selection?: {
    enabled: boolean
    multiple: boolean
  }

  features?: {
    search:
      | boolean
      | {
          enabled: boolean
          debounce?: number
        }
    columnFilters: boolean
    columnVisibility: boolean
    columnResizing: boolean
    sorting: boolean
    export: boolean
  }

  ui?: {
    striped: boolean
    hover: boolean
    bordered: boolean
    compact: boolean
  }

  loading?: {
    skeleton: boolean
    spinner: boolean
  }
}

// Props for the main component
export interface DataTableProps<T = any> {
  columns: DataTableColumn<T>[]
  data?: T[]
  serverSide?: boolean
  endpoint?: string
  params?: DataTableServerParams
  actions?: DataTableAction<T>[]
  bulkActions?: DataTableBulkAction<T>[]
  toolbarActions?: DataTableToolbarAction[]
  filters?: DataTableFilter[]
  searchPlaceholder?: string
  config?: DataTableConfig
  onRowClick?: (row: Row<T>) => void
  onSelectionChange?: (rows: Row<T>[]) => void
  loading?: boolean
  class?: string
}
