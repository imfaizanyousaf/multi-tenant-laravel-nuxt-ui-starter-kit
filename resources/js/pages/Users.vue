<script setup lang="ts">
  import { destroy, destroyBulk, table } from '@/actions/App/Http/Controllers/UserController'
  import DataTable from '@/components/app/DataTable/Index.vue'
  import { baseBulkActions, baseRowActions, columns, config } from '@/components/users/data'
  import ColumnName from '@/components/users/datatable/ColumnName.vue'
  import UsersFormModal from '@/components/users/UsersFormModal.vue'
  import { useConfirm } from '@/composables/useConfirm'
  import { createActions } from '@/composables/useDataTableActions'
  import Layout from '@/layouts/Default.vue'
  import { router } from '@inertiajs/vue3'
  import { ref } from 'vue'

  defineOptions({ layout: Layout })

  const dataTable = ref()
  const userFormModal = ref()
  const { confirm } = useConfirm()
  const toast = useToast()

  const rowActions = createActions(baseRowActions, {
    edit: (row: any) => {
      userFormModal.value?.openModal(row.original)
    },
    delete: (row: any) => {
      confirm({
        title: 'Delete User',
        description: `Are you sure you want to delete ${row.original.name}? This action cannot be undone.`,
        confirmText: 'Delete User',
        color: 'error',
        onConfirm: async () => {
          return new Promise<boolean>((resolve) => {
            router.delete(destroy.url(row.original.id), {
              preserveScroll: true,
              onSuccess: () => {
                toast.add({
                  title: 'User deleted',
                  description: `${row.original.name} has been deleted successfully.`,
                  color: 'success',
                })
                handleRefresh()
                resolve(true)
              },
              onError: (errors) => {
                const message = typeof errors.user === 'string' ? errors.user : 'Failed to delete user.'
                toast.add({
                  title: 'Error',
                  description: message,
                  color: 'error',
                })
                resolve(false)
              },
            })
          })
        },
      })
    },
  })

  const bulkActions = createActions(baseBulkActions, {
    'bulk-delete': (rows: any[]) => {
      const ids = rows.map((row) => row.original.id)
      confirm({
        title: 'Delete Selected Users',
        description: `Are you sure you want to delete ${rows.length} selected user${rows.length === 1 ? '' : 's'}? This action cannot be undone.`,
        confirmText: 'Delete Users',
        color: 'error',
        onConfirm: async () => {
          return new Promise<boolean>((resolve) => {
            router.delete(destroyBulk.url(), {
              data: { ids },
              preserveScroll: true,
              onSuccess: () => {
                toast.add({
                  title: 'Users deleted',
                  description: `${rows.length} user${rows.length === 1 ? '' : 's'} deleted successfully.`,
                  color: 'success',
                })
                handleRefresh()
                resolve(true)
              },
              onError: () => {
                toast.add({
                  title: 'Error',
                  description: 'Failed to delete selected users.',
                  color: 'error',
                })
                resolve(false)
              },
            })
          })
        },
      })
    },
  })

  function handleRefresh() {
    dataTable.value?.refresh()
  }
</script>

<template>
  <UDashboardPanel id="users">
    <template #header>
      <UDashboardNavbar title="Users">
        <template #leading>
          <UDashboardSidebarCollapse as="button" :disabled="false" />
        </template>

        <template #right>
          <UsersFormModal ref="userFormModal" @success="handleRefresh" />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <DataTable
        ref="dataTable"
        :actions="rowActions"
        :bulk-actions="bulkActions"
        :columns="columns"
        :config="config"
        :endpoint="table.url()"
        search-placeholder="Search users..."
        server-side
        @refresh="handleRefresh"
      >
        <template #name-cell="{ row }">
          <ColumnName :row="row.original" />
        </template>
      </DataTable>
    </template>
  </UDashboardPanel>
</template>
