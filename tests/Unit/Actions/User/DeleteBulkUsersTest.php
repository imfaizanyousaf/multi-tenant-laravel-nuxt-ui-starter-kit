<?php

declare(strict_types=1);

use App\Actions\User\DeleteBulkUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('delete bulk users works with integer ids', function (): void {
    $admin = User::factory()->create();
    $users = User::factory()->count(2)->create();

    $action = new DeleteBulkUsers;
    $deletedCount = $action->handle($users->pluck('id')->toArray(), (string) $admin->id);

    expect($deletedCount)->toBe(2);
    foreach ($users as $user) {
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
});

test('delete bulk users works with uuid strings', function (): void {
    $admin = User::factory()->create();
    $users = User::factory()->count(2)->create();

    $action = new DeleteBulkUsers;
    $deletedCount = $action->handle($users->pluck('uuid')->toArray(), (string) $admin->id);

    expect($deletedCount)->toBe(2);
    foreach ($users as $user) {
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
});

test('delete bulk users excludes current user id', function (): void {
    $admin = User::factory()->create();
    $otherUser = User::factory()->create();

    $action = new DeleteBulkUsers;
    $ids = [(string) $admin->id, (string) $admin->uuid, $otherUser->uuid];

    $deletedCount = $action->handle($ids, (string) $admin->id);

    expect($deletedCount)->toBe(1);
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
    $this->assertDatabaseMissing('users', ['id' => $otherUser->id]);
});
