<?php

declare(strict_types=1);

use App\Actions\Role\GetPaginatedRoles;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

test('get paginated roles works with default null request', function (): void {
    Role::create(['name' => 'Role A']);
    Role::create(['name' => 'Role B']);

    $result = (new GetPaginatedRoles)->handle();

    expect($result->total())->toBe(3);
});

test('get paginated roles filters by search term', function (): void {
    Role::create(['name' => 'Manager']);
    Role::create(['name' => 'Editor']);

    $req = Request::create('/roles', 'GET', ['search' => 'Manager']);

    $result = (new GetPaginatedRoles)->handle($req);

    expect($result->total())->toBe(1);
    expect($result->items()[0]->name)->toBe('Manager');
});

test('get paginated roles sorts by column and respects per page', function (): void {
    $manager = Role::create(['name' => 'Manager']);
    $editor = Role::create(['name' => 'Editor']);
    $writer = Role::create(['name' => 'Writer']);

    $req = Request::create('/roles', 'GET', ['sort' => 'name.asc', 'per_page' => '2']);

    $result = (new GetPaginatedRoles)->handle($req);

    expect($result->perPage())->toBe(2);
    expect($result->items()[0]->name)->toBe($editor->name)
        ->and($result->items()[1]->name)->toBe($manager->name);

    $descReq = Request::create('/roles', 'GET', ['sort' => 'name.desc']);
    $descResult = (new GetPaginatedRoles)->handle($descReq);

    expect($descResult->items()[0]->name)->toBe($writer->name);
});

test('get paginated roles falls back to latest when sort yields no column', function (): void {
    $writer = Role::create(['name' => 'Writer']);
    $editor = Role::create(['name' => 'Editor']);

    Role::query()->update(['created_at' => now()->subMinutes(20)]);
    Role::query()->where('id', $writer->id)->update(['created_at' => now()->subMinutes(10)]);
    Role::query()->where('id', $editor->id)->update(['created_at' => now()->subMinutes(5)]);

    $req = Request::create('/roles', 'GET', ['sort' => 'name']);

    $result = (new GetPaginatedRoles)->handle($req);

    expect($result->items()[0]->id)->toBe($editor->id);
});
