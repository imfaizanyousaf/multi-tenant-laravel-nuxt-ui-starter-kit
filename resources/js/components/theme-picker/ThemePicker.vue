<script setup lang="ts">
  import ThemePickerButton from './ThemePickerButton.vue'

  const {
    colorMode,
    themeModes,
    neutralColors,
    neutral,
    primaryColors,
    primary,
    blackAsPrimary,
    setBlackAsPrimary,
    themePreset,
    radiusOptions,
    radius,
    themePresetOptions,
    themePresetIcon,
    themePresetIconClass,
  } = useTheme()
</script>

<template>
  <!-- Inline / Full Page Mode -->
  <div class="contents">
    <!-- Theme Mode -->
    <UFormField
      label="Theme Mode"
      description="Choose between light, dark, or system mode."
      orientation="horizontal"
      :ui="{ root: 'flex-col md:flex-row md:items-start', container: 'mt-1 md:mt-0' }"
    >
      <UTabs v-model="colorMode" size="sm" variant="pill" :content="false" :items="themeModes" class="mt-2 w-full">
        <template #leading="{ item }">
          <UTooltip :text="item.label" :content="{ side: 'top' }" :delay-duration="0">
            <span class="inline-flex">
              <UIcon :name="item.icon" class="size-4" />
            </span>
          </UTooltip>
        </template>
      </UTabs>
    </UFormField>

    <USeparator />

    <!-- Theme Presets -->
    <UFormField
      label="Theme"
      description="Select a preset or customize colors manually."
      orientation="horizontal"
      :ui="{ root: 'flex-col md:flex-row md:items-start', container: 'mt-1 md:mt-0' }"
    >
      <USelect
        v-model="themePreset"
        :items="themePresetOptions"
        value-key="value"
        :icon="themePresetIcon"
        :ui="{ leadingIcon: themePresetIconClass }"
        class="mt-2 w-full min-w-fit md:mt-0 md:w-32"
      />
    </UFormField>

    <div v-if="themePreset === 'custom'" class="space-y-3">
      <USeparator class="mb-2" />
      <!-- Primary Color -->
      <UFormField label="Primary Color" description="Select an accent color for buttons, active states, and highlights." class="flex flex-col gap-3">
        <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
          <ThemePickerButton label="Black" :selected="blackAsPrimary" class="w-full justify-center" @click="setBlackAsPrimary(true)">
            <template #leading>
              <span class="inline-block size-2.5 shrink-0 rounded-full bg-black dark:bg-white" />
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
      <UFormField label="Neutral Color" description="Select a neutral palette for backgrounds, borders, and muted text." class="flex flex-col gap-3">
        <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
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
    </div>

    <USeparator />

    <!-- Border Radius -->
    <UFormField
      label="Border Radius"
      description="Select corner roundedness for UI elements."
      orientation="horizontal"
      :ui="{ root: 'flex-col md:flex-row md:items-start', container: 'mt-1 md:mt-0' }"
    >
      <USelect v-model="radius" :items="radiusOptions" value-key="value" class="mt-2 w-full md:w-32" />
    </UFormField>
  </div>
</template>
