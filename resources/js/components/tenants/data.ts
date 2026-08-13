import { createDataTableConfig } from '@/components/app/DataTable/config'
import type { BaseActionDefinition } from '@/composables/useDataTableActions'
import type { DataTableColumn, DataTableConfig } from '@/types/datatable'

export const config: DataTableConfig = createDataTableConfig()

export const columns: DataTableColumn<any>[] = [
  {
    accessorKey: 'name',
    header: 'Name',
    sortable: true,
  },
  {
    accessorKey: 'domain',
    header: 'Domain',
    sortable: true,
  },
  {
    accessorKey: 'status',
    header: 'Status',
    sortable: true,
  },
  {
    accessorKey: 'database',
    header: 'Database',
    sortable: true,
  },
  {
    accessorKey: 'created_at',
    header: 'Created At',
    sortable: true,
  },
]

export const baseRowActions: BaseActionDefinition[] = [
  {
    id: 'delete',
    label: 'Delete tenant',
    icon: 'i-lucide-trash',
    color: 'error',
  },
]
