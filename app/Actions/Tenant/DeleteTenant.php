<?php

declare(strict_types=1);

namespace App\Actions\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

use function config;

class DeleteTenant
{
    public function handle(Tenant $tenant): void
    {
        $database = $tenant->database;
        $tenant->delete();

        DB::connection(config('multitenancy.landlord_database_connection_name'))
            ->statement("DROP DATABASE IF EXISTS `{$database}`;");
    }
}
