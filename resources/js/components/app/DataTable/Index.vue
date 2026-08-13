<script generic="T = any" lang="ts" setup>
  import { createDataTableContext, provideDataTableContext } from '@/composables/useDataTableContext'
  import { useDataTableServer } from '@/composables/useDataTableServer'
  import type { DataTableProps } from '@/types/datatable'
  import type { Row } from '@tanstack/table-core'
  import { computed, nextTick, onMounted, onUnmounted, watch } from 'vue'
  import Body from './Body.vue'
  import BulkActions from './BulkActions.vue'
  import { createDataTableConfig, defaultDataTableConfig } from './config'
  import Footer from './Footer.vue'
  import Toolbar from './Toolbar.vue'

  const props = withDefaults(defineProps<DataTableProps<T>>(), {
    serverSide: false,
    loading: false,
    searchPlaceholder: 'Search...',
    config: () => defaultDataTableConfig,
  })

  const emit = defineEmits<{
    rowClick: [row: Row<T>]
    selectionChange: [rows: Row<T>[]]
    filterChange: [filters: Record<string, any>]
    refresh: []
  }>()

  const mergedConfig = computed(() => createDataTableConfig(props.config))

  const serverData =
    props.serverSide && props.endpoint
      ? useDataTableServer<T>({
          endpoint: props.endpoint,
          initialParams: props.params,
          config: mergedConfig.value,
          searchDebounce: typeof mergedConfig.value.features?.search === 'object' ? mergedConfig.value.features.search.debounce || 300 : 300,
        })
      : null

  const context = createDataTableContext<T>({
    config: mergedConfig.value,
    serverSide: props.serverSide,
    serverData,
    loading: computed(() => props.loading),
    onFilterChange: (filters) => emit('filterChange', filters),
    onRefresh: () => emit('refresh'),
  })

  provideDataTableContext(context)

  const data = computed(() => {
    if (props.serverSide && serverData) {
      return serverData.items.value || []
    }
    return props.data || []
  })

  const handleRowClick = (row: Row<T>) => {
    emit('rowClick', row)
  }

  const handleSelectionChange = (rows: Row<T>[]) => {
    emit('selectionChange', rows)
  }

  const handleSortChange = (sortState: any) => {
    if (props.serverSide && serverData) {
      const sortString = sortState.map((sort: any) => `${sort.id}.${sort.desc ? 'desc' : 'asc'}`).join('-')
      context.setSorting(sortString)
    }
  }

  let selectionTimeout: ReturnType<typeof setTimeout> | null = null
  watch(
    () => context.tableRef.value?.tableApi?.getState().rowSelection,
    async () => {
      if (selectionTimeout) {
        clearTimeout(selectionTimeout)
      }

      selectionTimeout = setTimeout(async () => {
        await nextTick()
        if (context.tableRef.value?.tableApi) {
          const selectedRows = context.tableRef.value.tableApi.getFilteredSelectedRowModel().rows
          handleSelectionChange(selectedRows)
        }
      }, 10)
    },
    { deep: true },
  )

  const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && context.selectedRows.value.length > 0) {
      context.clearSelection()
    }
  }

  onMounted(() => {
    document.addEventListener('keydown', handleKeydown)
  })

  onUnmounted(() => {
    if (selectionTimeout) {
      clearTimeout(selectionTimeout)
    }
    document.removeEventListener('keydown', handleKeydown)
  })

  defineExpose({
    setFilter: context.setFilter,
    clearAllFilters: context.clearAllFilters,
    getFilters: () => context.dynamicFilters.value,
    refresh: context.refresh,
    clearSelection: context.clearSelection,
    data,
  })
</script>

<template>
  <div class="space-y-4">
    <Toolbar :columns="columns" :search-placeholder="searchPlaceholder" :toolbar-actions="toolbarActions">
      <template #filters>
        <slot :clear-filter="context.clearAllFilters" :filters="context.dynamicFilters.value" :set-filter="context.setFilter" name="filters" />
      </template>
    </Toolbar>

    <Body :actions="actions" :columns="columns" :data="data" @select="handleRowClick" @update:sorting="handleSortChange">
      <template v-for="(_, name) in $slots" :key="name" #[name]="slotData">
        <slot :name="name" v-bind="slotData" />
      </template>
    </Body>

    <Footer :bulk-actions="bulkActions" />

    <BulkActions :bulk-actions="bulkActions" class="fixed bottom-8 left-1/2 z-50 -translate-x-1/2 transform" />
  </div>
</template>
