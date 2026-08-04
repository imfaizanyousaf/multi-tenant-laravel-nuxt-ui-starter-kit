<script generic="T = any" lang="ts" setup>
  import { useDataTableContext } from '@/composables/useDataTableContext'
  import type { DataTableBulkAction } from '@/types/datatable'
  import { computed, resolveComponent } from 'vue'

  interface Props {
    bulkActions?: DataTableBulkAction<T>[]
  }

  defineProps<Props>()

  const context = useDataTableContext<T>()

  const USelect = resolveComponent('USelect')
  const UPagination = resolveComponent('UPagination')

  const displayFrom = computed(() => {
    if (context.serverSide) {
      return context.from?.value || 0
    }
    return 0
  })

  const displayTo = computed(() => {
    if (context.serverSide) {
      return context.to?.value || 0
    }
    return 0
  })

  const displayTotal = computed(() => {
    return context.totalItems?.value || 0
  })

  const currentPageSize = computed(() => {
    return context.pageSize?.value || context.config.pagination?.pageSize || 10
  })

  const currentPageNumber = computed(() => {
    return context.currentPage?.value || 1
  })
</script>

<template>
  <div class="flex items-center justify-between gap-3 border-t border-default pt-4">
    <div class="flex items-center gap-4 text-sm text-muted">
      <div v-if="context.serverSide">Showing {{ displayFrom }} to {{ displayTo }} of {{ displayTotal }} records</div>
      <div v-if="context.config.selection?.enabled && context.selectedRows.value.length > 0">({{ context.selectedRows.value.length }} selected)</div>
    </div>

    <div v-if="context.config.pagination?.enabled" class="flex items-center gap-1.5">
      <USelect
        v-if="context.config.pagination?.showPageSizeSelector"
        :items="context.config.pagination.pageSizeOptions.map((size: number) => ({ label: `${size} per page`, value: size }))"
        :model-value="currentPageSize"
        @update:model-value="context.setPageSize"
      />

      <UPagination :default-page="currentPageNumber" :items-per-page="currentPageSize" :total="displayTotal" @update:page="context.setPage" />
    </div>
  </div>
</template>
