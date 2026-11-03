<?php

declare(strict_types=1);

use App\Models\Role;
use App\Queries\RoleListQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('role list query works with default arguments', function (): void {
    Role::create(['name' => 'Role A']);
    Role::create(['name' => 'Role B']);

    $result = (new RoleListQuery)->handle();

    expect($result->total())->toBe(3);
});

test('role list query filters by search term', function (): void {
    Role::create(['name' => 'Manager']);
    Role::create(['name' => 'Editor']);

    $result = (new RoleListQuery)->handle(search: 'Manager');

    expect($result->total())->toBe(1);
    expect($result->items()[0]->name)->toBe('Manager');
});

test('role list query sorts by column and respects per page', function (): void {
    $manager = Role::create(['name' => 'Manager']);
    $editor = Role::create(['name' => 'Editor']);
    $writer = Role::create(['name' => 'Writer']);

    $result = (new RoleListQuery)->handle(sort: 'name.asc', perPage: 2);

    expect($result->perPage())->toBe(2);
    expect($result->items()[0]->name)->toBe($editor->name)
        ->and($result->items()[1]->name)->toBe($manager->name);

    $descResult = (new RoleListQuery)->handle(sort: 'name.desc');

    expect($descResult->items()[0]->name)->toBe($writer->name);
});

test('role list query falls back to latest when sort yields no column', function (): void {
    $writer = Role::create(['name' => 'Writer']);
    $editor = Role::create(['name' => 'Editor']);

    Role::query()->update(['created_at' => now()->subMinutes(20)]);
    Role::query()->where('id', $writer->id)->update(['created_at' => now()->subMinutes(10)]);
    Role::query()->where('id', $editor->id)->update(['created_at' => now()->subMinutes(5)]);

    $result = (new RoleListQuery)->handle(sort: 'name');

    expect($result->items()[0]->id)->toBe($editor->id);

    $invalidSortResult = (new RoleListQuery)->handle(sort: 'password.asc');

    expect($invalidSortResult->items()[0]->id)->toBe($editor->id);
});
