<?php

declare(strict_types=1);

namespace App\Queries;

use Spatie\Permission\Models\Permission;

final readonly class RoleOptionsQuery
{
    /**
     * Get the permission names available for role creation and editing.
     *
     * @return array<int, string>
     */
    public function handle(): array
    {
        return Permission::query()->pluck('name')->values()->all();
    }
}
