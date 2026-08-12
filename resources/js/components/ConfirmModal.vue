<script setup lang="ts">
  import { useMediaQuery } from '@vueuse/core'
  import { ref } from 'vue'

  interface Props {
    title: string
    description: string
    cancelText: string
    confirmText: string
    variant: 'link' | 'solid' | 'outline' | 'soft' | 'ghost' | 'subtle'
    color: 'neutral' | 'primary' | 'secondary' | 'success' | 'info' | 'warning' | 'error'
    verificationText?: string
    onConfirm: (verificationInput?: string) => Promise<boolean>
    onCancel: () => Promise<void>
  }

  const props = defineProps<Props>()

  const emit = defineEmits<{
    close: [boolean]
  }>()

  const isLoading = ref(false)
  const activeButton = ref<'cancel' | 'confirm' | null>(null)
  const isDesktop = useMediaQuery('(min-width: 768px)')
  const verificationInput = ref('')

  const handleConfirm = async () => {
    if (isLoading.value) return

    isLoading.value = true
    activeButton.value = 'confirm'

    try {
      const result = await props.onConfirm(verificationInput.value)
      if (result) {
        emit('close', true)
      } else {
        isLoading.value = false
        activeButton.value = null
      }
    } catch (error) {
      console.error('Confirmation action failed:', error)
      isLoading.value = false
      activeButton.value = null
    }
  }

  const handleCancel = async () => {
    if (isLoading.value) return

    isLoading.value = true
    activeButton.value = 'cancel'

    try {
      await props.onCancel()
      emit('close', false)
    } catch (error) {
      console.error('Cancel action failed:', error)
      isLoading.value = false
      activeButton.value = null
    }
  }
</script>

<template>
  <UModal v-if="isDesktop" :title="title" :description="description" :close="{ onClick: () => emit('close', false) }">
    <template #body v-if="verificationText">
      <p class="mb-3 text-sm">
        Please type <strong class="text-foreground select-all">{{ verificationText }}</strong> to confirm.
      </p>
      <UInput v-model="verificationInput" :disabled="isLoading" class="w-full" placeholder="Type database name" />
    </template>

    <template #footer>
      <div class="flex w-full justify-end gap-3">
        <UButton :disabled="isLoading" :loading="isLoading && activeButton === 'cancel'" color="neutral" variant="ghost" @click="handleCancel">
          {{ cancelText }}
        </UButton>

        <UButton
          :disabled="isLoading || (!!verificationText && verificationInput !== verificationText)"
          :loading="isLoading && activeButton === 'confirm'"
          :color="color"
          :variant="variant"
          @click="handleConfirm"
        >
          {{ confirmText }}
        </UButton>
      </div>
    </template>
  </UModal>

  <UDrawer v-else :title="title" :description="description" :close="{ onClick: () => emit('close', false) }">
    <template #body v-if="verificationText">
      <p class="mb-3 text-sm">
        Please type <strong class="text-foreground select-all">{{ verificationText }}</strong> to confirm.
      </p>
      <UInput v-model="verificationInput" :disabled="isLoading" class="w-full" placeholder="Type database name" />
    </template>

    <template #footer>
      <div class="flex w-full justify-end gap-3">
        <UButton :disabled="isLoading" :loading="isLoading && activeButton === 'cancel'" color="neutral" variant="ghost" @click="handleCancel">
          {{ cancelText }}
        </UButton>

        <UButton
          :disabled="isLoading || (!!verificationText && verificationInput !== verificationText)"
          :loading="isLoading && activeButton === 'confirm'"
          :color="color"
          :variant="variant"
          @click="handleConfirm"
        >
          {{ confirmText }}
        </UButton>
      </div>
    </template>
  </UDrawer>
</template>
