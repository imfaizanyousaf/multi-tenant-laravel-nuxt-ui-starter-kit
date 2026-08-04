import type { Row } from '@tanstack/table-core'

export interface BaseActionDefinition {
  id: string
  label: string
  icon?: string
  color?: 'primary' | 'secondary' | 'success' | 'warning' | 'error' | 'neutral'
  variant?: 'solid' | 'outline' | 'soft' | 'ghost' | 'link'
  disabled?: boolean | ((row: Row<any>) => boolean)
  visible?: boolean | ((row: Row<any>) => boolean)
  onClick?: RowActionHandler<any>
}

export interface BaseBulkActionDefinition {
  id: string
  label: string
  icon?: string
  color?: 'primary' | 'secondary' | 'success' | 'warning' | 'error' | 'neutral'
  variant?: 'solid' | 'outline' | 'soft' | 'ghost' | 'link'
  disabled?: boolean | ((rows: Row<any>[]) => boolean)
  visible?: boolean | ((rows: Row<any>[]) => boolean)
  onClick?: BulkActionHandler<any>
}

export interface BaseToolbarActionDefinition {
  id: string
  label: string
  icon?: string
  color?: 'primary' | 'secondary' | 'success' | 'warning' | 'error' | 'neutral'
  variant?: 'solid' | 'outline' | 'soft' | 'ghost' | 'link'
  disabled?: boolean
  visible?: boolean | (() => boolean)
  onClick?: ToolbarActionHandler
}

export type RowActionHandler<T = any> = (row: Row<T>) => void | Promise<void>
export type BulkActionHandler<T = any> = (rows: Row<T>[]) => void | Promise<void>
export type ToolbarActionHandler = () => void | Promise<void>

export function createActions(
  baseActions: (BaseActionDefinition | BaseBulkActionDefinition | BaseToolbarActionDefinition)[],
  handlers?: Record<string, any>,
): any[] {
  return baseActions.map((action) => {
    const handler = handlers?.[action.id]

    if (!handler) {
      if (action.onClick) {
        return {
          ...action,
          onClick: action.onClick,
        }
      }
      return {
        ...action,
        onClick: () => {},
      }
    }

    if (typeof handler === 'function') {
      return {
        ...action,
        onClick: handler,
      }
    }

    const { onClick, loading, disabled, visible, ...customParams } = handler

    return {
      ...action,
      onClick,
      loading,
      disabled: disabled ?? action.disabled,
      visible: visible ?? action.visible,
      ...customParams,
    }
  })
}
