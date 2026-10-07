<script lang="ts" setup>
  import { options, store, update } from '@/actions/App/Http/Controllers/UserController'
  import DialogForm from '@/components/app/DialogForm.vue'
  import { useAuth } from '@/composables/useAuth'
  import type { User } from '@/types'
  import { useForm, useHttp } from '@inertiajs/vue3'
  import { createReusableTemplate } from '@vueuse/core'
  import { computed, onMounted, ref, watch } from 'vue'

  const [DefineFormTemplate, ReuseFormTemplate] = createReusableTemplate()
  const { hasPermission } = useAuth()

  const props = defineProps<{
    user?: User | null
    roles?: string[]
    open?: boolean
  }>()

  const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'success'): void
  }>()

  const isOpen = ref(false)
  const rolesOptions = ref<string[]>(props.roles ?? [])
  const rolesHttp = useHttp()

  function fetchRoles() {
    if (rolesOptions.value.length === 0) {
      rolesHttp.get(options.url(), {
        onSuccess: (data: any) => {
          rolesOptions.value = (data as string[]) ?? []
        },
      })
    }
  }

  onMounted(() => {
    fetchRoles()
  })

  watch(
    () => props.open,
    (val) => {
      if (val !== undefined) {
        isOpen.value = val
        if (val) {
          fetchRoles()
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
    id: undefined as string | undefined,
    name: '',
    email: '',
    password: '',
    roles: [] as string[],
  })

  function resetForm() {
    form.reset()
    form.clearErrors()
  }

  function openModal(user?: User | null) {
    fetchRoles()
    resetForm()
    if (user?.id) {
      form.id = user.id
      form.name = user.name ?? ''
      form.email = user.email ?? ''
      form.password = ''
      form.roles = [...(user.roles ?? [])]
    } else {
      form.id = undefined
      form.name = ''
      form.email = ''
      form.password = ''
      form.roles = []
    }
    setOpen(true)
  }

  watch(
    () => props.user,
    (newUser) => {
      if (newUser?.id) {
        form.id = newUser.id
        form.name = newUser.name ?? ''
        form.email = newUser.email ?? ''
        form.password = ''
        form.roles = [...(newUser.roles ?? [])]
      }
    },
    { immediate: true },
  )

  const toast = useToast()

  const selectedRole = computed({
    get: () => form.roles[0] ?? undefined,
    set: (val: string | undefined) => {
      form.roles = val ? [val] : []
    },
  })

  function onSubmit() {
    const isEdit = !!form.id
    const url = isEdit ? update.url(form.id!) : store.url()
    const method = isEdit ? 'put' : 'post'

    form.submit(method, url, {
      preserveScroll: true,
      onSuccess: () => {
        toast.add({
          title: 'Success',
          description: isEdit ? 'User updated successfully' : 'User created successfully',
          color: 'success',
        })
        resetForm()
        setOpen(false)
        emit('success')
      },
    })
  }

  const title = computed(() => (form.id ? 'Edit User' : 'New User'))
  const description = computed(() => (form.id ? 'Update the user information' : 'Add a new user to the database'))

  defineExpose({ openModal })
</script>

<template>
  <DefineFormTemplate>
    <form id="user-form" class="space-y-4" @submit.prevent="onSubmit">
      <UFormField :error="form.errors.name" label="Name" name="name" required>
        <UInput v-model="form.name" class="w-full" placeholder="John Doe" required />
      </UFormField>

      <UFormField :error="form.errors.email" label="Email" name="email" required>
        <UInput v-model="form.email" class="w-full" type="email" autocomplete="off" placeholder="john.doe@example.com" required />
      </UFormField>

      <UFormField :error="form.errors.password" label="Password" name="password" :required="!form.id">
        <UInput
          v-model="form.password"
          type="password"
          class="w-full"
          :placeholder="form.id ? 'Leave empty to keep current password' : 'Enter password'"
          :required="!form.id"
        />
      </UFormField>

      <UFormField :error="form.errors.roles || form.errors['roles.0']" label="Role" name="roles">
        <USelect v-model="selectedRole" :items="rolesOptions" class="w-full" placeholder="Select a role" />
      </UFormField>
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
        <UButton v-if="hasPermission('create users')" icon="i-lucide-plus" label="New user" @click="openModal()" />
      </slot>
    </template>
    <ReuseFormTemplate />
    <template #footer>
      <div class="mt-8 mb-4 flex w-full flex-col gap-4 md:mt-0 md:mb-0 md:flex-row-reverse md:gap-2">
        <UButton
          :disabled="!form.isDirty"
          form="user-form"
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
