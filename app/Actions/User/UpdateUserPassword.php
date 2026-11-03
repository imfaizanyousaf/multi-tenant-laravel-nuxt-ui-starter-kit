<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;

final readonly class UpdateUserPassword
{
    /**
     * Update the user's password.
     */
    public function handle(User $user, string $password, ?string $rememberToken = null): void
    {
        $attributes = [
            'password' => $password,
        ];

        if ($rememberToken !== null) {
            $attributes['remember_token'] = $rememberToken;
        }

        $user->forceFill($attributes)->save();
    }
}
