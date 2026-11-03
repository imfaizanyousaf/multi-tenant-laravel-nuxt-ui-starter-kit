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

test('delete role deletes the super admin role when protection is handled by the request', function (): void {
    $role = Role::findByName(Role::SUPER_ADMIN);

    $deleted = (new DeleteRole)->handle($role);

    expect($deleted)->toBeTrue();
    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});
