<script lang="ts" setup>
  import { options, store, update } from '@/actions/App/Http/Controllers/RoleController'
  import { ROLE_SUPER_ADMIN } from '@/constants/roles'
  import AlertError from '@/components/AlertError.vue'
  import DialogForm from '@/components/app/DialogForm.vue'
  import PermissionPicker from '@/components/roles/PermissionPicker.vue'
  import { useAuth } from '@/composables/useAuth'
  import type { Role } from '@/types'
  import { useForm, useHttp } from '@inertiajs/vue3'
  import { createReusableTemplate } from '@vueuse/core'
  import { computed, onMounted, ref, watch } from 'vue'

  const [DefineFormTemplate, ReuseFormTemplate] = createReusableTemplate()
  const { hasPermission } = useAuth()

  const props = defineProps<{
    role?: Role | null
    permissions?: string[]
    open?: boolean
  }>()

  const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'success'): void
  }>()

  const isOpen = ref(false)
  const permissionsOptions = ref<string[]>(props.permissions ?? [])
  const permissionsHttp = useHttp()

  function fetchPermissions() {
    if (permissionsOptions.value.length === 0) {
      permissionsHttp.get(options.url(), {
        onSuccess: (data: any) => {
          permissionsOptions.value = (data as string[]) ?? []
        },
      })
    }
  }

  onMounted(() => {
    fetchPermissions()
  })

  watch(
    () => props.open,
    (val) => {
      if (val !== undefined) {
        isOpen.value = val
        if (val) {
          fetchPermissions()
        }
      }
    },
    { immediate: true },
  )

  function setOpen(val: boolean) {
    isOpen.value = val
    emit('update:open', val)
  }

  const form = useForm({
    id: undefined as number | undefined,
    name: '',
    permissions: [] as string[],
  })

  function resetForm() {
    form.reset()
    form.clearErrors()
  }

  function openModal(role?: Role | null) {
    fetchPermissions()
    resetForm()
    if (role?.id) {
      form.id = role.id
      form.name = role.name ?? ''
      form.permissions = [...(role.permissions ?? [])]
    } else {
      form.id = undefined
      form.name = ''
      form.permissions = []
    }
    setOpen(true)
  }

  watch(
    () => props.role,
    (newRole) => {
      if (newRole?.id) {
        form.id = newRole.id
        form.name = newRole.name ?? ''
        form.permissions = [...(newRole.permissions ?? [])]
      }
    },
    { immediate: true },
  )

  const toast = useToast()

  function onSubmit() {
    const isEdit = !!form.id
    const url = isEdit ? update.url(form.id!) : store.url()
    const method = isEdit ? 'put' : 'post'

    form.submit(method, url, {
      preserveScroll: true,
      onSuccess: () => {
        toast.add({
          title: 'Success',
          description: isEdit ? 'Role updated successfully' : 'Role created successfully',
          color: 'success',
        })
        resetForm()
        setOpen(false)
        emit('success')
      },
    })
  }

  const title = computed(() => (form.id ? 'Edit Role' : 'New Role'))
  const description = computed(() => (form.id ? 'Update role details and permissions' : 'Create a new role and assign permissions'))
  const isSuperAdmin = computed(() => form.name === ROLE_SUPER_ADMIN)

  defineExpose({ openModal })
</script>

<template>
  <DefineFormTemplate>
    <form id="role-form" class="space-y-4" @submit.prevent="onSubmit">
      <AlertError v-if="form.errors.role" :errors="[form.errors.role]" title="Unable to save role" />

      <UFormField :error="form.errors.name" label="Role Name" name="name" required>
        <UInput v-model="form.name" class="w-full" placeholder="e.g. Manager" :disabled="isSuperAdmin" required />
      </UFormField>

      <PermissionPicker v-model="form.permissions" :permissions="permissionsOptions" :error="form.errors.permissions" />
    </form>
  </DefineFormTemplate>

  <DialogForm
    :open="isOpen"
    :description="description"
    :title="title"
    @update:open="
      (val) => {
        setOpen(val)
        if (!val) resetForm()
      }
    "
  >
    <template #trigger>
      <slot name="trigger">
        <UButton v-if="hasPermission('create roles')" icon="i-lucide-plus" label="New role" @click="openModal()" />
      </slot>
    </template>
    <ReuseFormTemplate />
    <template #footer>
      <div class="mt-8 mb-4 flex w-full flex-col gap-4 md:mt-0 md:mb-0 md:flex-row-reverse md:gap-2">
        <UButton
          :disabled="!form.isDirty"
          form="role-form"
          :label="form.id ? 'Update' : 'Create'"
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
