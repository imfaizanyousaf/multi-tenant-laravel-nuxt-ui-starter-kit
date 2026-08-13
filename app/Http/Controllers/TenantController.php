<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Tenant\CreateTenant;
use App\Actions\Tenant\DeleteTenant;
use App\Actions\Tenant\GetPaginatedTenants;
use App\Http\Requests\DestroyTenantRequest;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Resources\DatatableResourceCollection;
use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

use function back;
use function config;

class TenantController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Tenant::class);

        return Inertia::render('Tenants', [
            'tenant' => [
                'prefix' => config('multitenancy.tenant_database_prefix'),
                'suffix' => config('multitenancy.tenant_database_suffix'),
            ],
        ]);
    }

    public function table(Request $request, GetPaginatedTenants $getPaginatedTenants): DatatableResourceCollection
    {
        Gate::authorize('viewAny', Tenant::class);

        $tenants = $getPaginatedTenants->handle($request);

        return new DatatableResourceCollection($tenants, TenantResource::class);
    }

    public function store(StoreTenantRequest $request, CreateTenant $action): RedirectResponse
    {
        $action->handle($request->validated('name'));

        return back();
    }

    public function destroy(Tenant $tenant, DestroyTenantRequest $request, DeleteTenant $action): RedirectResponse
    {
        $action->handle($tenant);

        return back();
    }
}
