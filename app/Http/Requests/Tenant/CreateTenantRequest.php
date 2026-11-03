<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class CreateTenantRequest extends FormRequest
{
    public function authorize(): Response
    {
        return $this->user()?->can('create', Tenant::class)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:50', 'unique:tenants,name'],
        ];
    }
}
