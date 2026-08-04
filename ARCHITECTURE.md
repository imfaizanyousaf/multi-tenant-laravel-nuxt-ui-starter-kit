# Architecture Guidelines: CRUD & Datatable Standard

This document establishes the architecture and design guidelines for domain logic, backend APIs, and frontend CRUD components in this Laravel application. All AI coding assistants (LLMs) and developers MUST follow these patterns when building or refactoring features for any model.

---

## 🏛️ Core Architectural Principles

### 1. Backend Architecture

#### A. Form Requests (`App\Http\Requests\<Domain>\...`)

- **Single Responsibility**: ALL HTTP request validation and authorization MUST be encapsulated within dedicated `FormRequest` classes under `app/Http/Requests/<Domain>/`.
- **No Inline Validation**: Controllers MUST NOT run inline `$request->validate()` calls.
- **Strict Typing**: Form Requests MUST include `declare(strict_types=1);`, explicit return type declarations (`rules(): array`, `authorize(): bool`), and parameter type hints.

#### B. Domain Actions (`App\Actions\<Domain>\...`)

- **Single Business Mutation or Query**: Business operations (Create, Update, Delete, Bulk Operations, Paginated Queries) MUST be implemented in dedicated Action classes under `app/Actions/<Domain>/`.
- **Single Public Method**: Every Action class MUST expose a single public `handle(...)` method.
- **Static Analysis Compliance**: Array parameter annotations MUST use PHPDoc `@param array<string, mixed> $data` so PHPStan passes without errors.

#### C. Resources & Datatable API (`App\Http\Resources\...`)

- **Eloquent API Resource**: Create a dedicated `<Entity>Resource` extending `JsonResource` for consistent object serialization.
- **Datatable Pagination Response**: Use `DatatableResourceCollection` to wrap paginated queries:
  ```php
  return new DatatableResourceCollection($paginatedData, <Entity>Resource::class);
  ```
  This guarantees standard client-side pagination metadata (`{ data: [...], meta: { current_page, last_page, per_page, total, from, to } }`).

#### D. Thin Controllers (`App\Http\Controllers\<Entity>Controller.php`)

- **HTTP Concerns Only**: Controllers delegate validation to Form Requests and execution to injected Action classes.
- **Datatable Endpoint**: Include a `table(Request $request, GetPaginated<Entities> $getPaginated)` action mapped to route `GET /<entities>/table` (`name('<entities>.table')`).

---

### 2. Frontend Architecture

#### A. Domain Directory Structure (`resources/js/components/<entities>/`)

Organize all domain-specific UI components within `resources/js/components/<entities>/`:

- **`data.ts`**: Contains column definitions (`columns`), base row actions (`baseRowActions`), base bulk actions (`baseBulkActions`), and datatable configuration (`config = createDataTableConfig()`).
- **`<Entity>FormModal.vue`**: Unified modal for both **Create** and **Edit** operations, leveraging `DialogForm.vue` and `createReusableTemplate` from `@vueuse/core`.

#### B. Confirmation System (`useConfirm()`)

- Do NOT build separate delete modal components for models. Use the programmatic `useConfirm()` composable for single and bulk deletion confirmations.

#### C. Page Component Structure (`resources/js/pages/...`)

- **Single-Page Models**: If a model has only a single page view (e.g. datatable listing), place it as `resources/js/pages/<Entities>.vue`.
- **Multi-Page Models**: If a model requires multiple frontend pages (e.g., listing, specific user view/profile, detailed analytics), create a dedicated folder under `resources/js/pages/<entities>/` containing:
  - `resources/js/pages/<entities>/Index.vue`: Main listing page with `<DataTable>`.
  - `resources/js/pages/<entities>/View.vue`: Detailed view page for a specific resource instance.
  - `resources/js/pages/<entities>/Edit.vue`: Full-page editor (if complex full-page editing is needed beyond modals).

---

## 📁 Directory Structure Standard

```
app/
├── Actions/
│   └── <Domain>/
│       ├── Create<Entity>.php
│       ├── Update<Entity>.php
│       ├── Delete<Entity>.php
│       ├── DeleteBulk<Entities>.php
│       └── GetPaginated<Entities>.php
├── Http/
│   ├── Controllers/
│   │   └── <Entity>Controller.php
│   └── Requests/
│       └── <Domain>/
│           ├── Store<Entity>Request.php
│           ├── Update<Entity>Request.php
│           └── DestroyBulk<Entity>Request.php
│   └── Resources/
│       ├── DatatableResourceCollection.php
│       └── <Entity>Resource.php

resources/js/
├── components/
│   ├── app/
│   │   ├── DialogForm.vue       # Reusable Dialog Form wrapper
│   │   └── DataTable/          # Reusable server-side DataTable system
│   └── <entities>/               # Domain UI components directory
│       ├── <Entity>FormModal.vue # Unified Create/Edit Modal using DialogForm
│       └── data.ts              # Columns, base actions & createDataTableConfig()
└── pages/
    ├── <Entities>.vue           # Single-page container rendering <DataTable>
    └── <entities>/              # (Or folder for multi-page models)
        ├── Index.vue            # Datatable listing view
        └── Show.vue             # Specific entity detailed view page
```

---

## 💻 Backend Code Examples

### 1. Form Request Example (`app/Http/Requests/User/StoreUserRequest.php`)

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string'],
        ];
    }
}
```

### 2. Query Action Example (`app/Actions/User/GetPaginatedUsers.php`)

```php
<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class GetPaginatedUsers
{
    public function handle(?Request $request = null): LengthAwarePaginator
    {
        $request ??= request();
        $perPage = (int) ($request->input('per_page') ?? 10);
        $search = $request->input('search');
        $sort = $request->input('sort');

        $query = User::query();

        if (! empty($search) && is_string($search)) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        if (! empty($sort) && is_string($sort)) {
            [$column, $direction] = explode('.', $sort, 2);
            $query->orderBy($column, strtolower($direction) === 'desc' ? 'desc' : 'asc');
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }
}
```

### 3. Controller Example (`app/Http/Controllers/UserController.php`)

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\User\CreateUser;
use App\Actions\User\GetPaginatedUsers;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\DatatableResourceCollection;
use App\Http\Resources\UserResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Users');
    }

    public function table(Request $request, GetPaginatedUsers $getPaginatedUsers): DatatableResourceCollection
    {
        $users = $getPaginatedUsers->handle($request);

        return new DatatableResourceCollection($users, UserResource::class);
    }

    public function store(StoreUserRequest $request, CreateUser $createUser): RedirectResponse
    {
        $createUser->handle($request->validated());

        return back();
    }
}
```

---

## 💻 Frontend Code Examples

### 1. Data Module Example (`resources/js/components/users/data.ts`)

```typescript
import { createDataTableConfig } from '@/components/app/DataTable/config'
import type { BaseActionDefinition, BaseBulkActionDefinition } from '@/composables/useDataTableActions'
import type { User } from '@/types'
import type { DataTableColumn, DataTableConfig } from '@/types/datatable'

export const columns: DataTableColumn<User>[] = [
  { accessorKey: 'name', header: 'Name', sortable: true },
  { accessorKey: 'email', header: 'Email', sortable: true },
]

export const baseRowActions: BaseActionDefinition[] = [
  { id: 'edit', label: 'Edit user details', icon: 'i-lucide-pencil' },
  { id: 'delete', label: 'Delete user', icon: 'i-lucide-trash', color: 'error' },
]

export const baseBulkActions: BaseBulkActionDefinition[] = [
  { id: 'bulk-delete', label: 'Delete selected', icon: 'i-lucide-trash', color: 'error' },
]

export const config: DataTableConfig = createDataTableConfig()
```

### 2. Unified Form Modal Example (`resources/js/components/users/UsersFormModal.vue`)

```vue
<script lang="ts" setup>
  import { store, update } from '@/actions/App/Http/Controllers/UserController'
  import DialogForm from '@/components/app/DialogForm.vue'
  import type { User } from '@/types'
  import { useForm } from '@inertiajs/vue3'
  import { createReusableTemplate } from '@vueuse/core'
  import { computed, ref, watch } from 'vue'

  const [DefineFormTemplate, ReuseFormTemplate] = createReusableTemplate()

  const props = defineProps<{ user?: User | null; open?: boolean }>()
  const emit = defineEmits<{ (e: 'update:open', val: boolean): void; (e: 'success'): void }>()

  const isOpen = ref(false)
  watch(
    () => props.open,
    (val) => {
      if (val !== undefined) isOpen.value = val
    },
    { immediate: true },
  )

  function setOpen(val: boolean) {
    isOpen.value = val
    emit('update:open', val)
  }

  const form = useForm({ id: undefined as string | undefined, name: '', email: '' })

  function openModal(user?: User | null) {
    form.reset()
    if (user?.id) {
      form.id = user.id
      form.name = user.name
      form.email = user.email
    } else {
      form.id = undefined
      form.name = ''
      form.email = ''
    }
    setOpen(true)
  }

  function onSubmit() {
    const isEdit = !!form.id
    const url = isEdit ? update.url(form.id!) : store.url()
    form.submit(isEdit ? 'put' : 'post', url, {
      onSuccess: () => {
        setOpen(false)
        emit('success')
      },
    })
  }

  defineExpose({ openModal })
</script>

<template>
  <DefineFormTemplate>
    <form id="user-form" class="space-y-4" @submit.prevent="onSubmit">
      <UFormField :error="form.errors.name" label="Name" required>
        <UInput v-model="form.name" class="w-full" required />
      </UFormField>
    </form>
  </DefineFormTemplate>

  <DialogForm :open="isOpen" :title="form.id ? 'Edit User' : 'New User'" @update:open="setOpen">
    <template #trigger>
      <slot name="trigger"><UButton icon="i-lucide-plus" label="New user" @click="openModal()" /></slot>
    </template>
    <ReuseFormTemplate />
  </DialogForm>
</template>
```

---

## 🧪 Verification & Quality Control Commands

Every change MUST pass the following checks before finalizing:

1. **Wayfinder Action Generation** (whenever routes change):
   ```bash
   php artisan wayfinder:generate --no-interaction
   ```
2. **Pest Test Suite & 100.0% Coverage**:
   ```bash
   XDEBUG_MODE=coverage php -d memory_limit=512M ./vendor/bin/pest --parallel --coverage --exactly=100.0
   ```
3. **PHPStan Static Analysis**:
   ```bash
   vendor/bin/phpstan analyse --memory-limit=512M
   ```
4. **Code Formatting & Linting**:
   ```bash
   vendor/bin/pint --dirty --format agent
   npm run format && npm run lint
   ```
