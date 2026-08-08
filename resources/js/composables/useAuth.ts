import type { Auth } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useAuth() {
  const page = usePage()
  const auth = computed<Auth>(() => page.props.auth as Auth)
  const user = computed(() => auth.value?.user)
  const permissions = computed(() => user.value?.permissions ?? [])

  function hasPermission(permission: string): boolean {
    return permissions.value.includes(permission)
  }

  function hasAnyPermission(permissionList: string[]): boolean {
    return permissionList.some((p) => permissions.value.includes(p))
  }

  function hasRole(role: string): boolean {
    return user.value?.roles?.includes(role) ?? false
  }

  return {
    auth,
    user,
    permissions,
    hasPermission,
    can: hasPermission,
    hasAnyPermission,
    hasRole,
  }
}
