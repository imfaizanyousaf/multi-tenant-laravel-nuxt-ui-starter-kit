<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;

final class ListTenantTableRequest extends FormRequest
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
