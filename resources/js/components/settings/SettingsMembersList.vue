<script setup lang="ts">
  import { useConfirm } from '@/composables/useConfirm'
  import type { DropdownMenuItem } from '@nuxt/ui'
  import type { Member } from '../../types'

  defineProps<{
    members: Member[]
  }>()

  const { confirm } = useConfirm()

  function getItems(member: Member): DropdownMenuItem[] {
    return [
      {
        label: 'Edit member',
        onSelect: () => console.log('Edit member', member.username),
      },
      {
        label: 'Remove member',
        color: 'error' as const,
        onSelect: () => {
          confirm({
            title: 'Remove Member',
            description: `Are you sure you want to remove ${member.name}?`,
            confirmText: 'Remove',
            color: 'error',
            onConfirm: async () => true,
          })
        },
      },
    ]
  }
</script>

<template>
  <ul role="list" class="divide-y divide-default">
    <li v-for="member in members" :key="member.username" class="flex items-center justify-between gap-3 px-4 py-3 sm:px-6">
      <div class="flex min-w-0 items-center gap-3">
        <UAvatar v-bind="member.avatar" size="md" />

        <div class="min-w-0 text-sm">
          <p class="truncate font-medium text-highlighted">
            {{ member.name }}
          </p>
          <p class="truncate text-muted">
            {{ member.username }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <USelect :model-value="member.role" :items="['member', 'owner']" color="neutral" :ui="{ value: 'capitalize', item: 'capitalize' }" />

        <UDropdownMenu :items="getItems(member)" :content="{ align: 'end' }">
          <UButton icon="i-lucide-ellipsis-vertical" color="neutral" variant="ghost" />
        </UDropdownMenu>
      </div>
    </li>
  </ul>
</template>
