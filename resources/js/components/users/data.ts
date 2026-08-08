import { createDataTableConfig } from '@/components/app/DataTable/config'
import type { BaseActionDefinition, BaseBulkActionDefinition } from '@/composables/useDataTableActions'
import type { User } from '@/types'
import type { DataTableColumn, DataTableConfig } from '@/types/datatable'

/**
 * Datatable default configuration (uses central defaults, override options here if needed)
 */
export const config: DataTableConfig = createDataTableConfig()

/**
 * Column definitions for Users Datatable
 */
export const columns: DataTableColumn<User>[] = [
  {
    accessorKey: 'name',
    header: 'Name',
    sortable: true,
  },
  {
    accessorKey: 'roles',
    header: 'Role',
  },
  {
    accessorKey: 'two_factor_enabled',
    header: '2FA',
  },
]

/**
 * Base row action definitions
 */
export const baseRowActions: BaseActionDefinition[] = [
  {
    id: 'edit',
    label: 'Edit user details',
    icon: 'i-lucide-pencil',
  },
  {
    id: 'delete',
    label: 'Delete user',
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
