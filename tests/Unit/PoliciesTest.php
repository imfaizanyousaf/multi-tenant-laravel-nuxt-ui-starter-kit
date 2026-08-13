<?php

declare(strict_types=1);

use App\Enums\TenantStatus;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\RolePolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('role policy enforces super admin guards and permissions', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::SUPER_ADMIN);

    $editorRole = Role::create(['name' => 'Editor']);
    $editorRole->givePermissionTo(['view roles', 'create roles', 'update roles', 'delete roles']);
    $editor = User::factory()->create();
    $editor->assignRole($editorRole);

    $plainUser = User::factory()->create();

    $normalRole = Role::create(['name' => 'Writer']);
    $superAdminRole = Role::findByName(Role::SUPER_ADMIN);

    $policy = new RolePolicy;

    expect($policy->viewAny($superAdmin))->toBeTrue()
        ->and($policy->view($superAdmin, $normalRole))->toBeTrue()
        ->and($policy->create($superAdmin))->toBeTrue()
        ->and($policy->update($superAdmin, $normalRole))->toBeTrue()
        ->and($policy->update($superAdmin, $superAdminRole))->toBeFalse()
        ->and($policy->delete($superAdmin, $normalRole))->toBeTrue()
        ->and($policy->delete($superAdmin, $superAdminRole))->toBeFalse()
        ->and($policy->deleteBulk($superAdmin))->toBeTrue();

    expect($policy->viewAny($editor))->toBeTrue()
        ->and($policy->view($editor, $normalRole))->toBeTrue()
        ->and($policy->create($editor))->toBeTrue()
        ->and($policy->update($editor, $normalRole))->toBeTrue()
        ->and($policy->update($editor, $superAdminRole))->toBeFalse()
        ->and($policy->delete($editor, $normalRole))->toBeTrue()
        ->and($policy->delete($editor, $superAdminRole))->toBeFalse()
        ->and($policy->deleteBulk($editor))->toBeTrue();

    expect($policy->viewAny($plainUser))->toBeFalse()
        ->and($policy->view($plainUser, $normalRole))->toBeFalse()
        ->and($policy->create($plainUser))->toBeFalse()
        ->and($policy->update($plainUser, $normalRole))->toBeFalse()
        ->and($policy->delete($plainUser, $normalRole))->toBeFalse()
        ->and($policy->deleteBulk($plainUser))->toBeFalse();
});

test('user policy enforces super admin guards and permissions', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::SUPER_ADMIN);

    $managerRole = Role::create(['name' => 'User Manager']);
    $managerRole->givePermissionTo(['view users', 'create users', 'update users', 'delete users']);
    $manager = User::factory()->create();
    $manager->assignRole($managerRole);

    $plainUser = User::factory()->create();

    $target = User::factory()->create();

    $policy = new UserPolicy;

    expect($policy->viewAny($superAdmin))->toBeTrue()
        ->and($policy->view($superAdmin, $target))->toBeTrue()
        ->and($policy->create($superAdmin))->toBeTrue()
        ->and($policy->update($superAdmin, $target))->toBeTrue()
        ->and($policy->update($superAdmin, $superAdmin))->toBeFalse()
        ->and($policy->delete($superAdmin, $target))->toBeTrue()
        ->and($policy->delete($superAdmin, $superAdmin))->toBeFalse()
        ->and($policy->deleteBulk($superAdmin))->toBeTrue()
        ->and($policy->restore($superAdmin, $target))->toBeTrue()
        ->and($policy->forceDelete($superAdmin, $target))->toBeTrue();

    expect($policy->viewAny($manager))->toBeTrue()
        ->and($policy->view($manager, $target))->toBeTrue()
        ->and($policy->create($manager))->toBeTrue()
        ->and($policy->update($manager, $target))->toBeTrue()
        ->and($policy->update($manager, $superAdmin))->toBeFalse()
        ->and($policy->delete($manager, $target))->toBeTrue()
        ->and($policy->delete($manager, $superAdmin))->toBeFalse()
        ->and($policy->deleteBulk($manager))->toBeTrue()
        ->and($policy->restore($manager, $target))->toBeTrue()
        ->and($policy->forceDelete($manager, $target))->toBeTrue();

    expect($policy->viewAny($plainUser))->toBeFalse()
        ->and($policy->view($plainUser, $target))->toBeFalse()
        ->and($policy->create($plainUser))->toBeFalse()
        ->and($policy->update($plainUser, $target))->toBeFalse()
        ->and($policy->delete($plainUser, $target))->toBeFalse()
        ->and($policy->deleteBulk($plainUser))->toBeFalse()
        ->and($policy->restore($plainUser, $target))->toBeFalse()
        ->and($policy->forceDelete($plainUser, $target))->toBeFalse();
});

test('tenant policy enforces permissions', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::SUPER_ADMIN);

    $managerRole = Role::create(['name' => 'Tenant Manager']);
    $managerRole->givePermissionTo(['view tenants', 'create tenants', 'delete tenants']);
    $manager = User::factory()->create();
    $manager->assignRole($managerRole);

    $plainUser = User::factory()->create();

    $tenant = Tenant::query()->create([
        'name' => 'Acme Inc',
        'domain' => 'acme.'.config('app.domain'),
        'database' => config('multitenancy.tenant_database_prefix').'acme',
        'status' => TenantStatus::ACTIVE,
    ]);

    $policy = new TenantPolicy;

    expect($policy->viewAny($superAdmin))->toBeTrue()
        ->and($policy->view($superAdmin, $tenant))->toBeTrue()
        ->and($policy->create($superAdmin))->toBeTrue()
        ->and($policy->delete($superAdmin, $tenant))->toBeTrue()
        ->and($policy->update($superAdmin, $tenant))->toBeFalse()
        ->and($policy->restore($superAdmin, $tenant))->toBeFalse()
        ->and($policy->forceDelete($superAdmin, $tenant))->toBeFalse();

    expect($policy->viewAny($manager))->toBeTrue()
        ->and($policy->view($manager, $tenant))->toBeTrue()
        ->and($policy->create($manager))->toBeTrue()
        ->and($policy->delete($manager, $tenant))->toBeTrue();

    expect($policy->viewAny($plainUser))->toBeFalse()
        ->and($policy->view($plainUser, $tenant))->toBeFalse()
        ->and($policy->create($plainUser))->toBeFalse()
        ->and($policy->delete($plainUser, $tenant))->toBeFalse()
        ->and($policy->update($plainUser, $tenant))->toBeFalse()
        ->and($policy->restore($plainUser, $tenant))->toBeFalse()
        ->and($policy->forceDelete($plainUser, $tenant))->toBeFalse();
});
