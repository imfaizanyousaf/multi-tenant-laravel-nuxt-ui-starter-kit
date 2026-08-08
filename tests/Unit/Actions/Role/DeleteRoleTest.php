<?php

declare(strict_types=1);

use App\Actions\Role\DeleteRole;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('delete role deletes a regular role', function (): void {
    $role = Role::create(['name' => 'Temporary']);

    $deleted = (new DeleteRole)->handle($role);

    expect($deleted)->toBeTrue();
    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

test('delete role throws for the super admin role', function (): void {
    $role = Role::findByName(Role::SUPER_ADMIN);

    expect(fn (): bool => (new DeleteRole)->handle($role))
        ->toThrow(InvalidArgumentException::class, 'The Super Admin role cannot be deleted.');
});
