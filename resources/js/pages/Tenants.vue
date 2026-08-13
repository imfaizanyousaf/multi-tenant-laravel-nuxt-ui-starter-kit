<script setup lang="ts">
  import { destroy, table } from '@/actions/App/Http/Controllers/TenantController'
  import DataTable from '@/components/app/DataTable/Index.vue'
  import { baseRowActions, columns, config } from '@/components/tenants/data'
  import TenantsFormModal from '@/components/tenants/TenantsFormModal.vue'
  import { useConfirm } from '@/composables/useConfirm'
  import { useAuth } from '@/composables/useAuth'
  import { createActions } from '@/composables/useDataTableActions'
  import Layout from '@/layouts/Default.vue'
  import { router } from '@inertiajs/vue3'
  import { computed, ref } from 'vue'
  import { useIntervalFn } from '@vueuse/core'

  defineOptions({ layout: Layout })

  const dataTable = ref()
  const tenantFormModal = ref()
  const { confirm } = useConfirm()
  const { hasPermission } = useAuth()
  const toast = useToast()

  const rowActions = computed(() => {
    const actions = createActions(baseRowActions, {
      delete: {
        visible: (row: any) => row.original.status.value !== 'creating',
        onClick: (row: any) => {
          confirm({
            title: 'Delete Tenant',
            description: `Are you sure you want to delete ${row.original.name}? This action cannot be undone and will physically drop the database.`,
            verificationText: row.original.database,
            confirmText: 'Delete Tenant',
            color: 'error',
            onConfirm: async (verificationInput?: string) => {
              return new Promise<boolean>((resolve) => {
                router.delete(destroy.url(row.original.id), {
                  data: { confirmation: verificationInput },
                  preserveScroll: true,
                  onSuccess: () => {
                    toast.add({
                      title: 'Tenant deleted',
                      description: `${row.original.name} has been deleted successfully.`,
                      color: 'success',
                    })
                    handleRefresh()
                    resolve(true)
                  },
                  onError: () => {
                    toast.add({
                      title: 'Error',
                      description: 'Failed to delete tenant.',
                      color: 'error',
                    })
                    resolve(false)
                  },
                })
              })
            },
          })
        },
      },
    })

    return actions.filter((action) => {
      if (action.id === 'delete') return hasPermission('delete tenants')
      return true
    })
  })

  function handleRefresh() {
    dataTable.value?.refresh()
  }

  useIntervalFn(() => {
    if (!dataTable.value?.data) return
    const hasCreating = dataTable.value.data.some((tenant: any) => tenant.status.value === 'creating')
    if (hasCreating) {
      handleRefresh()
    }
  }, 5000)
</script>

<template>
  <UDashboardPanel id="tenants">
    <template #header>
      <UDashboardNavbar title="Tenants">
        <template #leading>
          <UDashboardSidebarCollapse as="button" :disabled="false" />
        </template>

        <template #right>
          <TenantsFormModal v-if="hasPermission('create tenants')" ref="tenantFormModal" @success="handleRefresh" />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <DataTable
        ref="dataTable"
        :actions="rowActions"
        :columns="columns"
        :config="config"
        :endpoint="table.url()"
        search-placeholder="Search tenants..."
        server-side
        @refresh="handleRefresh"
      >
        <template #domain-cell="{ row }">
          <a :href="'http://' + row.original.domain" target="_blank" class="text-primary-500 hover:underline">
            {{ row.original.domain }}
          </a>
        </template>
        <template #status-cell="{ row }">
          <UBadge
            variant="subtle"
            :color="row.original.status.color"
            :icon="row.original.status.loading ? 'i-lucide-loader' : null"
            :ui="{ leadingIcon: 'animate-spin' }"
          >
            {{ row.original.status.label }}
          </UBadge>
        </template>
      </DataTable>
    </template>
  </UDashboardPanel>
</template>
