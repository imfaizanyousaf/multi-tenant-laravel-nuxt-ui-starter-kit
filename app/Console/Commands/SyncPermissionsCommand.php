<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

use function app;

#[Signature('permissions:sync')]
#[Description('Sync application model permissions into database and assign to Super Admin role')]
class SyncPermissionsCommand extends Command
{
    /**
     * Map of models/modules to their permissions.
     *
     * @var array<string, list<string>>
     */
    protected array $permissionsMap = [
        'User' => [
            'view users',
            'create users',
            'update users',
            'delete users',
        ],
        'Role' => [
            'view roles',
            'create roles',
            'update roles',
            'delete roles',
        ],
        'Tenant' => [
            'view tenants',
            'create tenants',
            'delete tenants',
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionsToSync = $this->permissionsMap;

        if (Tenant::checkCurrent()) {
            unset($permissionsToSync['Tenant']);

            // Clean up any accidentally synced landlord-only permissions from tenant DBs
            Permission::whereIn('name', $this->permissionsMap['Tenant'])->delete();
        }

        foreach ($permissionsToSync as $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::findOrCreate($permissionName, 'web');
            }
        }

        $superAdminRole = Role::findOrCreate(Role::SUPER_ADMIN, 'web');
        $superAdminRole->syncPermissions(Permission::all());

        $this->info('Permissions synced successfully and assigned to Super Admin role.');

        return self::SUCCESS;
    }
}
