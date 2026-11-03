<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Role;

final readonly class UserRoleOptionsQuery
{
    /**
     * Get the role names that can be assigned to users.
     *
     * @return array<int, string>
     */
    public function handle(): array
    {
        return Role::query()
            ->where('name', '!=', Role::SUPER_ADMIN)
            ->pluck('name')
            ->values()
            ->all();
    }
}
