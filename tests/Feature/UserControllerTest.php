<?php

declare(strict_types=1);

use App\Http\Resources\DatatableResourceCollection;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login page when accessing users page', function (): void {
    $response = $this->get(route('users'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view the users page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('users'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page): Assert => $page
        ->component('Users')
        ->has('users')
    );
});

test('authenticated users can fetch paginated users table data with search and sorting', function (): void {
    $admin = User::factory()->create(['name' => 'Alice Admin', 'email' => 'alice@example.com']);
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
            '*' => ['id', 'name', 'email', 'avatar', 'created_at', 'updated_at'],
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
    $users = User::factory()->count(2)->create();
    $paginator = User::query()->paginate(10);
    $collection = new DatatableResourceCollection($paginator);

    $array = $collection->toArray(request());
    expect($array)->toHaveKey('data');
});

test('authenticated users can create a new user', function (): void {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
});

test('authenticated users can update a user', function (): void {
    $admin = User::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->put(route('users.update', $user), [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);

    $response->assertRedirect();
    expect($user->fresh()->name)->toBe('Updated Name');
    expect($user->fresh()->email)->toBe('updated@example.com');
});

test('authenticated users can update a user password', function (): void {
    $admin = User::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->put(route('users.update', $user), [
        'name' => 'Updated Name',
        'email' => 'updated_pass@example.com',
        'password' => 'new-password123',
    ]);

    $response->assertRedirect();
    expect(Hash::check('new-password123', $user->fresh()->password))->toBeTrue();
});

test('authenticated users can delete a user', function (): void {
    $admin = User::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->delete(route('users.destroy', $user));

    $response->assertRedirect();
    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('user cannot delete their own account via user crud', function (): void {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->delete(route('users.destroy', $admin));

    $response->assertRedirect();
    $response->assertSessionHasErrors('user');
    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});

test('authenticated users can bulk delete users', function (): void {
    $admin = User::factory()->create();
    $users = User::factory()->count(3)->create();

    $response = $this->actingAs($admin)->delete(route('users.destroy-bulk'), [
        'ids' => $users->pluck('uuid')->toArray(),
    ]);

    $response->assertRedirect();
    foreach ($users as $u) {
        $this->assertDatabaseMissing('users', [
            'id' => $u->id,
        ]);
    }
});
