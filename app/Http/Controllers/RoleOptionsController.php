<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Role\RoleOptionsRequest;
use App\Queries\RoleOptionsQuery;

final class RoleOptionsController
{
    /**
     * Get available permissions options for role creation/editing.
     *
     * @return array<int, string>
     */
    public function __invoke(RoleOptionsRequest $request, RoleOptionsQuery $roleOptionsQuery): array
    {
        return $roleOptionsQuery->handle();
    }
}
