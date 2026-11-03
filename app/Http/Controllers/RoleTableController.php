<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Role\RoleTableRequest;
use App\Http\Resources\DatatableResourceCollection;
use App\Http\Resources\RoleResource;
use App\Queries\RoleListQuery;

final class RoleTableController
{
    /**
     * Get paginated roles for server-side datatable.
     */
    public function __invoke(RoleTableRequest $request, RoleListQuery $roleListQuery): DatatableResourceCollection
    {
        $roles = $roleListQuery->handle(
            $request->search(),
            $request->sort(),
            $request->perPage(),
        );

        return new DatatableResourceCollection($roles, RoleResource::class);
    }
}
