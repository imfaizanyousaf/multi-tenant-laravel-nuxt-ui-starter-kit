<template>
  <div class="flex w-fit items-center gap-3">
    <UAvatar :alt="row.name" :src="row.avatar" class="shrink-0" />
    <div class="min-w-0 flex-1 space-y-1">
      <div class="flex items-center gap-1">
        <p class="truncate font-medium text-highlighted">{{ row.name }}</p>
      </div>
      <div class="flex items-center gap-1 truncate text-xs text-muted">
        <span class="truncate">{{ row.email }}</span>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
  import type { User } from '@/types'

  defineProps<{
    row: User
  }>()
</script>
