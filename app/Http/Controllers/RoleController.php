<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Role\CreateRole;
use App\Actions\Role\DeleteBulkRoles;
use App\Actions\Role\DeleteRole;
use App\Actions\Role\GetPaginatedRoles;
use App\Actions\Role\UpdateRole;
use App\Http\Requests\Role\DestroyBulkRoleRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\DatatableResourceCollection;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

use function abort_unless;
use function back;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', Role::class);

        return Inertia::render('Roles');
    }

    /**
     * Get available permissions options for role creation/editing.
     *
     * @return array<int, string>
     */
    public function options(Request $request): array
    {
        abort_unless($request->user()?->canAny(['create roles', 'update roles']), 403);

        return Permission::query()->pluck('name')->values()->all();
    }

    /**
     * Get paginated roles for server-side datatable.
     */
    public function table(Request $request, GetPaginatedRoles $getPaginatedRoles): DatatableResourceCollection
    {
        Gate::authorize('viewAny', Role::class);

        $roles = $getPaginatedRoles->handle($request);

        return new DatatableResourceCollection($roles, RoleResource::class);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRoleRequest $request, CreateRole $createRole): RedirectResponse
    {
        $createRole->handle($request->validated());

        return back();
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role, UpdateRole $updateRole): RedirectResponse
    {
        if ($role->name === Role::SUPER_ADMIN) {
            return back()->withErrors(['role' => 'The Super Admin role cannot be updated.']);
        }

        $updateRole->handle($role, $request->validated());

        return back();
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role, DeleteRole $deleteRole): RedirectResponse
    {
        Gate::authorize('delete', $role);

        if ($role->name === Role::SUPER_ADMIN) {
            return back()->withErrors(['role' => 'The Super Admin role cannot be deleted.']);
        }

        $deleteRole->handle($role);

        return back();
    }

    /**
     * Remove multiple specified roles from storage.
     */
    public function destroyBulk(DestroyBulkRoleRequest $request, DeleteBulkRoles $deleteBulkRoles): RedirectResponse
    {
        /** @var array<int, int|string> $ids */
        $ids = $request->validated('ids');

        $deleteBulkRoles->handle($ids);

        return back();
    }
}
