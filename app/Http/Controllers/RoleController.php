<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Role\CreateRole;
use App\Actions\Role\DeleteRole;
use App\Actions\Role\UpdateRole;
use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\DeleteRoleRequest;
use App\Http\Requests\Role\ListRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use function back;

final class RoleController
{
    /**
     * Display a listing of roles.
     */
    public function index(ListRoleRequest $request): Response
    {
        return Inertia::render('Roles');
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(CreateRoleRequest $request, CreateRole $createRole): RedirectResponse
    {
        $createRole->handle($request->validated());

        return back();
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role, UpdateRole $updateRole): RedirectResponse
    {
        $updateRole->handle($role, $request->validated());

        return back();
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(DeleteRoleRequest $request, Role $role, DeleteRole $deleteRole): RedirectResponse
    {
        $deleteRole->handle($role);

        return back();
    }
}
