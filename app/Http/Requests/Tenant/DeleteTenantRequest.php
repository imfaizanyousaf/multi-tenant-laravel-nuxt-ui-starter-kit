<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class DeleteTenantRequest extends FormRequest
{
    public function authorize(): Response
    {
        $tenant = $this->route('tenant');

        return $tenant instanceof Tenant && $this->user()?->can('delete', $tenant)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'confirmation' => ['required', 'string', 'in:'.$this->route('tenant')->database],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmation.in' => 'The confirmation text does not match the tenant database name.',
        ];
    }
}
