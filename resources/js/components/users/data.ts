import { createDataTableConfig } from '@/components/app/DataTable/config'
import type { BaseActionDefinition, BaseBulkActionDefinition } from '@/composables/useDataTableActions'
import type { User } from '@/types'
import type { DataTableColumn, DataTableConfig } from '@/types/datatable'
import { h, resolveComponent } from 'vue'

const UAvatar = resolveComponent('UAvatar')
const UBadge = resolveComponent('UBadge')

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
    cell: ({ row }) => {
      return h('div', { class: 'flex items-center gap-3' }, [
        h(UAvatar, {
          ...row.original.avatar,
          size: 'md',
        }),
        h('div', undefined, [
          h('p', { class: 'font-medium text-highlighted' }, row.original.name),
          h('p', { class: 'text-xs text-muted' }, `@${row.original.name?.toLowerCase()?.replace(/\s+/g, '') ?? ''}`),
        ]),
      ])
    },
  },
  {
    accessorKey: 'email',
    header: 'Email',
    sortable: true,
  },
  {
    accessorKey: 'email_verified_at',
    header: 'Status',
    sortable: true,
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
