<?php

declare(strict_types=1);

use App\Actions\User\GetPaginatedUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

test('get paginated users works with default null request', function (): void {
    User::factory()->count(3)->create();

    $action = new GetPaginatedUsers;
    $result = $action->handle();

    expect($result->total())->toBe(3);
});

test('get paginated users filters by search term', function (): void {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    $action = new GetPaginatedUsers;

    $req1 = Request::create('/users', 'GET', ['search' => 'John']);
    $res1 = $action->handle($req1);
    expect($res1->total())->toBe(1);
    expect($res1->items()[0]->name)->toBe('John Doe');

    $req2 = Request::create('/users', 'GET', ['search' => 'jane@example.com']);
    $res2 = $action->handle($req2);
    expect($res2->total())->toBe(1);
    expect($res2->items()[0]->name)->toBe('Jane Smith');
});

test('get paginated users filters by email verified status', function (): void {
    User::factory()->create(['email_verified_at' => now()]);
    User::factory()->unverified()->create();

    $action = new GetPaginatedUsers;

    $reqVerified = Request::create('/users', 'GET', ['email_verified' => 'verified']);
    $resVerified = $action->handle($reqVerified);
    expect($resVerified->total())->toBe(1);

    $reqUnverified = Request::create('/users', 'GET', ['email_verified' => 'unverified']);
    $resUnverified = $action->handle($reqUnverified);
    expect($resUnverified->total())->toBe(1);

    $reqOther = Request::create('/users', 'GET', ['email_verified' => 'all']);
    $resOther = $action->handle($reqOther);
    expect($resOther->total())->toBe(2);
});

test('get paginated users handles complex sorting options', function (): void {
    $u1 = User::factory()->create(['name' => 'Alice', 'created_at' => now()->subDays(2)]);
    $u2 = User::factory()->create(['name' => 'Bob', 'created_at' => now()->subDay()]);

    $action = new GetPaginatedUsers;

    // Multi-column sorting and multi-part string
    $reqMulti = Request::create('/users', 'GET', ['sort' => 'name.asc-created_at.desc']);
    $resMulti = $action->handle($reqMulti);
    expect($resMulti->items()[0]->name)->toBe('Alice');

    // Invalid part without dot and invalid column name
    $reqInvalid = Request::create('/users', 'GET', ['sort' => 'nodotpart-password.asc-name.desc']);
    $resInvalid = $action->handle($reqInvalid);
    expect($resInvalid->items()[0]->name)->toBe('Bob');
});

test('get paginated users respects per page parameter', function (): void {
    User::factory()->count(15)->create();

    $action = new GetPaginatedUsers;
    $req = Request::create('/users', 'GET', ['per_page' => '5']);
    $res = $action->handle($req);

    expect($res->perPage())->toBe(5);
    expect($res->count())->toBe(5);
});
