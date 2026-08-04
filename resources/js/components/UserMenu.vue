<script setup lang="ts">
  import { logout } from '@/routes'
  import { router } from '@inertiajs/vue3'
  import type { DropdownMenuItem } from '@nuxt/ui'

  defineProps<{
    collapsed?: boolean
  }>()

  const auth = useAuth()
  const { getInitials } = useInitials()

  const user = computed(() => ({
    name: auth.value.user.name,
    avatar: {
      text: getInitials(auth.value.user.name),
    },
  }))

  const items = computed<DropdownMenuItem[][]>(() => [
    [
      {
        type: 'label',
        label: user.value.name,
        avatar: user.value.avatar,
      },
    ],
    [
      {
        label: 'Profile',
        icon: 'i-lucide-user',
      },
      {
        label: 'Billing',
        icon: 'i-lucide-credit-card',
      },
      {
        label: 'Settings',
        icon: 'i-lucide-settings',
        to: '/settings/profile',
      },
    ],
    [
      {
        label: 'Log out',
        icon: 'i-lucide-log-out',
        to: logout(),
        onSelect: () => handleLogout(),
      },
    ],
  ])

  function handleLogout() {
    router.flushAll()
  }
</script>

<template>
  <UDropdownMenu
    :items="items"
    :content="{ align: 'center', collisionPadding: 12 }"
    :ui="{ content: collapsed ? 'w-48' : 'w-(--reka-dropdown-menu-trigger-width)' }"
  >
    <UButton
      v-bind="{
        ...user,
        label: collapsed ? undefined : auth.user.name,
        trailingIcon: collapsed ? undefined : 'i-lucide-chevrons-up-down',
      }"
      color="neutral"
      variant="ghost"
      block
      :square="collapsed"
      class="data-[state=open]:bg-elevated"
      :ui="{
        trailingIcon: 'text-dimmed',
      }"
    />
  </UDropdownMenu>
</template>
