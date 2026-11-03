<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;

final class ListTenantRequest extends FormRequest
{
    public function authorize(): Response
    {
        return $this->user()?->can('viewAny', Tenant::class)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * @return array{}
     */
    public function rules(): array
    {
        return [];
    }
}
