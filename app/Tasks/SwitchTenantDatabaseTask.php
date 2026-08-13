<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

use function config;

class SwitchTenantDatabaseTask implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
        if (! $tenant instanceof Tenant) {
            return;
        }

        $tenantConnectionName = config('multitenancy.tenant_database_connection_name');

        config(["database.connections.{$tenantConnectionName}.database" => $tenant->database]);
        config(['database.default' => $tenantConnectionName]);

        DB::purge($tenantConnectionName);
    }

    public function forgetCurrent(): void
    {
        $tenantConnectionName = config('multitenancy.tenant_database_connection_name');
        $landlordConnectionName = config('multitenancy.landlord_database_connection_name');

        config(["database.connections.{$tenantConnectionName}.database" => null]);
        config(['database.default' => $landlordConnectionName]);

        DB::purge($tenantConnectionName);
    }
}
