<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

final class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage permissions');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->can('manage permissions');
    }

    public function create(User $user): bool
    {
        return $user->can('manage permissions');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->can('manage permissions');
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->can('manage permissions');
    }
}
