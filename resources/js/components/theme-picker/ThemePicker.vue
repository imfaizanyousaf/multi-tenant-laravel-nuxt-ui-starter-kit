<script setup lang="ts">
  import ThemePickerButton from './ThemePickerButton.vue'
  import { ref } from 'vue'

  defineProps<{
    popover?: boolean
  }>()

  const open = ref(false)

  const {
    neutralColors,
    neutral,
    primaryColors,
    primary,
    blackAsPrimary,
    setBlackAsPrimary,
    radiuses,
    radius,
    resetTheme,
    isCustomized,
  } = useTheme()

  const radiusLabels: Record<number, string> = {
    0: '0 (None)',
    0.125: '0.125 (XS)',
    0.25: '0.25 (SM)',
    0.375: '0.375 (MD)',
    0.5: '0.5 (LG)',
  }
</script>

<template>
  <!-- Popover Mode -->
  <UPopover v-if="popover" v-model:open="open" :ui="{ content: 'w-80 px-6 py-4 flex flex-col gap-5 overflow-y-auto max-h-[calc(100vh-5rem)]' }">
    <template #default>
      <UButton
        icon="i-lucide-swatch-book"
        color="neutral"
        :variant="open ? 'soft' : 'ghost'"
        square
        aria-label="Color picker"
        :ui="{ leadingIcon: 'text-primary' }"
      />
    </template>

    <template #content>
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold text-highlighted">Theme Settings</span>
        <UButton
          v-if="isCustomized"
          size="xs"
          color="neutral"
          variant="ghost"
          icon="i-lucide-rotate-ccw"
          label="Reset"
          @click="resetTheme"
        />
      </div>

      <fieldset>
        <legend class="mb-2 text-[11px] font-semibold text-muted select-none">Primary Color</legend>
        <div class="grid grid-cols-3 gap-1.5">
          <ThemePickerButton label="Black" :selected="blackAsPrimary" @click="setBlackAsPrimary(true)">
            <template #leading>
              <span class="inline-block size-2.5 rounded-full bg-black dark:bg-white shrink-0" />
            </template>
          </ThemePickerButton>

          <ThemePickerButton
            v-for="color in primaryColors"
            :key="color"
            :label="color"
            :chip="color"
            :selected="!blackAsPrimary && primary === color"
            @click="primary = color"
          />
        </div>
      </fieldset>

      <fieldset>
        <legend class="mb-2 text-[11px] font-semibold text-muted select-none">Neutral Color</legend>
        <div class="grid grid-cols-3 gap-1.5">
          <ThemePickerButton
            v-for="color in neutralColors"
            :key="color"
            :label="color"
            :chip="color"
            :selected="neutral === color"
            @click="neutral = color"
          />
        </div>
      </fieldset>

      <fieldset>
        <legend class="mb-2 text-[11px] font-semibold text-muted select-none">Border Radius</legend>
        <div class="grid grid-cols-5 gap-1">
          <ThemePickerButton
            v-for="r in radiuses"
            :key="r"
            :label="String(r)"
            class="justify-center px-0 text-center"
            :selected="radius === r"
            @click="radius = r"
          />
        </div>
      </fieldset>
    </template>
  </UPopover>

  <!-- Inline / Full Page Mode -->
  <div v-else class="contents">
    <!-- Primary Color -->
    <UFormField
      label="Primary Color"
      description="Select an accent color for buttons, active states, and highlights."
      class="flex flex-col gap-3"
    >
      <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 mt-2">
        <ThemePickerButton label="Black" :selected="blackAsPrimary" class="w-full justify-center" @click="setBlackAsPrimary(true)">
          <template #leading>
            <span class="inline-block size-2.5 rounded-full bg-black dark:bg-white shrink-0" />
          </template>
        </ThemePickerButton>

        <ThemePickerButton
          v-for="color in primaryColors"
          :key="color"
          :label="color"
          :chip="color"
          :selected="!blackAsPrimary && primary === color"
          class="w-full justify-center"
          @click="primary = color"
        />
      </div>
    </UFormField>

    <USeparator />

    <!-- Neutral Color -->
    <UFormField
      label="Neutral Color"
      description="Select a neutral palette for backgrounds, borders, and muted text."
      class="flex flex-col gap-3"
    >
      <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 mt-2">
        <ThemePickerButton
          v-for="color in neutralColors"
          :key="color"
          :label="color"
          :chip="color"
          :selected="neutral === color"
          class="w-full justify-center"
          @click="neutral = color"
        />
      </div>
    </UFormField>

    <USeparator />

    <!-- Border Radius -->
    <UFormField
      label="Border Radius"
      description="Select corner roundedness for UI elements across the application."
      class="flex flex-col gap-3"
    >
      <div class="grid grid-cols-2 gap-2 sm:grid-cols-5 mt-2">
        <ThemePickerButton
          v-for="r in radiuses"
          :key="r"
          :label="radiusLabels[r] || String(r)"
          class="w-full justify-center text-center"
          :selected="radius === r"
          @click="radius = r"
        />
      </div>
    </UFormField>
  </div>
</template>
