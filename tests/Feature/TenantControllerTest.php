<?php

declare(strict_types=1);

use App\Enums\TenantStatus;
use App\Jobs\CreateTenantDatabase;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login page when accessing tenants page', function (): void {
    $response = $this->get(route('tenants'));

    $response->assertRedirect(route('login'));
});

test('authenticated users without view tenants permission receive forbidden status', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('tenants'))->assertForbidden();
    $this->actingAs($user)->getJson(route('tenants.table'))->assertForbidden();
});

test('users with view tenants permission can view the tenants page and table', function (): void {
    $role = Role::create(['name' => 'Tenant Viewer']);
    $role->givePermissionTo('view tenants');
    $user = User::factory()->create();
    $user->assignRole($role);
    Tenant::query()->create([
        'name' => 'Acme Inc',
        'domain' => 'acme.'.config('app.domain'),
        'database' => config('multitenancy.tenant_database_prefix').'acme',
        'status' => TenantStatus::ACTIVE,
    ]);

    $response = $this->actingAs($user)->get(route('tenants'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page): Assert => $page
        ->component('Tenants')
    );

    $tableResponse = $this->actingAs($user)->getJson(route('tenants.table'));
    $tableResponse->assertOk();
});

test('super admin can fetch paginated tenants with search and sorting', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $acme = Tenant::query()->create([
        'name' => 'Acme Inc',
        'domain' => 'acme.'.config('app.domain'),
        'database' => config('multitenancy.tenant_database_prefix').'acme',
        'status' => TenantStatus::ACTIVE,
    ]);
    Tenant::query()->create([
        'name' => 'Globex Corp',
        'domain' => 'globex.'.config('app.domain'),
        'database' => config('multitenancy.tenant_database_prefix').'globex',
        'status' => TenantStatus::ACTIVE,
    ]);

    $response = $this->actingAs($admin)->getJson(route('tenants.table', [
        'search' => 'acme',
        'sort' => 'name.asc',
        'per_page' => 5,
    ]));

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'domain', 'database', 'status', 'created_at'],
        ],
        'meta' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to'],
    ]);
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.id'))->toBe($acme->id);

    $statusResponse = $this->actingAs($admin)->getJson(route('tenants.table', [
        'sort' => 'status.desc',
    ]));
    $statusResponse->assertOk();

    $defaultResponse = $this->actingAs($admin)->getJson(route('tenants.table'));
    $defaultResponse->assertOk();
});

test('super admin can create a tenant and the database job is queued', function (): void {
    Queue::fake();

    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $response = $this->actingAs($admin)->post(route('tenants.store'), [
        'name' => 'Acme Co',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tenants', [
        'name' => 'Acme Co',
        'domain' => 'acme-co.'.config('app.domain'),
        'database' => config('multitenancy.tenant_database_prefix').'acme-co',
    ]);

    $tenant = Tenant::query()->where('domain', 'acme-co.'.config('app.domain'))->first();
    expect($tenant?->status)->toBe(TenantStatus::CREATING);

    Queue::assertPushed(CreateTenantDatabase::class);
});

test('creating tenants that slug to the same value yields unique domains and databases', function (): void {
    Queue::fake();

    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $this->actingAs($admin)->post(route('tenants.store'), ['name' => 'Acme Co']);
    $this->actingAs($admin)->post(route('tenants.store'), ['name' => 'Acme-Co']);

    expect(Tenant::query()->where('domain', 'acme-co.'.config('app.domain'))->exists())->toBeTrue();
    expect(Tenant::query()->where('domain', 'acme-co-1.'.config('app.domain'))->exists())->toBeTrue();

    $this->assertDatabaseCount('tenants', 2);
});

test('creating a tenant with a non-latin name falls back to a valid slug', function (): void {
    Queue::fake();

    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $this->actingAs($admin)->post(route('tenants.store'), ['name' => '株式会社']);

    $this->assertDatabaseHas('tenants', [
        'name' => '株式会社',
        'domain' => 'tenant.'.config('app.domain'),
        'database' => config('multitenancy.tenant_database_prefix').'tenant',
    ]);
});

test('users without create tenants permission cannot create tenants', function (): void {
    $role = Role::create(['name' => 'Tenant Viewer']);
    $role->givePermissionTo('view tenants');
    $user = User::factory()->create();
    $user->assignRole($role);

    $response = $this->actingAs($user)->post(route('tenants.store'), [
        'name' => 'Acme Co',
    ]);

    $response->assertForbidden();
});

test('super admin can delete a tenant with the correct confirmation', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);
    $tenant = Tenant::query()->create([
        'name' => 'Acme Inc',
        'domain' => 'acme.'.config('app.domain'),
        'database' => config('multitenancy.tenant_database_prefix').'acme',
        'status' => TenantStatus::ACTIVE,
    ]);

    $wrongResponse = $this->actingAs($admin)->delete(route('tenants.destroy', $tenant), [
        'confirmation' => 'nope',
    ]);

    $wrongResponse->assertRedirect();
    $wrongResponse->assertSessionHasErrors('confirmation');
    $this->assertDatabaseHas('tenants', ['id' => $tenant->id]);

    $response = $this->actingAs($admin)->delete(route('tenants.destroy', $tenant), [
        'confirmation' => $tenant->database,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
});

test('users without delete tenants permission cannot delete tenants', function (): void {
    $role = Role::create(['name' => 'Tenant Viewer']);
    $role->givePermissionTo('view tenants');
    $user = User::factory()->create();
    $user->assignRole($role);
    $tenant = Tenant::query()->create([
        'name' => 'Acme Inc',
        'domain' => 'acme.'.config('app.domain'),
        'database' => config('multitenancy.tenant_database_prefix').'acme',
        'status' => TenantStatus::ACTIVE,
    ]);

    $response = $this->actingAs($user)->delete(route('tenants.destroy', $tenant), [
        'confirmation' => $tenant->database,
    ]);

    $response->assertForbidden();
});

test('permissions sync command seeds tenant permissions', function (): void {
    $this->artisan('permissions:sync')
        ->expectsOutput('Permissions synced successfully and assigned to Super Admin role.')
        ->assertSuccessful();

    expect(Role::findByName(Role::SUPER_ADMIN)->hasPermissionTo('view tenants'))->toBeTrue()
        ->and(Role::findByName(Role::SUPER_ADMIN)->hasPermissionTo('create tenants'))->toBeTrue()
        ->and(Role::findByName(Role::SUPER_ADMIN)->hasPermissionTo('delete tenants'))->toBeTrue();
});
