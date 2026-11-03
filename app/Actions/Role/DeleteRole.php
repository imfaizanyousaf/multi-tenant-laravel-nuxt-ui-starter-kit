<?php

declare(strict_types=1);

namespace App\Actions\Role;

use App\Models\Role;

final readonly class DeleteRole
{
    /**
     * Delete the specified role.
     */
    public function handle(Role $role): bool
    {
        return (bool) $role->delete();
    }
}
