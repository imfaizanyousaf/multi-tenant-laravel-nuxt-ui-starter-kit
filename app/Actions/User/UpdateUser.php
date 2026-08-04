<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUser
{
    /**
     * Update an existing user record.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): User
    {
        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return $user;
    }
}
