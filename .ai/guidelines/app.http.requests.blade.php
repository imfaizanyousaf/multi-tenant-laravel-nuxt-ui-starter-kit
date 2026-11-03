# App/Http/Requests guidelines

- Use Laravel policies for authorization. Policies are the single source of truth for role- and permission-based access decisions.
- Form requests must delegate authorization to policies from `authorize()`:
  - Model instances: `$this->user()?->can('update', $model)`.
  - Model classes: `$this->user()?->can('create', Model::class)`.
  - Custom abilities: `$this->user()?->can('deleteBulk', Model::class)`.
- Policies should use the project's roles and permissions through `$user->can(...)` and contain resource-specific rules, such as protecting the Super Admin role.
- Do not duplicate policy logic in form requests, controllers, actions, or models.
- Authorization and validation only ever happen in form requests: policy authorization is invoked in `authorize()`, and validation is defined in `rules()`. Never authorize or validate in controllers, actions, or models. This includes `index` and `show` endpoints.
- Controllers must not call `Gate::authorize()` or perform authorization checks; authorization belongs in the endpoint's dedicated form request.
- Controllers and actions never check anything themselves: no `abort()`, `abort_if()`, `abort_unless()`, `throw_if()`, `throw_unless()`, `Validator::make()`, `$request->validate()`, or `ValidationException`. The only exception is an action checking again under a row lock to close a race, which skips the write or throws its own exception for the caller to catch (see the actions guidelines).
- Create a dedicated form request for each endpoint that checks anything, and never share one between endpoints. If the endpoint has none yet, create one.
- Name it after the controller method it serves, CRUD by design: `index` → `List…Request`, `show` → `Show…Request`, `store` → `Create…Request`, `update` → `Update…Request`, `destroy` → `Delete…Request`. For example `CommitPinController@update` → `UpdateCommitPinRequest`, `CommitPinController@destroy` → `DeleteCommitPinRequest`, `CommitApprovalController@store` → `CreateCommitApprovalRequest`.
- When hiding something should look like it does not exist, return `Response::denyAsNotFound()` from `authorize()` instead of `false`.
- Put input interpretation in form request methods (`$request->tab()`, `$request->parent()`), and complex checks in `App\Rules` validation rules.
- `tests/Unit/ArchTest.php` enforces this.

@boostsnippet('Example form request', 'php')
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): Response
    {
        $user = $this->route('user');

        // Class-level ability, for example when creating a model:
        // return $this->user()?->can('create', User::class)
        //     ? Response::allow()
        //     : Response::deny();

        // Instance-level ability, for example when updating this model:
        if (! $user instanceof User || ! $this->user()?->can('update', $user)) {
            return Response::denyAsNotFound();
        }

        // Custom class-level ability, for example for bulk operations:
        // return $this->user()?->can('deleteBulk', User::class)
        //     ? Response::allow()
        //     : Response::deny();

        return Response::allow();
    }

    /**
     * @return array{}
     */
    public function rules(): array
    {
        return [];
    }
}
@endboostsnippet