<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

test('guests are redirected to login page when accessing roles page', function (): void {
    $response = $this->get(route('roles'));

    $response->assertRedirect(route('login'));
});

test('authenticated users without view roles permission receive forbidden status', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('roles'));

    $response->assertForbidden();
});

test('super admin can view roles page and table endpoint', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::SUPER_ADMIN);

    $response = $this->actingAs($superAdmin)->get(route('roles'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page): Assert => $page
        ->component('Roles')
    );

    $optionsResponse = $this->actingAs($superAdmin)->getJson(route('roles.options'));
    $optionsResponse->assertOk();

    $tableResponse = $this->actingAs($superAdmin)->getJson(route('roles.table', [
        'search' => 'Super',
        'sort' => 'name.asc',
        'per_page' => 10,
    ]));

    $tableResponse->assertOk();
    $tableResponse->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'guard_name', 'permissions', 'created_at', 'updated_at'],
        ],
        'meta' => ['current_page', 'last_page', 'per_page', 'total'],
    ]);
});

test('super admin can create a role with permissions', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::SUPER_ADMIN);

    $response = $this->actingAs($superAdmin)->post(route('roles.store'), [
        'name' => 'Manager',
        'permissions' => ['view users', 'view roles'],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('roles', ['name' => 'Manager']);

    $role = Role::findByName('Manager');
    expect($role->hasPermissionTo('view users'))->toBeTrue()
        ->and($role->hasPermissionTo('view roles'))->toBeTrue();
});

test('super admin can update a role and permissions', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::SUPER_ADMIN);

    $role = Role::create(['name' => 'Editor']);
    $role->givePermissionTo('view users');

    $response = $this->actingAs($superAdmin)->put(route('roles.update', $role), [
        'name' => 'Content Manager',
        'permissions' => ['create users', 'update users'],
    ]);

    $response->assertRedirect();
    expect($role->fresh()->name)->toBe('Content Manager')
        ->and($role->fresh()->hasPermissionTo('create users'))->toBeTrue()
        ->and($role->fresh()->hasPermissionTo('view users'))->toBeFalse();
});

test('non super admin cannot update the super admin role', function (): void {
    $role = Role::create(['name' => 'Role Manager']);
    $role->givePermissionTo('update roles');
    $manager = User::factory()->create();
    $manager->assignRole($role);

    $superAdminRole = Role::findByName(Role::SUPER_ADMIN);

    $response = $this->actingAs($manager)->put(route('roles.update', $superAdminRole), [
        'name' => Role::SUPER_ADMIN,
        'permissions' => ['view users'],
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('roles', ['name' => Role::SUPER_ADMIN]);
});

test('super admin cannot rename the super admin role', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::SUPER_ADMIN);

    $superAdminRole = Role::findByName(Role::SUPER_ADMIN);

    $response = $this->actingAs($superAdmin)->put(route('roles.update', $superAdminRole), [
        'name' => 'Renamed Super Admin',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('name');
    $this->assertDatabaseHas('roles', ['name' => Role::SUPER_ADMIN]);
});

test('super admin cannot update the super admin role permissions', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::SUPER_ADMIN);

    $superAdminRole = Role::findByName(Role::SUPER_ADMIN);

    $response = $this->actingAs($superAdmin)->put(route('roles.update', $superAdminRole), [
        'name' => Role::SUPER_ADMIN,
        'permissions' => ['view users'],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('role');
    $this->assertDatabaseHas('roles', ['name' => Role::SUPER_ADMIN]);
});

test('super admin can delete a role but cannot delete super admin role', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::SUPER_ADMIN);

    $role = Role::create(['name' => 'Temporary']);

    $response = $this->actingAs($superAdmin)->delete(route('roles.destroy', $role));
    $response->assertRedirect();
    $this->assertDatabaseMissing('roles', ['id' => $role->id]);

    $superAdminRole = Role::findByName(Role::SUPER_ADMIN);
    $superAdminDeleteResponse = $this->actingAs($superAdmin)->delete(route('roles.destroy', $superAdminRole));
    $superAdminDeleteResponse->assertRedirect();
    $superAdminDeleteResponse->assertSessionHasErrors('role');
    $this->assertDatabaseHas('roles', ['name' => Role::SUPER_ADMIN]);
});

test('super admin can bulk delete roles', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::SUPER_ADMIN);

    $role1 = Role::create(['name' => 'Role 1']);
    $role2 = Role::create(['name' => 'Role 2']);
    $superAdminRole = Role::findByName(Role::SUPER_ADMIN);

    $response = $this->actingAs($superAdmin)->delete(route('roles.destroy-bulk'), [
        'ids' => [$role1->id, $role2->id, $superAdminRole->id],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseMissing('roles', ['id' => $role1->id]);
    $this->assertDatabaseMissing('roles', ['id' => $role2->id]);
    $this->assertDatabaseHas('roles', ['id' => $superAdminRole->id]);
});

test('users with create or update roles permission can fetch role options', function (): void {
    $role = Role::create(['name' => 'Role Editor']);
    $role->givePermissionTo('update roles');
    $editor = User::factory()->create();
    $editor->assignRole($role);
    $permission = Permission::create(['name' => 'view reports']);

    $response = $this->actingAs($editor)->getJson(route('roles.options'));

    $response->assertOk();
    expect($response->json())->toContain('view reports');
});

test('users without create or update roles permission cannot fetch role options', function (): void {
    $role = Role::create(['name' => 'Role Viewer']);
    $role->givePermissionTo('view roles');
    $viewer = User::factory()->create();
    $viewer->assignRole($role);

    $response = $this->actingAs($viewer)->getJson(route('roles.options'));

    $response->assertForbidden();
});

test('permissions sync command seeds permissions and assigns to super admin', function (): void {
    $this->artisan('permissions:sync')
        ->expectsOutput('Permissions synced successfully and assigned to Super Admin role.')
        ->assertSuccessful();

    $superAdminRole = Role::findByName(Role::SUPER_ADMIN);
    expect($superAdminRole->permissions)->not->toBeEmpty();
});
