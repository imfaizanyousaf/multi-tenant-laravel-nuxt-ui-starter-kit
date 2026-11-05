<script setup lang="ts">
  import ThemePicker from '@/components/theme-picker/ThemePicker.vue'
  import Layout from '@/layouts/Default.vue'
  import SettingsLayout from '@/layouts/SettingsLayout.vue'
  import { Head } from '@inertiajs/vue3'

  defineOptions({ layout: Layout })

  const { isCustomized, resetTheme } = useTheme()
</script>

<template>
  <SettingsLayout>
    <Head title="Appearance Settings" />

    <div>
      <UPageCard title="Appearance" description="Customize how the app looks on your device." variant="naked" orientation="horizontal" class="mb-4">
        <UButton v-if="isCustomized" label="Reset" color="neutral" variant="outline" class="w-fit lg:ms-auto" @click="resetTheme" />
      </UPageCard>

      <UPageCard variant="subtle">
        <ThemePicker />
      </UPageCard>
    </div>
  </SettingsLayout>
</template>
