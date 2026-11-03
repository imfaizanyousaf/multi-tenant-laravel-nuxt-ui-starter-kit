<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use function is_string;

final class UserTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return $this->user()?->can('viewAny', User::class)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
            'sort' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'email_verified' => ['nullable', Rule::in(['verified', 'unverified'])],
        ];
    }

    public function search(): string
    {
        return (string) $this->input('search', '');
    }

    public function sort(): string
    {
        return (string) $this->input('sort', '');
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 10);
    }

    public function emailVerified(): ?string
    {
        $value = $this->input('email_verified');

        return is_string($value) ? $value : null;
    }
}
