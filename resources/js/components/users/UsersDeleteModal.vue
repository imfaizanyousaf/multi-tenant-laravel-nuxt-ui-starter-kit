<script setup lang="ts">
  import { ref } from 'vue'

  defineProps<{
    count?: number
  }>()

  const open = ref(false)

  const toast = useToast()
  async function onDelete() {
    toast.add({ title: 'Success', description: 'Selected users have been deleted.', color: 'success' })
    open.value = false
  }
</script>

<template>
  <UModal
    v-model:open="open"
    :title="`Delete ${count === 1 ? 'user' : 'users'}`"
    :description="`Are you sure you want to delete ${count === 1 ? 'this user' : 'these users'}? This action cannot be undone.`"
  >
    <slot />

    <template #footer>
      <UButton label="Cancel" color="neutral" variant="subtle" @click="open = false" />
      <UButton label="Delete" color="error" variant="solid" @click="onDelete" />
    </template>
  </UModal>
</template>
