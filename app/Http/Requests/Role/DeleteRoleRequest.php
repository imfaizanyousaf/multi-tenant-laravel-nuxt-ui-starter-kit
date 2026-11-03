<?php

declare(strict_types=1);

namespace App\Http\Requests\Role;

use App\Models\Role;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class DeleteRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        $role = $this->route('role');

        if (! $role instanceof Role || ! $this->user()?->can('delete', $role)) {
            return Response::deny();
        }

        return Response::allow();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array{}
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get the "after" validation callables for the request.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Role $role */
                $role = $this->route('role');

                if ($role->name === Role::SUPER_ADMIN) {
                    $validator->errors()->add('role', 'The '.Role::SUPER_ADMIN.' role cannot be deleted.');
                }
            },
        ];
    }
}
