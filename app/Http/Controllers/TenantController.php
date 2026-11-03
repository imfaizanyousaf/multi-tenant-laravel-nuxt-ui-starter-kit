<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Tenant\CreateTenant;
use App\Actions\Tenant\DeleteTenant;
use App\Http\Requests\Tenant\CreateTenantRequest;
use App\Http\Requests\Tenant\DeleteTenantRequest;
use App\Http\Requests\Tenant\ListTenantRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use function back;
use function config;

final class TenantController
{
    public function index(ListTenantRequest $request): Response
    {
        return Inertia::render('Tenants', [
            'tenant' => [
                'prefix' => config('multitenancy.tenant_database_prefix'),
                'suffix' => config('multitenancy.tenant_database_suffix'),
            ],
        ]);
    }

    public function store(CreateTenantRequest $request, CreateTenant $action): RedirectResponse
    {
        $action->handle($request->validated('name'));

        return back();
    }

    public function destroy(Tenant $tenant, DeleteTenantRequest $request, DeleteTenant $action): RedirectResponse
    {
        $action->handle($tenant);

        return back();
    }
}
