<?php

declare(strict_types=1);

namespace App\Actions\Role;

use App\Models\Role;

use function is_array;

class CreateRole
{
    /**
     * Create a new role and sync permissions.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Role
    {
        /** @var Role $role */
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }
}
