import { dashboard, users } from '@/routes'
import { edit as profileEdit } from '@/routes/profile'
import { router } from '@inertiajs/vue3'
import { createSharedComposable } from '@vueuse/core'
import { ref } from 'vue'

const _useDashboard = () => {
  const isNotificationsSlideoverOpen = ref(false)

  defineShortcuts({
    'g-h': () => router.visit(dashboard.url()),
    'g-u': () => router.visit(users.url()),
    'g-s': () => router.visit(profileEdit.url()),
    n: () => (isNotificationsSlideoverOpen.value = !isNotificationsSlideoverOpen.value),
  })

  router.on('navigate', () => {
    isNotificationsSlideoverOpen.value = false
  })

  return {
    isNotificationsSlideoverOpen,
  }
}

export const useDashboard = createSharedComposable(_useDashboard)
