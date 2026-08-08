<?php

declare(strict_types=1);

namespace App\Actions\Role;

use App\Models\Role;
use InvalidArgumentException;

use function throw_if;

class DeleteRole
{
    /**
     * Delete the specified role.
     */
    public function handle(Role $role): bool
    {
        throw_if($role->name === Role::SUPER_ADMIN, InvalidArgumentException::class, 'The Super Admin role cannot be deleted.');

        return (bool) $role->delete();
    }
}
