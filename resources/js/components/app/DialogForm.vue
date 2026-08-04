<script lang="ts" setup>
  import { useMediaQuery } from '@vueuse/core'

  interface Props {
    open: boolean
    title?: string
    description?: string
    ui?: Record<string, any>
    dismissible?: boolean
  }

  const props = withDefaults(defineProps<Props>(), {
    title: '',
    description: '',
    ui: () => ({ content: 'sm:max-w-2xl' }),
    dismissible: true,
  })

  const emit = defineEmits<{
    'update:open': [value: boolean]
  }>()

  const isDesktop = useMediaQuery('(min-width: 768px)')

  const localOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
  })
</script>

<template>
  <UModal v-if="isDesktop" v-model:open="localOpen" :description="description" :title="title" :ui="ui" :dismissible="dismissible">
    <slot name="trigger" />
    <template #body>
      <slot />
    </template>
    <template #footer>
      <slot name="footer" />
    </template>
  </UModal>

  <UDrawer v-else v-model:open="localOpen" :description="description" :title="title" :dismissible="dismissible">
    <slot name="trigger" />
    <template #body>
      <slot />
    </template>
    <template #footer>
      <slot name="footer" />
    </template>
  </UDrawer>
</template>
