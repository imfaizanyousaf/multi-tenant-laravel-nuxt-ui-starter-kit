<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $targetUser = $this->route('user');

        if (! $targetUser instanceof User || $targetUser->hasRole(Role::SUPER_ADMIN)) {
            return false;
        }

        return (bool) $this->user()?->can('update', $targetUser);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $targetUser */
        $targetUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'email' => ['required', 'string', 'email:rfc,dns', 'min:5', 'max:254', Rule::unique(User::class)->ignore($targetUser->id)],
            'password' => ['nullable', 'string', Password::defaults()],
            'roles' => ['nullable', 'array', 'max:1'],
            'roles.*' => [
                'string',
                'exists:roles,name',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value === Role::SUPER_ADMIN) {
                        $fail('The Super Admin role cannot be assigned.');
                    }
                },
            ],
        ];
    }
}
