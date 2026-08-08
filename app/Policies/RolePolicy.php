<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    /**
     * Determine whether the user can view any roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view roles');
    }

    /**
     * Determine whether the user can view the role.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->can('view roles');
    }

    /**
     * Determine whether the user can create roles.
     */
    public function create(User $user): bool
    {
        return $user->can('create roles');
    }

    /**
     * Determine whether the user can update the role.
     */
    public function update(User $user, Role $role): bool
    {
        if ($role->name === Role::SUPER_ADMIN) {
            return false;
        }

        return $user->can('update roles');
    }

    /**
     * Determine whether the user can delete the role.
     */
    public function delete(User $user, Role $role): bool
    {
        if ($role->name === Role::SUPER_ADMIN) {
            return false;
        }

        return $user->can('delete roles');
    }

    /**
     * Determine whether the user can delete multiple roles.
     */
    public function deleteBulk(User $user): bool
    {
        return $user->can('delete roles');
    }
}
