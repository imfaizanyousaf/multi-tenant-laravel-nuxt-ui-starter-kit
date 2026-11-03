<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;

use function is_array;

final readonly class UpdateUser
{
    /**
     * Update an existing user record.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $user->name = $data['name'];
            $user->email = $data['email'];

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }

            $user->save();

            if (isset($data['roles']) && is_array($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            return $user;
        });
    }
}
