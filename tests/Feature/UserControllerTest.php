<?php

declare(strict_types=1);

use App\Http\Resources\DatatableResourceCollection;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login page when accessing users page', function (): void {
    $response = $this->get(route('users'));

    $response->assertRedirect(route('login'));
});

test('authenticated users without view users permission receive forbidden status', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('users'));

    $response->assertForbidden();
});

test('authenticated super admin can view the users page', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Role::SUPER_ADMIN);

    $response = $this->actingAs($user)->get(route('users'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page): Assert => $page
        ->component('Users')
    );
});

test('authenticated users can fetch roles options excluding super admin', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);
    Role::create(['name' => 'Manager']);

    $response = $this->actingAs($admin)->getJson(route('users.options'));

    $response->assertOk();
    expect($response->json())
        ->toContain('Manager')
        ->not->toContain(Role::SUPER_ADMIN);
});

test('users without create or update users permission cannot fetch user role options', function (): void {
    $role = Role::create(['name' => 'User Viewer']);
    $role->givePermissionTo('view users');
    $viewer = User::factory()->create();
    $viewer->assignRole($role);

    $response = $this->actingAs($viewer)->getJson(route('users.options'));

    $response->assertForbidden();
});

test('authenticated users can fetch paginated users table data with search and sorting', function (): void {
    $admin = User::factory()->create(['name' => 'Alice Admin', 'email' => 'alice@example.com']);
    $admin->assignRole(Role::SUPER_ADMIN);
    $unverified = User::factory()->unverified()->create(['name' => 'Bob Builder', 'email' => 'bob@example.com']);

    $response = $this->actingAs($admin)->getJson(route('users.table', [
        'search' => 'Alice',
        'sort' => 'name.asc',
        'per_page' => 5,
        'email_verified' => 'verified',
    ]));

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'email', 'roles', 'permissions', 'two_factor_enabled', 'avatar', 'created_at', 'updated_at'],
        ],
        'meta' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to'],
    ]);
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Alice Admin');

    // Test unverified filter and sorting desc
    $unverifiedResponse = $this->actingAs($admin)->getJson(route('users.table', [
        'email_verified' => 'unverified',
        'sort' => 'email.desc',
    ]));

    $unverifiedResponse->assertOk();
    expect($unverifiedResponse->json('data.0.id'))->toBe($unverified->uuid);

    // Test without sort param (default latest)
    $defaultSortResponse = $this->actingAs($admin)->getJson(route('users.table'));
    $defaultSortResponse->assertOk();
});

test('datatable resource collection supports default collection without custom resource class', function (): void {
    User::factory()->count(2)->create();
    $paginator = User::query()->paginate(10);
    $collection = new DatatableResourceCollection($paginator);

    $array = $collection->toArray(request());
    expect($array)->toHaveKey('data');
});

test('authenticated users can create a new user with roles but cannot assign super admin role', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);
    Role::create(['name' => 'Manager']);

    $response = $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@laravel.com',
        'password' => 'password123',
        'roles' => ['Manager'],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'name' => 'Jane Doe',
        'email' => 'jane@laravel.com',
    ]);

    $user = User::query()->where('email', 'jane@laravel.com')->first();
    expect($user?->hasRole('Manager'))->toBeTrue();

    // Assert Super Admin role cannot be assigned
    $invalidResponse = $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Super Fake',
        'email' => 'fake@laravel.com',
        'password' => 'password123',
        'roles' => [Role::SUPER_ADMIN],
    ]);

    $invalidResponse->assertSessionHasErrors('roles.0');
});

test('authenticated users can update a user and roles but cannot assign super admin role', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);
    $user = User::factory()->create();
    Role::create(['name' => 'Manager']);

    $response = $this->actingAs($admin)->put(route('users.update', $user), [
        'name' => 'Updated Name',
        'email' => 'updated@laravel.com',
        'roles' => ['Manager'],
    ]);

    $response->assertRedirect();
    expect($user->fresh()->name)->toBe('Updated Name');
    expect($user->fresh()->email)->toBe('updated@laravel.com');
    expect($user->fresh()->hasRole('Manager'))->toBeTrue();

    // Assert Super Admin role cannot be assigned on update
    $invalidResponse = $this->actingAs($admin)->put(route('users.update', $user), [
        'name' => 'Updated Name',
        'email' => 'updated@laravel.com',
        'roles' => [Role::SUPER_ADMIN],
    ]);

    $invalidResponse->assertSessionHasErrors('roles.0');
});

test('authenticated users can update a user password', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->put(route('users.update', $user), [
        'name' => 'Updated Name',
        'email' => 'updated_pass@laravel.com',
        'password' => 'new-password123',
    ]);

    $response->assertRedirect();
    expect(Hash::check('new-password123', $user->fresh()->password))->toBeTrue();
});

test('users cannot be created with more than one role', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);
    $editorRole = Role::create(['name' => 'Editor']);
    $viewerRole = Role::create(['name' => 'Viewer']);

    $response = $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@laravel.com',
        'password' => 'password123',
        'roles' => [$editorRole->name, $viewerRole->name],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('roles');
});

test('users cannot be updated with more than one role', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);
    $user = User::factory()->create();
    $editorRole = Role::create(['name' => 'Editor']);
    $viewerRole = Role::create(['name' => 'Viewer']);

    $response = $this->actingAs($admin)->put(route('users.update', $user), [
        'name' => 'Updated Name',
        'email' => 'updated@laravel.com',
        'roles' => [$editorRole->name, $viewerRole->name],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('roles');
});

test('authenticated users can delete a user', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->delete(route('users.destroy', $user));

    $response->assertRedirect();
    $this->assertSoftDeleted($user);
});

test('user cannot delete their own account via user crud', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $response = $this->actingAs($admin)->delete(route('users.destroy', $admin));

    $response->assertRedirect();
    $response->assertSessionHasErrors('user');
    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});

test('authenticated users can bulk delete users', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);
    $users = User::factory()->count(3)->create();

    $response = $this->actingAs($admin)->delete(route('users.destroy-bulk'), [
        'ids' => $users->pluck('uuid')->toArray(),
    ]);

    $response->assertRedirect();
    foreach ($users as $u) {
        $this->assertSoftDeleted($u);
    }
});

test('non super admin cannot assign the super admin role when creating a user', function (): void {
    $role = Role::create(['name' => 'User Creator']);
    $role->givePermissionTo('create users');
    $manager = User::factory()->create();
    $manager->assignRole($role);

    $response = $this->actingAs($manager)->post(route('users.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@laravel.com',
        'password' => 'password123',
        'roles' => [Role::SUPER_ADMIN],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('roles.0');
});

test('non super admin cannot assign the super admin role when updating a user', function (): void {
    $role = Role::create(['name' => 'User Editor']);
    $role->givePermissionTo('update users');
    $manager = User::factory()->create();
    $manager->assignRole($role);
    $user = User::factory()->create();

    $response = $this->actingAs($manager)->put(route('users.update', $user), [
        'name' => 'Updated Name',
        'email' => 'updated@laravel.com',
        'roles' => [Role::SUPER_ADMIN],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('roles.0');
    expect($user->fresh()->hasRole(Role::SUPER_ADMIN))->toBeFalse();
});

test('non super admin cannot update a super admin user', function (): void {
    $role = Role::create(['name' => 'User Editor']);
    $role->givePermissionTo('update users');
    $manager = User::factory()->create();
    $manager->assignRole($role);
    $superAdminUser = User::factory()->create();
    $superAdminUser->assignRole(Role::SUPER_ADMIN);

    $response = $this->actingAs($manager)->put(route('users.update', $superAdminUser), [
        'name' => 'Updated Name',
        'email' => 'updated@laravel.com',
    ]);

    $response->assertForbidden();
});

test('super admin cannot update a super admin user', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);
    $superAdminUser = User::factory()->create();
    $superAdminUser->assignRole(Role::SUPER_ADMIN);

    $response = $this->actingAs($admin)->put(route('users.update', $superAdminUser), [
        'name' => 'Updated Name',
        'email' => 'updated@laravel.com',
        'roles' => [Role::SUPER_ADMIN],
    ]);

    $response->assertForbidden();
    expect($superAdminUser->fresh()->name)->not->toBe('Updated Name');
    expect($superAdminUser->fresh()->email)->not->toBe('updated@laravel.com');
});

test('non super admin cannot delete a super admin user', function (): void {
    $role = Role::create(['name' => 'User Deleter']);
    $role->givePermissionTo('delete users');
    $manager = User::factory()->create();
    $manager->assignRole($role);
    $superAdminUser = User::factory()->create();
    $superAdminUser->assignRole(Role::SUPER_ADMIN);

    $response = $this->actingAs($manager)->delete(route('users.destroy', $superAdminUser));

    $response->assertForbidden();
});

test('non super admin bulk delete skips super admin users', function (): void {
    $role = Role::create(['name' => 'User Deleter']);
    $role->givePermissionTo('delete users');
    $manager = User::factory()->create();
    $manager->assignRole($role);
    $superAdminUser = User::factory()->create();
    $superAdminUser->assignRole(Role::SUPER_ADMIN);
    $regularUser = User::factory()->create();

    $response = $this->actingAs($manager)->delete(route('users.destroy-bulk'), [
        'ids' => [$superAdminUser->uuid, $regularUser->uuid],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['uuid' => $superAdminUser->uuid]);
    $this->assertSoftDeleted($regularUser);
});

test('super admin cannot delete a super admin user', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);
    $superAdminUser = User::factory()->create();
    $superAdminUser->assignRole(Role::SUPER_ADMIN);

    $response = $this->actingAs($admin)->delete(route('users.destroy', $superAdminUser));

    $response->assertRedirect();
    $response->assertSessionHasErrors('user');
    $this->assertDatabaseHas('users', ['uuid' => $superAdminUser->uuid]);
});

test('super admin bulk delete skips super admin users', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);
    $superAdminUser = User::factory()->create();
    $superAdminUser->assignRole(Role::SUPER_ADMIN);
    $regularUser = User::factory()->create();

    $response = $this->actingAs($admin)->delete(route('users.destroy-bulk'), [
        'ids' => [$superAdminUser->uuid, $regularUser->uuid],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['uuid' => $superAdminUser->uuid]);
    $this->assertSoftDeleted($regularUser);
});
