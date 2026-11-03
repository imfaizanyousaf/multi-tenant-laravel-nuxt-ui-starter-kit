<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class DeleteUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        $user = $this->route('user');

        if (! $user instanceof User || ! $this->user()?->can('delete', $user)) {
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
                /** @var User $user */
                $user = $this->route('user');

                if ($user->id === $this->user()?->id) {
                    $validator->errors()->add('user', 'You cannot delete your own account.');
                }

                if ($user->hasRole(Role::SUPER_ADMIN)) {
                    $validator->errors()->add('user', 'The '.Role::SUPER_ADMIN.' account cannot be deleted.');
                }
            },
        ];
    }
}
