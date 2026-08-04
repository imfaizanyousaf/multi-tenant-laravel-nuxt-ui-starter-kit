import type { DataTableConfig, DataTableServerParams } from '@/types/datatable'
import type { Row } from '@tanstack/table-core'
import { computed, inject, type InjectionKey, provide, type Ref, ref } from 'vue'

export interface DataTableContext<T = any> {
  config: DataTableConfig
  serverSide: boolean

  columnFilters: Ref<any[]>
  columnVisibility: Ref<any>
  rowSelection: Ref<any>
  sorting: Ref<any[]>
  pagination: Ref<any>
  dynamicFilters: Ref<Record<string, any>>

  isLoading: Ref<boolean>
  hasActiveFilters: Ref<boolean>
  selectedRows: Ref<Row<T>[]>

  serverParams?: Ref<DataTableServerParams>
  totalItems?: Ref<number>
  currentPage?: Ref<number>
  pageSize?: Ref<number>
  from?: Ref<number>
  to?: Ref<number>

  setFilter: (key: string, value: any) => void
  clearAllFilters: () => void
  refresh: () => void
  clearSelection: () => void
  setSearch: (search: string) => void
  setPage: (page: number) => void
  setPageSize: (pageSize: number) => void
  setSorting: (sort: string) => void

  tableRef: Ref<any>
}

export const DataTableContextKey: InjectionKey<DataTableContext> = Symbol('DataTableContext')

export function provideDataTableContext<T = any>(context: DataTableContext<T>) {
  provide(DataTableContextKey, context)
}

export function useDataTableContext<T = any>(): DataTableContext<T> {
  const context = inject(DataTableContextKey)
  if (!context) {
    throw new Error('useDataTableContext must be used within a DataTable component')
  }
  return context as DataTableContext<T>
}

export function createDataTableContext<T = any>(options: {
  config: DataTableConfig
  serverSide: boolean
  serverData?: any
  loading?: Ref<boolean>
  onFilterChange?: (filters: Record<string, any>) => void
  onRefresh?: () => void
}): DataTableContext<T> {
  const { config, serverSide, serverData, loading, onFilterChange, onRefresh } = options

  const columnFilters = ref<any[]>([])
  const columnVisibility = ref<any>({})
  const rowSelection = ref<any>({})
  const sorting = ref<any[]>([])
  const pagination = ref({
    pageIndex: 0,
    pageSize: config.pagination?.pageSize || 10,
  })
  const dynamicFilters = ref<Record<string, any>>({})
  const tableRef = ref<any>(null)

  const isLoading = computed(() => {
    if (serverSide && serverData) {
      return serverData.loading.value
    }
    return loading?.value || false
  })

  const hasActiveFilters = computed(() => {
    const hasDynamicFilters = Object.values(dynamicFilters.value).some((value) => value !== null && value !== undefined && value !== '')
    const hasSearch = serverData?.params.value.search && serverData.params.value.search.trim() !== ''
    const hasColumnFilters = columnFilters.value && columnFilters.value.length > 0
    return hasDynamicFilters || hasSearch || hasColumnFilters
  })

  const selectedRows = computed((): Row<T>[] => {
    if (tableRef.value?.tableApi) {
      return tableRef.value.tableApi.getFilteredSelectedRowModel().rows
    }
    return []
  })

  const setFilter = (key: string, value: any) => {
    dynamicFilters.value[key] = value
    if (serverSide && serverData) {
      serverData.setFilter(key, value)
    }
    onFilterChange?.(dynamicFilters.value)
  }

  const clearAllFilters = () => {
    dynamicFilters.value = {}
    if (serverSide && serverData) {
      serverData.clearAllFilters()
    }
    columnFilters.value = []
    onFilterChange?.({})
  }

  const refresh = () => {
    if (serverSide && serverData) {
      serverData.refresh()
    } else {
      onRefresh?.()
    }
  }

  const clearSelection = () => {
    rowSelection.value = {}
    if (tableRef.value?.tableApi) {
      tableRef.value.tableApi.resetRowSelection()
    }
  }

  const setSearch = (search: string) => {
    if (serverSide && serverData) {
      serverData.setSearch(search)
    }
  }

  const setPage = (page: number) => {
    if (serverSide && serverData) {
      serverData.setPage(page)
    }
  }

  const setPageSize = (pageSize: number) => {
    if (serverSide && serverData) {
      serverData.setPageSize(pageSize)
    }
  }

  const setSorting = (sort: string) => {
    if (serverSide && serverData) {
      serverData.setSorting(sort)
    }
  }

  const context: DataTableContext<T> = {
    config,
    serverSide,
    columnFilters,
    columnVisibility,
    rowSelection,
    sorting,
    pagination,
    dynamicFilters,
    tableRef,
    isLoading,
    hasActiveFilters,
    selectedRows,
    setFilter,
    clearAllFilters,
    refresh,
    clearSelection,
    setSearch,
    setPage,
    setPageSize,
    setSorting,
  }

  if (serverSide && serverData) {
    context.serverParams = computed(() => serverData.params.value)
    context.totalItems = computed(() => serverData.totalItems.value)
    context.currentPage = computed(() => serverData.currentPage.value)
    context.pageSize = computed(() => serverData.pageSize.value)
    context.from = computed(() => serverData.meta.value?.from || 0)
    context.to = computed(() => serverData.meta.value?.to || 0)
  }

  return context
}
