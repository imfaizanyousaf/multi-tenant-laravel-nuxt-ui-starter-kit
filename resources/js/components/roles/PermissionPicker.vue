<script lang="ts" setup>
  import type { TabsItem } from '@nuxt/ui'
  import { computed } from 'vue'

  const props = defineProps<{
    modelValue: string[]
    permissions?: string[]
    error?: string
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: string[]): void
  }>()

  function togglePermission(perm: string) {
    const current = [...props.modelValue]
    const idx = current.indexOf(perm)
    if (idx > -1) {
      current.splice(idx, 1)
    } else {
      current.push(perm)
    }
    emit('update:modelValue', current)
  }

  function toggleGroup(groupPerms: string[]) {
    const allSelected = groupPerms.every((p) => props.modelValue.includes(p))
    let current = [...props.modelValue]
    if (allSelected) {
      current = current.filter((p) => !groupPerms.includes(p))
    } else {
      const toAdd = groupPerms.filter((p) => !current.includes(p))
      current.push(...toAdd)
    }
    emit('update:modelValue', current)
  }

  const groupedPermissions = computed(() => {
    const map: Record<string, string[]> = {}
    const list = props.permissions ?? []

    for (const perm of list) {
      const parts = perm.split(' ')
      const group = parts.length > 1 ? parts[parts.length - 1] : 'general'
      const capitalized = group.charAt(0).toUpperCase() + group.slice(1)
      if (!map[capitalized]) {
        map[capitalized] = []
      }
      map[capitalized].push(perm)
    }

    return map
  })

  const tabItems = computed<TabsItem[]>(() => {
    return Object.entries(groupedPermissions.value).map(([group, perms]) => ({
      label: group,
      value: group.toLowerCase(),
      perms,
    }))
  })
</script>

<template>
  <div class="space-y-3">
    <UFormField :error="error" label="Permissions">
      <UTabs
        v-if="tabItems.length > 0"
        :default-value="tabItems[0]?.value"
        orientation="vertical"
        variant="link"
        :items="tabItems"
        class="mt-4 w-full items-start"
      >
        <template #content="{ item }">
          <div class="space-y-3 pl-4">
            <div class="flex items-center justify-between pb-1">
              <span class="mt-3 text-sm font-semibold capitalize">{{ item.label }} Permissions</span>
              <UButton
                variant="subtle"
                size="sm"
                color="neutral"
                type="button"
                :label="item.perms.every((p: string) => modelValue.includes(p)) ? 'Deselect All' : 'Select All'"
                @click="toggleGroup(item.perms)"
              />
            </div>
            <USeparator />

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
              <UCheckbox
                v-for="perm in item.perms"
                :id="'perm-' + perm.replace(/\s+/g, '-')"
                :key="perm"
                :model-value="modelValue.includes(perm)"
                :label="perm"
                class="p-1 capitalize"
                @update:model-value="togglePermission(perm)"
              />
            </div>
          </div>
        </template>
      </UTabs>
    </UFormField>
  </div>
</template>
