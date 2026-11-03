<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\User\UserOptionsRequest;
use App\Queries\UserRoleOptionsQuery;

final class UserOptionsController
{
    /**
     * Get available roles options for user creation/editing.
     *
     * @return array<int, string>
     */
    public function __invoke(UserOptionsRequest $request, UserRoleOptionsQuery $userRoleOptionsQuery): array
    {
        return $userRoleOptionsQuery->handle();
    }
}
