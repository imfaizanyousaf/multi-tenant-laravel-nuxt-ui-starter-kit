<script setup lang="ts">
  import Layout from '@/layouts/Default.vue'
  import type { TableColumn } from '@nuxt/ui'
  import { getPaginationRowModel, type Row } from '@tanstack/table-core'
  import { upperFirst } from 'scule'
  import { computed, h, ref, resolveComponent, useTemplateRef } from 'vue'
  import type { User } from '../types'

  defineOptions({ layout: Layout })

  const props = withDefaults(
    defineProps<{
      users?: User[]
    }>(),
    {
      users: () => [],
    },
  )

  const UAvatar = resolveComponent('UAvatar')
  const UButton = resolveComponent('UButton')
  const UBadge = resolveComponent('UBadge')
  const UDropdownMenu = resolveComponent('UDropdownMenu')
  const UCheckbox = resolveComponent('UCheckbox')

  const toast = useToast()
  const table = useTemplateRef('table')

  const columnFilters = ref([
    {
      id: 'email',
      value: '',
    },
  ])
  const columnVisibility = ref()
  const rowSelection = ref({ 1: true })

  const data = computed(() => props.users)

  function getRowItems(row: Row<User>) {
    return [
      {
        type: 'label',
        label: `Actions for ${row.original.name}`,
      },
      {
        label: 'View user details',
        icon: 'i-lucide-list',
      },
      {
        type: 'separator',
      },
      {
        label: 'Delete user',
        icon: 'i-lucide-trash',
        color: 'error',
        onSelect() {
          toast.add({
            title: 'User deleted',
            description: `${row.original.name} has been deleted.`,
          })
        },
      },
    ]
  }

  const columns: TableColumn<User>[] = [
    {
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
    },
    {
      accessorKey: 'name',
      header: 'Name',
      cell: ({ row }) => {
        return h('div', { class: 'flex items-center gap-3' }, [
          h(UAvatar, {
            ...row.original.avatar,
            size: 'lg',
          }),
          h('div', undefined, [h('p', { class: 'font-medium text-highlighted' }, row.original.name), h('p', { class: '' }, `@${row.original.name}`)]),
        ])
      },
    },
    {
      accessorKey: 'email',
      header: ({ column }) => {
        const isSorted = column.getIsSorted()

        return h(UButton, {
          color: 'neutral',
          variant: 'ghost',
          label: 'Email',
          icon: isSorted ? (isSorted === 'asc' ? 'i-lucide-arrow-up-narrow-wide' : 'i-lucide-arrow-down-wide-narrow') : 'i-lucide-arrow-up-down',
          class: '-mx-2.5',
          onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
        })
      },
    },
    {
      accessorKey: 'email_verified_at',
      header: 'Email Verified',
      cell: ({ row }) => {
        const isVerified = !!row.original.email_verified_at
        return h(
          UBadge,
          {
            variant: 'subtle',
            color: isVerified ? 'success' : 'neutral',
          },
          () => (isVerified ? 'Verified' : 'Unverified'),
        )
      },
    },
    {
      id: 'actions',
      cell: ({ row }) => {
        return h(
          'div',
          { class: 'text-right' },
          h(
            UDropdownMenu,
            {
              content: {
                align: 'end',
              },
              items: getRowItems(row),
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
      },
    },
  ]

  const pagination = ref({
    pageIndex: 0,
    pageSize: 10,
  })
</script>

<template>
  <UDashboardPanel id="users">
    <template #header>
      <UDashboardNavbar title="Users">
        <template #leading>
          <UDashboardSidebarCollapse as="button" :disabled="false" />
        </template>

        <template #right>
          <UsersAddModal />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="flex flex-wrap items-center justify-between gap-1.5">
        <UInput
          :model-value="table?.tableApi?.getColumn('email')?.getFilterValue() as string"
          class="max-w-sm"
          icon="i-lucide-search"
          placeholder="Filter emails..."
          @update:model-value="table?.tableApi?.getColumn('email')?.setFilterValue($event)"
        />

        <div class="flex flex-wrap items-center gap-1.5">
          <UsersDeleteModal :count="table?.tableApi?.getFilteredSelectedRowModel().rows.length">
            <UButton
              v-if="table?.tableApi?.getFilteredSelectedRowModel().rows.length"
              label="Delete"
              color="error"
              variant="subtle"
              icon="i-lucide-trash"
            >
              <template #trailing>
                <UKbd>
                  {{ table?.tableApi?.getFilteredSelectedRowModel().rows.length }}
                </UKbd>
              </template>
            </UButton>
          </UsersDeleteModal>

          <UDropdownMenu
            :items="
              table?.tableApi
                ?.getAllColumns()
                .filter((column: any) => column.getCanHide())
                .map((column: any) => ({
                  label: upperFirst(column.id),
                  type: 'checkbox' as const,
                  checked: column.getIsVisible(),
                  onUpdateChecked(checked: boolean) {
                    table?.tableApi?.getColumn(column.id)?.toggleVisibility(!!checked)
                  },
                  onSelect(e?: Event) {
                    e?.preventDefault()
                  },
                }))
            "
            :content="{ align: 'end' }"
          >
            <UButton label="Display" color="neutral" variant="outline" trailing-icon="i-lucide-settings-2" />
          </UDropdownMenu>
        </div>
      </div>

      <UTable
        ref="table"
        v-model:column-filters="columnFilters"
        v-model:column-visibility="columnVisibility"
        v-model:row-selection="rowSelection"
        v-model:pagination="pagination"
        :pagination-options="{
          getPaginationRowModel: getPaginationRowModel(),
        }"
        class="shrink-0"
        :data="data ?? []"
        :columns="columns"
        :ui="{
          base: 'table-fixed border-separate border-spacing-0',
          thead: '[&>tr]:bg-elevated/50 [&>tr]:after:content-none',
          tbody: '[&>tr]:last:[&>td]:border-b-0',
          th: 'py-2 first:rounded-l-lg last:rounded-r-lg border-y border-default first:border-l last:border-r',
          td: 'border-b border-default',
        }"
      />

      <div class="mt-auto flex items-center justify-between gap-3 border-t border-default pt-4">
        <div class="text-sm text-muted">
          {{ table?.tableApi?.getFilteredSelectedRowModel().rows.length || 0 }} of
          {{ table?.tableApi?.getFilteredRowModel().rows.length || 0 }} row(s) selected.
        </div>

        <div class="flex items-center gap-1.5">
          <UPagination
            :default-page="(table?.tableApi?.getState().pagination.pageIndex || 0) + 1"
            :items-per-page="table?.tableApi?.getState().pagination.pageSize"
            :total="table?.tableApi?.getFilteredRowModel().rows.length"
            @update:page="(p: number) => table?.tableApi?.setPageIndex(p - 1)"
          />
        </div>
      </div>
    </template>
  </UDashboardPanel>
</template>
