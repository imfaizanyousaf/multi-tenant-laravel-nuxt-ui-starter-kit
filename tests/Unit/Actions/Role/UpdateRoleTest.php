<?php

declare(strict_types=1);

use App\Actions\Role\UpdateRole;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('update role updates name and syncs permissions', function (): void {
    $role = Role::create(['name' => 'Editor']);
    $role->givePermissionTo('view users');

    (new UpdateRole)->handle($role, [
        'name' => 'Content Manager',
        'permissions' => ['create users', 'update users'],
    ]);

    expect($role->fresh()->name)->toBe('Content Manager')
        ->and($role->fresh()->hasPermissionTo('create users'))->toBeTrue()
        ->and($role->fresh()->hasPermissionTo('view users'))->toBeFalse();
});

test('update role keeps the super admin role name', function (): void {
    $role = Role::findByName(Role::SUPER_ADMIN);

    (new UpdateRole)->handle($role, [
        'name' => 'Renamed',
        'permissions' => ['view users'],
    ]);

    expect($role->fresh()->name)->toBe(Role::SUPER_ADMIN)
        ->and($role->fresh()->hasPermissionTo('view users'))->toBeTrue();
});
