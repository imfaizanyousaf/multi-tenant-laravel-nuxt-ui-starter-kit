<script setup lang="ts">
  defineProps<{
    label: string
    icon?: string
    chip?: string
    selected?: boolean
  }>()

  const slots = defineSlots<{
    leading?: () => any
  }>()
</script>

<template>
  <UButton
    size="sm"
    color="neutral"
    variant="outline"
    :icon="icon"
    :label="label"
    class="rounded-lg text-[12px] capitalize ring-default cursor-pointer justify-start transition-all"
    :class="[selected ? 'bg-elevated ring-2 ring-primary font-medium' : 'hover:bg-elevated/50']"
  >
    <template v-if="chip || !!slots.leading" #leading>
      <slot name="leading">
        <span
          class="inline-block size-2.5 rounded-full shrink-0"
          :class="`bg-(--color-light) dark:bg-(--color-dark)`"
          :style="{
            '--color-light': `var(--color-${chip}-500)`,
            '--color-dark': `var(--color-${chip}-400)`,
          }"
        />
      </slot>
    </template>
  </UButton>
</template>
