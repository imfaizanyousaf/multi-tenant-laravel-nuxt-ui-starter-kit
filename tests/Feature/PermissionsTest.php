<?php

use App\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;

test('roles and permissions can be assigned to user', function (): void {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);
    $permission = Permission::create(['name' => 'manage users']);

    $role->givePermissionTo($permission);
    $user->assignRole($role);

    expect($user->hasRole('admin'))->toBeTrue()
        ->and($user->hasPermissionTo('manage users'))->toBeTrue();
});
