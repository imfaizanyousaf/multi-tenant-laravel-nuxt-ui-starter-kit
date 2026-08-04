import type { DataTableConfig, DataTableServerParams, DataTableServerResponse } from '@/types/datatable'
import { useFetch } from '@vueuse/core'
import { computed, ref } from 'vue'

function debounce<T extends (...args: any[]) => any>(fn: T, delay: number): (...args: Parameters<T>) => void {
  let timeoutId: ReturnType<typeof setTimeout> | null = null
  return (...args: Parameters<T>) => {
    if (timeoutId) clearTimeout(timeoutId)
    timeoutId = setTimeout(() => fn(...args), delay)
  }
}

export interface UseDataTableServerOptions<T = any> {
  endpoint: string
  initialParams?: DataTableServerParams
  config?: DataTableConfig
  searchDebounce?: number
  onError?: (error: any) => void
  onSuccess?: (data: DataTableServerResponse<T>) => void
}

export function useDataTableServer<T = any>(options: UseDataTableServerOptions<T>) {
  const { endpoint, initialParams = {}, config = {}, searchDebounce = 300, onError, onSuccess } = options

  const params = ref<DataTableServerParams>({
    page: 1,
    per_page: config.pagination?.pageSize || 10,
    sort: '',
    search: '',
    filters: {},
    ...initialParams,
  })

  const loading = ref(false)
  const error = ref<any>(null)

  const url = computed(() => {
    const searchParams = new URLSearchParams()

    Object.entries(params.value).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        if (key === 'filters' && typeof value === 'object') {
          Object.entries(value).forEach(([filterKey, filterValue]) => {
            if (filterValue !== undefined && filterValue !== null && filterValue !== '') {
              searchParams.append(filterKey, String(filterValue))
            }
          })
        } else {
          searchParams.append(key, String(value))
        }
      }
    })

    const queryString = searchParams.toString()
    return queryString ? `${endpoint}?${queryString}` : endpoint
  })

  const { data, isFetching, execute } = useFetch(url, {
    refetch: true,
    initialData: {
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 },
    },
    afterFetch: (ctx) => {
      loading.value = false
      error.value = null
      onSuccess?.(ctx.data as DataTableServerResponse<T>)
      return ctx
    },
    onFetchError: (ctx) => {
      loading.value = false
      error.value = ctx.error
      onError?.(ctx.error)
      return ctx
    },
  }).json<DataTableServerResponse<T>>()

  const updateParams = (newParams: Partial<DataTableServerParams>) => {
    params.value = { ...params.value, ...newParams }
  }

  const setPage = (page: number) => {
    updateParams({ page })
  }

  const setPageSize = (per_page: number) => {
    updateParams({ page: 1, per_page })
  }

  const setSorting = (sort: string) => {
    updateParams({ sort, page: 1 })
  }

  const setSearchImmediate = (search: string) => {
    updateParams({ search, page: 1 })
  }

  const setSearch = debounce(setSearchImmediate, searchDebounce)

  const setFilters = (filters: Record<string, any>) => {
    updateParams({ filters, page: 1 })
  }

  const setFilter = (key: string, value: any) => {
    const newFilters = { ...params.value.filters }
    if (value === undefined || value === null || value === '') {
      delete newFilters[key]
    } else {
      newFilters[key] = value
    }
    setFilters(newFilters)
  }

  const clearFilters = () => {
    updateParams({ filters: {}, page: 1 })
  }

  const clearAllFilters = () => {
    updateParams({ search: '', filters: {}, page: 1 })
  }

  const refresh = () => {
    loading.value = true
    execute()
  }

  const items = computed(() => data.value?.data || [])
  const meta = computed(
    () =>
      data.value?.meta || {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0,
      },
  )
  const links = computed(() => data.value?.links || { first: '', last: '', prev: null, next: null })

  const totalPages = computed(() => meta.value.last_page)
  const currentPage = computed(() => meta.value.current_page)
  const pageSize = computed(() => meta.value.per_page)
  const totalItems = computed(() => meta.value.total)
  const hasNextPage = computed(() => currentPage.value < totalPages.value)
  const hasPrevPage = computed(() => currentPage.value > 1)

  return {
    loading: computed(() => loading.value || isFetching.value),
    error,
    items,
    meta,
    links,
    params: computed(() => params.value),
    hasNextPage,
    hasPrevPage,
    totalPages,
    currentPage,
    pageSize,
    totalItems,
    updateParams,
    setPage,
    setPageSize,
    setSorting,
    setSearch,
    setFilters,
    setFilter,
    clearFilters,
    clearAllFilters,
    refresh,
  }
}
