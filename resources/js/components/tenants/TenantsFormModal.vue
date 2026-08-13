<script lang="ts" setup>
  import { store } from '@/actions/App/Http/Controllers/TenantController'
  import DialogForm from '@/components/app/DialogForm.vue'
  import { useForm, usePage } from '@inertiajs/vue3'
  import { createReusableTemplate } from '@vueuse/core'
  import { computed, ref, watch } from 'vue'

  const [DefineFormTemplate, ReuseFormTemplate] = createReusableTemplate()

  const props = defineProps<{
    open?: boolean
  }>()

  const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'success'): void
  }>()

  const isOpen = ref(false)

  watch(
    () => props.open,
    (val) => {
      if (val !== undefined) {
        isOpen.value = val
      }
    },
    { immediate: true },
  )

  function setOpen(val: boolean) {
    isOpen.value = val
    emit('update:open', val)
  }

  const form = useForm({
    name: '',
  })

  function slugify(text: string) {
    return text
      .toString()
      .toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^\w\-]+/g, '')
      .replace(/\-\-+/g, '-')
      .replace(/^-+/, '')
      .replace(/-+$/, '')
  }

  const generatedDomain = computed(() => {
    if (!form.name) return ''
    return `${slugify(form.name)}.${window.location.hostname}`
  })

  const page = usePage<any>()
  const prefix = computed(() => page.props.tenant?.prefix ?? '')
  const suffix = computed(() => page.props.tenant?.suffix ?? '')

  const generatedDatabase = computed(() => {
    if (!form.name) return ''
    return `${prefix.value}${slugify(form.name)}${suffix.value}`
  })

  function resetForm() {
    form.reset()
    form.clearErrors()
  }

  function openModal() {
    resetForm()
    setOpen(true)
  }

  const toast = useToast()

  function onSubmit() {
    form.submit('post', store.url(), {
      preserveScroll: true,
      onSuccess: () => {
        toast.add({
          title: 'Tenant Creation Started',
          description: 'The status will be updated once the process is complete.',
          color: 'info',
        })
        resetForm()
        setOpen(false)
        emit('success')
      },
    })
  }

  defineExpose({ openModal })
</script>

<template>
  <DefineFormTemplate>
    <form id="tenant-form" class="space-y-4" @submit.prevent="onSubmit">
      <UFormField :error="form.errors.name" label="Name" name="name" required>
        <UInput v-model="form.name" class="w-full" placeholder="Tenant Name" required />
      </UFormField>

      <UFormField label="Domain (Auto-generated)" name="domain">
        <UInput :model-value="generatedDomain" disabled class="w-full" placeholder="Will be auto-generated" />
      </UFormField>

      <UFormField label="Database (Auto-generated)" name="database">
        <UInput :model-value="generatedDatabase" disabled class="w-full" placeholder="Will be auto-generated" />
      </UFormField>
    </form>
  </DefineFormTemplate>

  <DialogForm
    :open="isOpen"
    description="Add a new tenant"
    title="New Tenant"
    @update:open="
      (val) => {
        setOpen(val)
        if (!val) resetForm()
      }
    "
  >
    <template #trigger>
      <slot name="trigger">
        <UButton icon="i-lucide-plus" label="New tenant" @click="openModal()" />
      </slot>
    </template>
    <ReuseFormTemplate />
    <template #footer>
      <div class="mt-8 mb-4 flex w-full flex-col gap-4 md:mt-0 md:mb-0 md:flex-row-reverse md:gap-2">
        <UButton
          :disabled="!form.isDirty"
          form="tenant-form"
          label="Create"
          :loading="form.processing"
          color="primary"
          type="submit"
          class="justify-center"
        />
        <UButton color="neutral" class="justify-center" label="Cancel" variant="subtle" @click="setOpen(false)" />
      </div>
    </template>
  </DialogForm>
</template>
