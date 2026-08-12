<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TenantStatus;
use App\Jobs\CreateTenantDatabase;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

use function config;
use function dispatch_sync;
use function now;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Tenant::checkCurrent()) {
            $this->runTenantSeeder();
        } else {
            $this->runLandlordSeeder();
        }
    }

    private function runTenantSeeder(): void
    {
        $this->call(PermissionSeeder::class);

        $admin = User::query()->firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Tenant Admin',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $admin->assignRole(Role::SUPER_ADMIN);
    }

    private function runLandlordSeeder(): void
    {
        $this->call(PermissionSeeder::class);

        $admin = User::query()->firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Landlord Admin',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $admin->assignRole(Role::SUPER_ADMIN);

        $prefix = config('multitenancy.tenant_database_prefix');
        $suffix = config('multitenancy.tenant_database_suffix');

        $tenant = Tenant::query()->updateOrCreate(['domain' => 'foo.'.config('app.domain', 'localhost')], ['name' => 'foo', 'database' => $prefix.'foo'.$suffix, 'status' => TenantStatus::CREATING]);

        dispatch_sync(new CreateTenantDatabase($tenant));
    }
}
