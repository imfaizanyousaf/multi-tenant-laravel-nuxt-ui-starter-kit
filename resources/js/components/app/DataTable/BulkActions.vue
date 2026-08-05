<script generic="T = any" lang="ts" setup>
  import { useDataTableContext } from '@/composables/useDataTableContext'
  import type { DataTableBulkAction } from '@/types/datatable'
  import { computed, resolveComponent } from 'vue'

  interface Props {
    bulkActions?: DataTableBulkAction<T>[]
  }

  const props = withDefaults(defineProps<Props>(), {
    bulkActions: () => [],
  })

  const context = useDataTableContext<T>()

  const UButton = resolveComponent('UButton')
  const UTooltip = resolveComponent('UTooltip')
  const USeparator = resolveComponent('USeparator')

  const visibleBulkActions = computed(() => {
    if (!props.bulkActions) return []

    return props.bulkActions.filter((action) => {
      if (typeof action.visible === 'function') {
        return action.visible(context.selectedRows.value)
      }
      return action.visible !== false
    })
  })

  const shouldShow = computed(() => context.selectedRows.value.length > 0)
</script>

<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 translate-y-4"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-4"
  >
    <div v-if="shouldShow">
      <div class="flex h-fit items-center rounded-lg border border-accented bg-default px-4 py-2 shadow-lg">
        <span class="text-md whitespace-nowrap"> {{ context.selectedRows.value.length }} Selected </span>
        <USeparator orientation="vertical" class="mr-1 ml-2 data-[orientation=vertical]:h-4" />

        <template v-if="visibleBulkActions.length > 0">
          <UTooltip v-for="action in visibleBulkActions" :key="action.id || action.label" :text="action.label" :content="{ sideOffset: 10 }">
            <UButton
              :color="action.color"
              variant="ghost"
              :label="action.label"
              :icon="action.icon"
              :disabled="typeof action.disabled === 'function' ? action.disabled(context.selectedRows.value) : action.disabled"
              size="sm"
              class="mr-1 text-sm"
              @click="action.onClick(context.selectedRows.value)"
            />
          </UTooltip>
        </template>

        <UTooltip text="Clear selection" :kbds="['Esc']" :content="{ sideOffset: 10 }">
          <UButton variant="ghost" color="neutral" icon="i-lucide-x" size="md" class="-mr-2" @click="context.clearSelection" />
        </UTooltip>
      </div>
    </div>
  </Transition>
</template>
