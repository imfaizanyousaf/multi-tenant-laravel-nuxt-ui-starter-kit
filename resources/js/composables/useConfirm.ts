import ConfirmModal from '@/components/ConfirmModal.vue'

export interface ConfirmOptions {
  title?: string
  description?: string
  cancelText?: string
  confirmText?: string
  variant?: 'link' | 'solid' | 'outline' | 'soft' | 'ghost' | 'subtle'
  color?: 'neutral' | 'primary' | 'secondary' | 'success' | 'info' | 'warning' | 'error'
  onCancel?: () => Promise<void>
  onConfirm?: () => Promise<boolean>
}

const defaultOptions: Required<ConfirmOptions> = {
  title: 'Confirm Action',
  description: 'Are you sure you want to proceed?',
  cancelText: 'Cancel',
  confirmText: 'Confirm',
  variant: 'solid',
  color: 'primary',
  onCancel: async () => {},
  onConfirm: async () => true,
}

export function useConfirm() {
  const overlay = useOverlay()

  const modal = overlay.create(ConfirmModal)

  const confirm = async (options: ConfirmOptions = {}): Promise<boolean> => {
    const mergedOptions = { ...defaultOptions, ...options }

    const instance = modal.open({
      title: mergedOptions.title,
      description: mergedOptions.description,
      cancelText: mergedOptions.cancelText,
      confirmText: mergedOptions.confirmText,
      variant: mergedOptions.variant,
      color: mergedOptions.color,
      onConfirm: mergedOptions.onConfirm!,
      onCancel: mergedOptions.onCancel!,
    })

    const result = await instance.result

    return !!result
  }

  return {
    confirm,
  }
}
