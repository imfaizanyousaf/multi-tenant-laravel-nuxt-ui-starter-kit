<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Tenant\ListTenantTableRequest;
use App\Http\Resources\DatatableResourceCollection;
use App\Http\Resources\TenantResource;
use App\Queries\TenantListQuery;

final class TenantTableController
{
    public function __invoke(ListTenantTableRequest $request, TenantListQuery $tenantListQuery): DatatableResourceCollection
    {
        $tenants = $tenantListQuery->handle(
            $request->search(),
            $request->sort(),
            $request->perPage(),
        );

        return new DatatableResourceCollection($tenants, TenantResource::class);
    }
}
