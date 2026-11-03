<?php

declare(strict_types=1);

namespace App\Http\Requests\Role;

use App\Models\Role;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class RoleTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return $this->user()?->can('viewAny', Role::class)
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
}
