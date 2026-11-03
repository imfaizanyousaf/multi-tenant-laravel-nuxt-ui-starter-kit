<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;

final readonly class DeleteUser
{
    /**
     * Delete a single user record.
     */
    public function handle(User $user): bool
    {
        return (bool) $user->delete();
    }
}
