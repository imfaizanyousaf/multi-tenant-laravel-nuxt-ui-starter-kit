<script setup lang="ts">
  import ThemePicker from '@/components/theme-picker/ThemePicker.vue'
  import ThemePickerButton from '@/components/theme-picker/ThemePickerButton.vue'
  import Layout from '@/layouts/Default.vue'
  import SettingsLayout from '@/layouts/SettingsLayout.vue'
  import { Head } from '@inertiajs/vue3'
  import { useColorMode } from '@vueuse/core'

  defineOptions({ layout: Layout })

  const colorMode = useColorMode({ emitAuto: true })
  const { isCustomized, resetTheme } = useTheme()

  const items = [
    {
      label: 'Light',
      value: 'light',
      icon: 'i-lucide-sun',
    },
    {
      label: 'Dark',
      value: 'dark',
      icon: 'i-lucide-moon',
    },
    {
      label: 'System',
      value: 'auto',
      icon: 'i-lucide-monitor',
    },
  ]
</script>

<template>
  <SettingsLayout>
    <Head title="Appearance settings" />

    <div>
      <UPageCard title="Appearance" description="Customize how the app looks on your device." variant="naked" orientation="horizontal" class="mb-4">
        <UButton
          v-if="isCustomized"
          label="Reset theme"
          color="neutral"
          variant="outline"
          icon="i-lucide-rotate-ccw"
          class="w-fit lg:ms-auto"
          @click="resetTheme"
        />
      </UPageCard>

      <UPageCard variant="subtle">
        <!-- Theme Mode -->
        <UFormField
          label="Theme Mode"
          description="Choose between light, dark, or system mode."
          class="flex flex-col gap-3"
        >
          <div class="grid grid-cols-3 gap-2 sm:grid-cols-3 mt-2">
            <ThemePickerButton
              v-for="item in items"
              :key="item.value"
              :label="item.label"
              :icon="item.icon"
              :selected="colorMode === item.value"
              class="w-full justify-center"
              @click="colorMode = item.value"
            />
          </div>
        </UFormField>

        <USeparator />

        <!-- Colors & Radius -->
        <ThemePicker />
      </UPageCard>
    </div>
  </SettingsLayout>
</template>
