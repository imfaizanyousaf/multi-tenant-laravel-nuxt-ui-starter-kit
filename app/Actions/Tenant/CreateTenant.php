<?php

declare(strict_types=1);

namespace App\Actions\Tenant;

use App\Enums\TenantStatus;
use App\Jobs\CreateTenantDatabase;
use App\Models\Tenant;
use Illuminate\Support\Str;

use function config;
use function dispatch;

class CreateTenant
{
    public function handle(string $name): Tenant
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug === '' ? 'tenant' : $baseSlug;

        $suffix = 0;
        $slug = $baseSlug;

        do {
            $slug = $suffix === 0 ? $baseSlug : $baseSlug.'-'.$suffix;
            $domain = $slug.'.'.config('app.domain', 'localhost');
            $database = config('multitenancy.tenant_database_prefix').$slug.config('multitenancy.tenant_database_suffix');
            $suffix++;
        } while (Tenant::query()->where('domain', $domain)->orWhere('database', $database)->exists());

        $tenant = Tenant::query()->create([
            'name' => $name,
            'domain' => $domain,
            'database' => $database,
            'status' => TenantStatus::CREATING,
        ]);

        dispatch(new CreateTenantDatabase($tenant));

        return $tenant;
    }
}
