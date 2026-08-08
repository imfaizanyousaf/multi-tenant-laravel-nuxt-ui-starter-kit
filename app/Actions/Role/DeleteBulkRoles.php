<?php

declare(strict_types=1);

namespace App\Actions\Role;

use App\Models\Role;

class DeleteBulkRoles
{
    /**
     * Delete multiple roles by IDs.
     *
     * @param  array<int, int|string>  $ids
     */
    public function handle(array $ids): int
    {
        return Role::query()
            ->whereIn('id', $ids)
            ->where('name', '!=', Role::SUPER_ADMIN)
            ->delete();
    }
}
