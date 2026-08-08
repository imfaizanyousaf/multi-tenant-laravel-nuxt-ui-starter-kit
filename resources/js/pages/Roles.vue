<script setup lang="ts">
  import { destroy, destroyBulk, table } from '@/actions/App/Http/Controllers/RoleController'
  import DataTable from '@/components/app/DataTable/Index.vue'
  import { baseBulkActions, baseRowActions, columns, config } from '@/components/roles/data'
  import RolesFormModal from '@/components/roles/RolesFormModal.vue'
  import { useAuth } from '@/composables/useAuth'
  import { useConfirm } from '@/composables/useConfirm'
  import { createActions } from '@/composables/useDataTableActions'
  import { ROLE_SUPER_ADMIN } from '@/constants/roles'
  import Layout from '@/layouts/Default.vue'
  import { router } from '@inertiajs/vue3'
  import { computed, ref } from 'vue'

  defineOptions({ layout: Layout })

  const dataTable = ref()
  const roleFormModal = ref()
  const { confirm } = useConfirm()
  const toast = useToast()
  const { hasAnyPermission, hasPermission } = useAuth()

  const rowActions = computed(() => {
    const available = baseRowActions.filter((action) => {
      if (action.id === 'edit') return hasPermission('update roles')
      if (action.id === 'delete') return hasPermission('delete roles')
      return true
    })

    return createActions(available, {
      edit: {
        onClick: (row: any) => {
          roleFormModal.value?.openModal(row.original)
        },
        visible: (row: any) => row.original.name !== ROLE_SUPER_ADMIN,
      },
      delete: (row: any) => {
        if (row.original.name === ROLE_SUPER_ADMIN) {
          toast.add({
            title: 'Action prohibited',
            description: 'The Super Admin role cannot be deleted.',
            color: 'error',
          })
          return
        }

        confirm({
          title: 'Delete Role',
          description: `Are you sure you want to delete the "${row.original.name}" role? This action cannot be undone.`,
          confirmText: 'Delete Role',
          color: 'error',
          onConfirm: async () => {
            return new Promise<boolean>((resolve) => {
              router.delete(destroy.url(row.original.id), {
                preserveScroll: true,
                onSuccess: () => {
                  toast.add({
                    title: 'Role deleted',
                    description: `${row.original.name} has been deleted successfully.`,
                    color: 'success',
                  })
                  handleRefresh()
                  resolve(true)
                },
                onError: (errors) => {
                  const message = typeof errors.role === 'string' ? errors.role : 'Failed to delete role.'
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
  })

  const bulkActions = computed(() => {
    if (!hasPermission('delete roles')) return []

    return createActions(baseBulkActions, {
      'bulk-delete': (rows: any[]) => {
        const deletableRows = rows.filter((r) => r.original.name !== ROLE_SUPER_ADMIN)
        if (deletableRows.length === 0) {
          toast.add({
            title: 'Action prohibited',
            description: 'Selected role(s) cannot be deleted.',
            color: 'error',
          })
          return
        }

        const ids = deletableRows.map((row) => row.original.id)
        confirm({
          title: 'Delete Selected Roles',
          description: `Are you sure you want to delete ${ids.length} selected role${ids.length === 1 ? '' : 's'}? This action cannot be undone.`,
          confirmText: 'Delete Roles',
          color: 'error',
          onConfirm: async () => {
            return new Promise<boolean>((resolve) => {
              router.delete(destroyBulk.url(), {
                data: { ids },
                preserveScroll: true,
                onSuccess: () => {
                  toast.add({
                    title: 'Roles deleted',
                    description: `${ids.length} role${ids.length === 1 ? '' : 's'} deleted successfully.`,
                    color: 'success',
                  })
                  handleRefresh()
                  resolve(true)
                },
                onError: () => {
                  toast.add({
                    title: 'Error',
                    description: 'Failed to delete selected roles.',
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
  })

  function handleRefresh() {
    dataTable.value?.refresh()
  }
</script>

<template>
  <UDashboardPanel id="roles">
    <template #header>
      <UDashboardNavbar title="Roles">
        <template #leading>
          <UDashboardSidebarCollapse as="button" :disabled="false" />
        </template>

        <template #right>
          <RolesFormModal v-if="hasAnyPermission(['create roles', 'update roles'])" ref="roleFormModal" @success="handleRefresh" />
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
        search-placeholder="Search roles..."
        server-side
        @refresh="handleRefresh"
      />
    </template>
  </UDashboardPanel>
</template>
