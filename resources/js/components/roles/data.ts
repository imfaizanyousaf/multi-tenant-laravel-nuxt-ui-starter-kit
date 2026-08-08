import { createDataTableConfig } from '@/components/app/DataTable/config'
import type { BaseActionDefinition, BaseBulkActionDefinition } from '@/composables/useDataTableActions'
import { ROLE_SUPER_ADMIN } from '@/constants/roles'
import type { Role } from '@/types'
import type { DataTableColumn, DataTableConfig } from '@/types/datatable'
import { h, resolveComponent } from 'vue'

const UBadge = resolveComponent('UBadge')

/**
 * Datatable default configuration for Roles
 */
export const config: DataTableConfig = createDataTableConfig()

/**
 * Column definitions for Roles Datatable
 */
export const columns: DataTableColumn<Role>[] = [
  {
    accessorKey: 'name',
    header: 'Role Name',
    sortable: true,
    cell: ({ row }) => {
      const isSuperAdmin = row.original.name === ROLE_SUPER_ADMIN
      return h('div', { class: 'flex items-center gap-2' }, [
        h('span', { class: 'font-medium text-foreground' }, row.original.name),
        isSuperAdmin
          ? h(
              UBadge,
              {
                variant: 'subtle',
                color: 'warning',
                size: 'sm',
              },
              () => 'System',
            )
          : null,
      ])
    },
  },
  {
    accessorKey: 'permissions',
    header: 'Permissions',
    sortable: false,
    cell: ({ row }) => {
      const count = row.original.permissions?.length ?? 0
      return h(
        UBadge,
        {
          variant: 'subtle',
          color: count > 0 ? 'info' : 'neutral',
        },
        () => `${count} permission${count === 1 ? '' : 's'}`,
      )
    },
  },
]

/**
 * Base row action definitions
 */
export const baseRowActions: BaseActionDefinition[] = [
  {
    id: 'edit',
    label: 'Edit role details',
    icon: 'i-lucide-pencil',
  },
  {
    id: 'delete',
    label: 'Delete role',
    icon: 'i-lucide-trash',
    color: 'error',
  },
]

/**
 * Base bulk action definitions
 */
export const baseBulkActions: BaseBulkActionDefinition[] = [
  {
    id: 'bulk-delete',
    label: 'Delete',
    icon: 'i-lucide-trash',
    color: 'error',
  },
]
