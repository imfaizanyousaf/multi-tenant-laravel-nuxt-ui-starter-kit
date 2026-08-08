<?php

declare(strict_types=1);

namespace App\Actions\Role;

use App\Models\Role;

use function is_array;

class UpdateRole
{
    /**
     * Update an existing role and sync permissions.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Role $role, array $data): Role
    {
        $roleName = $role->name === Role::SUPER_ADMIN ? Role::SUPER_ADMIN : $data['name'];

        $role->update([
            'name' => $roleName,
        ]);

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }
}
