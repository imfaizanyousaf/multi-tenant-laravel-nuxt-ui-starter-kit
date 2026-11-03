<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\User\UserTableRequest;
use App\Http\Resources\DatatableResourceCollection;
use App\Http\Resources\UserResource;
use App\Queries\UserListQuery;

final class UserTableController
{
    /**
     * Get paginated users for server-side datatable.
     */
    public function __invoke(UserTableRequest $request, UserListQuery $userListQuery): DatatableResourceCollection
    {
        $users = $userListQuery->handle(
            $request->search(),
            $request->sort(),
            $request->perPage(),
            $request->emailVerified(),
        );

        return new DatatableResourceCollection($users, UserResource::class);
    }
}
