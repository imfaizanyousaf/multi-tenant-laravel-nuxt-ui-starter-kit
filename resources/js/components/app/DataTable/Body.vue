<script generic="T = any" lang="ts" setup>
  import { useDataTableContext } from '@/composables/useDataTableContext'
  import type { DataTableAction, DataTableColumn } from '@/types/datatable'
  import { getPaginationRowModel, getSortedRowModel } from '@tanstack/table-core'
  import { computed, h, resolveComponent, useAttrs } from 'vue'

  interface Props {
    data: T[]
    columns: DataTableColumn<T>[]
    actions?: DataTableAction<T>[]
  }

  const props = withDefaults(defineProps<Props>(), {
    actions: () => [],
  })

  const emit = defineEmits<{
    'update:sorting': [value: any[]]
    select: [row: any]
  }>()

  const context = useDataTableContext<T>()

  const UCheckbox = resolveComponent('UCheckbox')
  const UButton = resolveComponent('UButton')
  const UDropdownMenu = resolveComponent('UDropdownMenu')
  const UTooltip = resolveComponent('UTooltip')
  const UTable = resolveComponent('UTable')

  const attrs = useAttrs()

  const clearSelection = () => {
    if (context.tableRef.value?.tableApi) {
      context.tableRef.value.tableApi.resetRowSelection()
    }
  }

  defineExpose({
    tableApi: computed(() => context.tableRef.value?.tableApi),
    clearSelection,
  })

  const columnFiltersModel = computed({
    get: () => context.columnFilters.value,
    set: (value) => (context.columnFilters.value = value),
  })

  const columnVisibilityModel = computed({
    get: () => context.columnVisibility.value,
    set: (value) => (context.columnVisibility.value = value),
  })

  const rowSelectionModel = computed({
    get: () => context.rowSelection.value,
    set: (value) => (context.rowSelection.value = value),
  })

  const sortingModel = computed({
    get: () => context.sorting.value,
    set: (value) => {
      context.sorting.value = value
      emit('update:sorting', value)
    },
  })

  const paginationModel = computed({
    get: () => context.pagination.value,
    set: (value) => (context.pagination.value = value),
  })

  const resizingEnabled = computed(() => {
    return context.config.features?.columnResizing !== false
  })

  const enhancedColumns = computed<DataTableColumn<T>[]>(() => {
    const cols: DataTableColumn<T>[] = []

    if (context.config.selection?.enabled) {
      cols.push({
        id: 'select',
        header: ({ table }) =>
          h(UCheckbox, {
            modelValue: table.getIsSomePageRowsSelected() ? 'indeterminate' : table.getIsAllPageRowsSelected(),
            'onUpdate:modelValue': (value: boolean | 'indeterminate') => table.toggleAllPageRowsSelected(!!value),
            ariaLabel: 'Select all',
          }),
        cell: ({ row }) =>
          h(UCheckbox, {
            modelValue: row.getIsSelected(),
            'onUpdate:modelValue': (value: boolean | 'indeterminate') => row.toggleSelected(!!value),
            ariaLabel: 'Select row',
          }),
        enableSorting: false,
        enableHiding: false,
      })
    }

    const mappedColumns = props.columns.map((col) => {
      const baseColumn = {
        ...col,
        meta: {
          headerLabel: typeof col.header === 'string' ? col.header : '',
          style: resizingEnabled.value
            ? {
                th: `max-width: calc(var(--header-${col?.id}-size) * 1px)`,
                td: `max-width: calc(var(--col-${col?.id}-size) * 1px)`,
              }
            : {},
        },
        enableSorting: col.sortable === true,
        enableMultiSort: col.sortable === true,
      }

      const headerLabel = col.header
      const isHeaderString = typeof col.header === 'string'
      const sortable = col.sortable === true && isHeaderString
      const pinnable = col.pinnable === true && isHeaderString
      const pinnedPosition = col.pinnedPosition || 'left'

      baseColumn.header = ({ column, header }: any) => {
        const isSorted = column.getIsSorted()
        const isPinned = column.getIsPinned()
        const sortIndex = column.getSortIndex()

        return h('div', { class: 'flex items-center relative' }, [
          resizingEnabled.value &&
            h('div', {
              class: `resizer ${header.column.getIsResizing() ? 'isResizing' : ''}`,
              onDblclick: () => column.resetSize(),
              onMousedown: header.getResizeHandler(),
              onTouchstart: header.getResizeHandler(),
            }),
          sortable &&
            h(UButton, {
              color: 'neutral',
              variant: 'ghost',
              size: 'sm',
              label: sortIndex >= 0 ? `${sortIndex + 1}` : null,
              icon: isSorted ? (isSorted === 'asc' ? 'i-lucide-arrow-up-narrow-wide' : 'i-lucide-arrow-down-wide-narrow') : 'i-lucide-arrow-up-down',
              title: 'Click to sort',
              onClick: (event: MouseEvent) => {
                const isMultiSort = event.ctrlKey || event.metaKey || event.shiftKey
                column.toggleSorting(column.getIsSorted() === 'asc', isMultiSort)
              },
            }),
          pinnable &&
            h(UButton, {
              color: 'neutral',
              variant: 'ghost',
              size: 'sm',
              icon: isPinned ? 'i-lucide-pin-off' : 'i-lucide-pin',
              onClick: () => {
                column.pin(isPinned === pinnedPosition ? false : pinnedPosition)
              },
            }),
          h('p', { class: 'flex-shrink-0' }, `${headerLabel}`),
        ])
      }

      return baseColumn
    })
    cols.push(...mappedColumns)

    if (props.actions && props.actions.length > 0) {
      cols.push({
        id: 'actions',
        header: () => h('div', { class: 'text-center' }, 'Actions'),
        cell: ({ row }) => {
          const visibleActions = props.actions!.filter((action) => {
            if (typeof action.visible === 'function') {
              return action.visible(row)
            }
            return action.visible !== false
          })

          if (visibleActions.length === 0) return null

          const isDisabled = (action: DataTableAction<T>) => (typeof action.disabled === 'function' ? action.disabled(row) : action.disabled)

          if (visibleActions.length > 2) {
            const actionItems = visibleActions.map((action) => ({
              label: action.label,
              icon: action.icon,
              color: action.color,
              disabled: isDisabled(action),
              onSelect: () => action.onClick(row),
            }))

            return h(
              'div',
              { class: 'text-center' },
              h(
                UDropdownMenu,
                {
                  content: { align: 'end' },
                  items: actionItems,
                },
                () =>
                  h(UButton, {
                    icon: 'i-lucide-ellipsis-vertical',
                    color: 'neutral',
                    variant: 'ghost',
                    class: 'ml-auto',
                  }),
              ),
            )
          }

          return h(
            'div',
            { class: 'flex items-center justify-center gap-1' },
            visibleActions.map((action) =>
              h(UTooltip, { text: action.label, content: { sideOffset: 10 } }, () =>
                h(UButton, {
                  color: action.color ?? 'neutral',
                  variant: action.variant ?? 'ghost',
                  size: 'sm',
                  icon: action.icon,
                  label: action.icon ? undefined : action.label,
                  disabled: isDisabled(action),
                  onClick: () => action.onClick(row),
                }),
              ),
            ),
          )
        },
        enableSorting: false,
        enableHiding: false,
        meta: {
          style: {
            th: 'width: 100px',
            td: 'width: 100px',
          },
        },
      })
    }

    return cols
  })

  const tableUI = computed(() => ({
    base: 'table-fixed border-separate border-spacing-0',
    thead: '[&>tr]:bg-elevated/50 [&>tr]:after:content-none',
    tbody: '[&>tr]:last:[&>td]:border-b-0',
    th: 'py-2 first:rounded-l-lg last:rounded-r-lg border-y border-default first:border-l last:border-r',
    td: 'border-b border-default',
    ...context.config.ui,
  }))
</script>

<template>
  <UTable
    :ref="(el: any) => (context.tableRef.value = el)"
    v-model:column-filters="columnFiltersModel"
    v-model:column-visibility="columnVisibilityModel"
    v-model:pagination="paginationModel"
    v-model:row-selection="rowSelectionModel"
    v-model:sorting="sortingModel"
    :columns="enhancedColumns"
    :data="Array.isArray(data) ? data : []"
    :loading="context.isLoading.value"
    :pagination-options="{
      manualPagination: context.serverSide,
      getPaginationRowModel: getPaginationRowModel(),
    }"
    :sorting-options="{
      manualSorting: context.serverSide,
      getSortedRowModel: getSortedRowModel(),
      enableMultiSort: true,
      maxMultiSortColCount: 5,
    }"
    :ui="tableUI"
    class="max-h-[calc(100vh-232px)] shrink-0"
    sticky
    v-bind="attrs"
  >
    <template v-for="(_, name) in $slots" :key="name" #[name]="slotData">
      <slot :name="name" v-bind="slotData" />
    </template>
  </UTable>
</template>
