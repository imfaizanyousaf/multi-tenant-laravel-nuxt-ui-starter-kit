<?php

declare(strict_types=1);

namespace App\Http\Requests\Role;

use App\Models\Role;
use Closure;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        $role = $this->route('role');

        if (! $role instanceof Role || ! $this->user()?->can('update', $role)) {
            return Response::deny();
        }

        return Response::allow();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Role|null $role */
        $role = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                Rule::unique('roles', 'name')->ignore($role?->id),
                function (string $attribute, mixed $value, Closure $fail) use ($role): void {
                    if ($role?->name === Role::SUPER_ADMIN && $value !== Role::SUPER_ADMIN) {
                        $fail('The '.Role::SUPER_ADMIN.' role cannot be renamed.');
                    }
                },
            ],
            'permissions' => [
                'nullable',
                'array',
            ],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name'),
            ],
        ];
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
                $role = $this->route('role');

                if ($role instanceof Role && $role->name === Role::SUPER_ADMIN) {
                    $validator->errors()->add('role', 'The '.Role::SUPER_ADMIN.' role cannot be updated.');
                }
            },
        ];
    }
}
