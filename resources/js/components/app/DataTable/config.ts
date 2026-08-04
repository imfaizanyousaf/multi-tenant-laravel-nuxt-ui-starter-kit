import type { DataTableConfig } from '@/types/datatable'

export const defaultDataTableConfig: DataTableConfig = {
  pagination: {
    enabled: true,
    pageSize: 10,
    pageSizeOptions: [5, 10, 25, 50, 100],
    showPageSizeSelector: true,
  },
  selection: {
    enabled: true,
    multiple: true,
  },
  features: {
    search: true,
    columnFilters: true,
    columnVisibility: true,
    columnResizing: false,
    sorting: true,
    export: false,
  },
  ui: {
    striped: true,
    hover: true,
    bordered: false,
    compact: false,
  },
  loading: {
    skeleton: true,
    spinner: true,
  },
}

/**
 * Merge user overrides with default datatable configuration
 */
export function createDataTableConfig(overrides: Partial<DataTableConfig> = {}): DataTableConfig {
  return {
    ...defaultDataTableConfig,
    ...overrides,
    pagination: {
      ...defaultDataTableConfig.pagination!,
      ...overrides.pagination,
    },
    selection: {
      ...defaultDataTableConfig.selection!,
      ...overrides.selection,
    },
    features: {
      ...defaultDataTableConfig.features!,
      ...overrides.features,
    },
    ui: {
      ...defaultDataTableConfig.ui!,
      ...overrides.ui,
    },
    loading: {
      ...defaultDataTableConfig.loading!,
      ...overrides.loading,
    },
  }
}
