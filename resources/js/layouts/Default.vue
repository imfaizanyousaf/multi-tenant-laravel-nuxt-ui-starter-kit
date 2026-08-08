<script setup lang="ts">
  import { useAuth } from '@/composables/useAuth'
  import { appearance } from '@/routes'
  import type { NavigationMenuItem } from '@nuxt/ui'

  const { url } = usePage()
  const toast = useToast()
  const { hasPermission } = useAuth()

  const open = ref(false)

  const links = computed<NavigationMenuItem[][]>(() => [
    [
      {
        label: 'Home',
        icon: 'i-lucide-house',
        to: '/dashboard',
        onSelect: () => {
          open.value = false
        },
      },
      ...(hasPermission('view users')
        ? [
            {
              label: 'Users',
              icon: 'i-lucide-users',
              to: '/users',
              onSelect: () => {
                open.value = false
              },
            },
          ]
        : []),
      ...(hasPermission('view roles')
        ? [
            {
              label: 'Roles',
              icon: 'i-lucide-shield-check',
              to: '/roles',
              onSelect: () => {
                open.value = false
              },
            },
          ]
        : []),
      {
        label: 'Settings',
        to: '/settings',
        icon: 'i-lucide-settings',
        defaultOpen: true,
        type: 'trigger',
        children: [
          {
            label: 'Profile',
            to: '/settings/profile',
            exact: true,
            onSelect: () => {
              open.value = false
            },
          },
          {
            label: 'Members',
            to: '/settings/members',
            onSelect: () => {
              open.value = false
            },
          },
          {
            label: 'Notifications',
            to: '/settings/notifications',
            onSelect: () => {
              open.value = false
            },
          },
          {
            label: 'Security',
            to: '/settings/security',
            onSelect: () => {
              open.value = false
            },
          },
          {
            label: 'Appearance',
            to: appearance.url(),
            onSelect: () => {
              open.value = false
            },
          },
        ],
      },
    ],
    [
      {
        label: 'Feedback',
        icon: 'i-lucide-message-circle',
        to: 'https://github.com/nuxt-ui-pro/dashboard-vue',
        target: '_blank',
      },
      {
        label: 'Help & Support',
        icon: 'i-lucide-info',
        to: 'https://github.com/nuxt/ui-pro',
        target: '_blank',
      },
    ],
  ])

  const groups = computed(() => [
    {
      id: 'links',
      label: 'Go to',
      items: links.value.flat(),
    },
    {
      id: 'code',
      label: 'Code',
      items: [
        {
          id: 'source',
          label: 'View page source',
          icon: 'simple-icons:github',
          to: `https://github.com/nuxt-ui-pro/dashboard-vue/blob/main/src/pages${url === '/' ? '/index' : url}.vue`,
          target: '_blank',
        },
      ],
    },
  ])

  const cookie = useStorage('cookie-consent', 'pending')
  if (cookie.value !== 'accepted') {
    toast.add({
      title: 'We use first-party cookies to enhance your experience on our website.',
      duration: 0,
      close: false,
      actions: [
        {
          label: 'Accept',
          color: 'neutral',
          variant: 'outline',
          onClick: () => {
            cookie.value = 'accepted'
          },
        },
        {
          label: 'Opt out',
          color: 'neutral',
          variant: 'ghost',
        },
      ],
    })
  }
</script>

<template>
  <Suspense>
    <UApp>
      <UDashboardGroup unit="rem" storage="local">
        <UDashboardSidebar
          id="default"
          v-model:open="open"
          collapsible
          resizable
          class="bg-elevated/25"
          :ui="{ footer: 'lg:border-t lg:border-default' }"
        >
          <template #header="{ collapsed }">
            <TeamsMenu :collapsed="collapsed" />
          </template>

          <template #default="{ collapsed }">
            <UDashboardSearchButton :collapsed="collapsed" class="bg-transparent ring-default" />

            <UNavigationMenu :collapsed="collapsed" :items="links[0]" orientation="vertical" tooltip popover />

            <UNavigationMenu :collapsed="collapsed" :items="links[1]" orientation="vertical" tooltip class="mt-auto" />
          </template>

          <template #footer="{ collapsed }">
            <UserMenu :collapsed="collapsed" />
          </template>
        </UDashboardSidebar>

        <UDashboardSearch :groups="groups" />

        <slot />

        <NotificationsSlideover />
      </UDashboardGroup>
    </UApp>
  </Suspense>
</template>
