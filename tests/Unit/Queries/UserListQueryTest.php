<?php

declare(strict_types=1);

use App\Models\User;
use App\Queries\UserListQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user list query works with default arguments', function (): void {
    User::factory()->count(3)->create();

    $result = (new UserListQuery)->handle();

    expect($result->total())->toBe(3);
});

test('user list query filters by search term', function (): void {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    $query = new UserListQuery;

    $nameResult = $query->handle(search: 'John');
    expect($nameResult->total())->toBe(1);
    expect($nameResult->items()[0]->name)->toBe('John Doe');

    $emailResult = $query->handle(search: 'jane@example.com');
    expect($emailResult->total())->toBe(1);
    expect($emailResult->items()[0]->name)->toBe('Jane Smith');
});

test('user list query filters by email verified status', function (): void {
    User::factory()->create(['email_verified_at' => now()]);
    User::factory()->unverified()->create();

    $query = new UserListQuery;

    $verifiedResult = $query->handle(emailVerified: 'verified');
    expect($verifiedResult->total())->toBe(1);

    $unverifiedResult = $query->handle(emailVerified: 'unverified');
    expect($unverifiedResult->total())->toBe(1);

    $allResult = $query->handle(emailVerified: 'all');
    expect($allResult->total())->toBe(2);
});

test('user list query handles complex sorting options', function (): void {
    $alice = User::factory()->create(['name' => 'Alice', 'created_at' => now()->subDays(2)]);
    $bob = User::factory()->create(['name' => 'Bob', 'created_at' => now()->subDay()]);

    $query = new UserListQuery;

    $multiResult = $query->handle(sort: 'name.asc-created_at.desc');
    expect($multiResult->items()[0]->name)->toBe('Alice');

    $invalidPartsResult = $query->handle(sort: 'nodotpart-password.asc-name.desc');
    expect($invalidPartsResult->items()[0]->name)->toBe('Bob');

    $invalidOnlyResult = $query->handle(sort: 'password.asc');
    expect($invalidOnlyResult->items()[0]->name)->toBe('Bob');

    expect($alice->id)->not->toBe($bob->id);
});

test('user list query respects per page parameter', function (): void {
    User::factory()->count(15)->create();

    $result = (new UserListQuery)->handle(perPage: 5);

    expect($result->perPage())->toBe(5);
    expect($result->count())->toBe(5);
});
