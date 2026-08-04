<script generic="T = any" lang="ts" setup>
  import { useDataTableContext } from '@/composables/useDataTableContext'
  import type { DataTableColumn, DataTableToolbarAction } from '@/types/datatable'
  import { upperFirst } from 'scule'
  import { computed, resolveComponent } from 'vue'

  interface Props {
    searchPlaceholder?: string
    toolbarActions?: DataTableToolbarAction[]
    columns?: DataTableColumn<T>[]
  }

  withDefaults(defineProps<Props>(), {
    searchPlaceholder: 'Search...',
    toolbarActions: () => [],
    columns: () => [],
  })

  const context = useDataTableContext<T>()

  const UInput = resolveComponent('UInput')
  const UButton = resolveComponent('UButton')
  const UDropdownMenu = resolveComponent('UDropdownMenu')

  const searchValue = computed(() => {
    return context.serverParams?.value.search || ''
  })

  const isSearchEnabled = computed(() => {
    const searchConfig = context.config.features?.search
    if (typeof searchConfig === 'boolean') {
      return searchConfig
    }
    if (typeof searchConfig === 'object') {
      return searchConfig.enabled
    }
    return true
  })
</script>

<template>
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-3">
      <UInput
        v-if="isSearchEnabled"
        :model-value="searchValue"
        :ui="{ base: 'peer' }"
        class="min-w-64"
        icon="i-lucide-search"
        placeholder=""
        type="search"
        @update:model-value="context.setSearch"
      >
        <label
          class="pointer-events-none absolute -top-2.5 left-0 pr-1.5 pl-8 text-xs font-medium text-highlighted transition-all peer-placeholder-shown:top-1.5 peer-placeholder-shown:text-sm peer-placeholder-shown:font-normal peer-placeholder-shown:text-dimmed peer-focus:-top-2.5 peer-focus:pl-1.5 peer-focus:text-xs peer-focus:font-medium peer-focus:text-highlighted"
        >
          <span class="inline-flex bg-default px-1">{{ searchPlaceholder }}</span>
        </label>
      </UInput>

      <slot name="filters" />
    </div>

    <div class="flex flex-wrap items-center gap-1.5">
      <UButton
        v-if="context.hasActiveFilters.value"
        color="neutral"
        icon="i-lucide-x"
        size="sm"
        title="Clear all filters"
        variant="outline"
        @click="context.clearAllFilters"
      >
        Clear Filters
      </UButton>

      <template v-if="toolbarActions">
        <UButton
          v-for="action in toolbarActions.filter((a: any) => (typeof a.visible === 'function' ? a.visible() : a.visible !== false))"
          :key="action.id"
          :color="action.color"
          :disabled="action.disabled"
          :icon="action.icon"
          :label="action.label"
          :variant="action.variant"
          @click="action.onClick"
        />
      </template>

      <UDropdownMenu
        v-if="context.config.features?.columnVisibility && context.tableRef.value?.tableApi"
        :content="{ align: 'end' }"
        :items="
          context.tableRef.value.tableApi
            .getAllColumns()
            .filter((column: any) => column.getCanHide())
            .map((column: any) => {
              const headerLabel = column.columnDef?.meta?.headerLabel || upperFirst(column.id)
              return {
                label: headerLabel,
                type: 'checkbox' as const,
                checked: column.getIsVisible(),
                onUpdateChecked(checked: boolean) {
                  context.tableRef.value.tableApi.getColumn(column.id)?.toggleVisibility(!!checked)
                },
                onSelect(e?: Event) {
                  e?.preventDefault()
                },
              }
            })
        "
      >
        <UButton color="neutral" icon="i-lucide-settings-2" size="sm" title="Column visibility" variant="outline" />
      </UDropdownMenu>

      <UButton
        :loading="context.isLoading.value"
        color="neutral"
        icon="i-lucide-refresh-cw"
        size="sm"
        title="Refresh data"
        variant="outline"
        @click="context.refresh"
      />
    </div>
  </div>
</template>
